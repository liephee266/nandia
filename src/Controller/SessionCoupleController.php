<?php
// src/Controller/SessionCoupleController.php

namespace App\Controller;

use App\Entity\Couple;
use App\Entity\Session;
use App\Entity\SessionCard;
use App\Entity\Users;
use App\Repository\CardRepository;
use App\Repository\CoupleRepository;
use App\Repository\SessionRepository;
use App\Repository\SessionCardRepository;
use App\Service\PushNotificationService;
use App\Service\SessionStatePublisher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Sessions de jeu en mode couple (live ou relax).
 *
 * Endpoints :
 *  POST   /api/couple-session/create          → Créer une session couple
 *  GET    /api/couple-session/{id}/state      → Polling — état de la session
 *  POST   /api/couple-session/{id}/respond    → Soumettre sa réponse à la carte active
 *  POST   /api/couple-session/{id}/next-card  → Passer à la carte suivante
 *  POST   /api/couple-session/{id}/close      → Terminer la session
 */
#[Route('/api/couple-session')]
class SessionCoupleController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface    $em,
        private readonly CoupleRepository          $coupleRepo,
        private readonly SessionRepository         $sessionRepo,
        private readonly SessionCardRepository     $sessionCardRepo,
        private readonly CardRepository            $cardRepo,
        private readonly PushNotificationService   $push,
        private readonly SessionStatePublisher     $sessionPublisher,
    ) {}

    // ── POST /api/couple-session/create ─────────────────────────────────────

    #[Route('/create', methods: ['POST'])]
    public function create(
        Request            $request,
        #[CurrentUser] Users $user,
    ): JsonResponse {
        $couple = $this->coupleRepo->findActiveForUser($user);
        if ($couple === null) {
            return $this->json(['error' => 'Vous devez faire partie d\'un couple actif pour créer une session.'], 403);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $mode = in_array($data['mode'] ?? '', ['couple_live', 'couple_relax'])
            ? $data['mode']
            : 'couple_live';

        $session = new Session();
        $session->setUser($user);
        $session->setCouple($couple);
        $session->setMode($mode);
        $session->setCardCount((int) ($data['cardCount'] ?? 10));

        if (!empty($data['timerPerCard'])) {
            $session->setTimerPerCard((int) $data['timerPerCard']);
        }

        if (!empty($data['themeId'])) {
            $theme = $this->em->find(\App\Entity\Theme::class, (int) $data['themeId']);
            if ($theme) $session->setTheme($theme);
        }

        $this->em->persist($session);

        // Tirer la première carte immédiatement
        $sessionCard = $this->drawNextCard($session, $couple, 0);
        if (!$sessionCard) {
            return $this->json(['error' => 'Aucune carte disponible pour ce thème.'], 500);
        }

        $this->em->flush();

        return $this->json($this->serializeSession($session, $user, $couple), 201);
    }

    // ── GET /api/couple-session/{id}/state ──────────────────────────────────

    #[Route('/{id}/state', methods: ['GET'])]
    public function state(
        int                $id,
        #[CurrentUser] Users $user,
    ): JsonResponse {
        [$session, $couple, $error] = $this->resolveSession($id, $user);
        if ($error) return $error;

        // Vérifier expiration du timer sur la carte active
        $activeCard = $this->sessionCardRepo->findActiveCardForSession($session->getId());
        if ($activeCard && $activeCard->isTimerExpired() && !$activeCard->isRevealed()) {
            // Timer expiré → révéler automatiquement avec ce qui existe
            $activeCard->setRevealed(true);
            $this->em->flush();
        }

        return $this->json($this->serializeSession($session, $user, $couple));
    }

    // ── POST /api/couple-session/{id}/respond ───────────────────────────────

    #[Route('/{id}/respond', methods: ['POST'])]
    public function respond(
        int                $id,
        Request            $request,
        #[CurrentUser] Users $user,
    ): JsonResponse {
        [$session, $couple, $error] = $this->resolveSession($id, $user);
        if ($error) return $error;

        $data       = json_decode($request->getContent(), true) ?? [];
        $answerText = trim($data['answer'] ?? '');

        if (empty($answerText)) {
            return $this->json(['error' => 'La réponse ne peut pas être vide.'], 400);
        }

        $activeCard = $this->sessionCardRepo->findActiveCardForSession($session->getId());
        if (!$activeCard) {
            return $this->json(['error' => 'Aucune carte active dans cette session.'], 404);
        }

        if ($activeCard->isRevealed()) {
            return $this->json(['error' => 'Cette carte a déjà été révélée.'], 409);
        }

        // Déterminer si c'est user1 ou user2 qui répond
        $position = $this->getUserPosition($user, $couple);

        // Vérifier que ce joueur n'a pas déjà répondu
        if ($position === 1 && $activeCard->getUser1RespondedAt() !== null) {
            return $this->json(['error' => 'Vous avez déjà répondu à cette carte.'], 409);
        }
        if ($position === 2 && $activeCard->getUser2RespondedAt() !== null) {
            return $this->json(['error' => 'Vous avez déjà répondu à cette carte.'], 409);
        }

        // Pour les défis tour à tour : vérifier que c'est bien le tour de ce joueur
        if ($activeCard->getCurrentTurn() !== null && $activeCard->getCurrentTurn() !== $position) {
            return $this->json(['error' => 'Ce n\'est pas votre tour.'], 403);
        }

        $activeCard->submitResponse($position, $answerText);

        // Mode tour à tour : passer au tour suivant si pas révélé
        if ($activeCard->getCurrentTurn() !== null && !$activeCard->isRevealed()) {
            $next = $position === 1 ? 2 : 1;
            $activeCard->setCurrentTurn($next);
        }

        $this->em->flush();

        // Notify partner in real-time
        $partner = $position === 1 ? $couple->getUser2() : $couple->getUser1();
        if ($partner && $partner !== $user) {
            $this->push->sendToUser(
                $partner,
                'Ton partenaire a répondu 💬',
                'C\'est ton tour de répondre !',
                ['route' => '/couple-play', 'sessionId' => (string) $session->getId()],
            );
        }

        $this->sessionPublisher->publishSessionUpdate($session, 'partner_responded');

        return $this->json($this->serializeSession($session, $user, $couple));
    }

    // ── POST /api/couple-session/{id}/next-card ──────────────────────────────

    #[Route('/{id}/next-card', methods: ['POST'])]
    public function nextCard(
        int                $id,
        #[CurrentUser] Users $user,
    ): JsonResponse {
        [$session, $couple, $error] = $this->resolveSession($id, $user);
        if ($error) return $error;

        $activeCard = $this->sessionCardRepo->findActiveCardForSession($session->getId());

        // On ne peut passer qu'une fois la carte révélée (ou le timer expiré)
        if ($activeCard && !$activeCard->isRevealed() && !$activeCard->isTimerExpired()) {
            return $this->json(['error' => 'Attendez que les deux partenaires aient répondu.'], 409);
        }

        // Compter les cartes déjà tirées (y compris l'actuelle)
        $playedCount = $this->sessionCardRepo->countForSession($session->getId());

        // Vérifier AVANT de tirer — on ne tire pas si on a atteint la limite.
        // La carte active a déjà été affichée avant cet appel, on vérifie
        // maintenant si une SUIVANTE peut encore être tirée.
        $cardCount = $session->getCardCount();
        if ($cardCount !== null && $playedCount >= $cardCount) {
            $session->setEndedAt(new \DateTimeImmutable());
            $this->em->flush();
            return $this->json(['status' => 'done', 'sessionId' => $session->getId()]);
        }

        // La carte en cours a été révélée → on tire la suivante
        $newCard = $this->drawNextCard($session, $couple, $playedCount);
        if (!$newCard) {
            $session->setEndedAt(new \DateTimeImmutable());
            $this->em->flush();
            return $this->json(['status' => 'done', 'sessionId' => $session->getId()]);
        }

        $this->em->flush();

        // Notifier les deux joueurs : nouvelle carte disponible
        $recipients = array_filter([$couple->getUser1(), $couple->getUser2()]);
        $this->push->sendToUsers(
            $recipients,
            'Nouvelle carte 🃏',
            'Une nouvelle question vous attend !',
            ['route' => '/couple-play', 'sessionId' => (string) $session->getId()],
        );

        $this->sessionPublisher->publishSessionEvent($session, 'new_card', [
            'sessionCardId' => $newCard->getId(),
        ]);

        return $this->json($this->serializeSession($session, $user, $couple));
    }

    // ── POST /api/couple-session/{id}/close ─────────────────────────────────

    #[Route('/{id}/close', methods: ['POST'])]
    public function close(
        int                $id,
        #[CurrentUser] Users $user,
    ): JsonResponse {
        [$session, , $error] = $this->resolveSession($id, $user);
        if ($error) return $error;

        if ($session->getEndedAt() === null) {
            $session->setEndedAt(new \DateTimeImmutable());
            $this->em->flush();
        }

        return $this->json(['status' => 'closed', 'sessionId' => $session->getId()]);
    }

    // ── Helpers privés ───────────────────────────────────────────────────────

    /**
     * Résout la session et valide les droits d'accès.
     * Retourne [Session, Couple, null] ou [null, null, JsonResponse erreur].
     */
    private function resolveSession(int $id, Users $user): array
    {
        $session = $this->sessionRepo->find($id);
        if (!$session) {
            return [null, null, $this->json(['error' => 'Session introuvable.'], 404)];
        }

        $couple = $this->coupleRepo->findActiveForUser($user);
        if (!$couple || $session->getCouple()?->getId() !== $couple->getId()) {
            return [null, null, $this->json(['error' => 'Accès refusé.'], 403)];
        }

        return [$session, $couple, null];
    }

    /** Retourne 1 si user est user1 du couple, 2 si user2 */
    private function getUserPosition(Users $user, Couple $couple): int
    {
        return $couple->getUser1()?->getId() === $user->getId() ? 1 : 2;
    }

    private function drawNextCard(Session $session, Couple $couple, int $orderIndex): ?SessionCard
    {
        $themeId = $session->getTheme()?->getId();

        // Exclure les cartes déjà jouées dans cette session pour éviter les doublons
        $playedCardIds = array_map(
            fn($sc) => $sc->getCard()?->getId(),
            $session->getSessionCards()->toArray()
        );
        $playedCardIds = array_filter($playedCardIds, fn($id) => $id !== null);

        $card = $this->cardRepo->findRandomCard($themeId, null, $playedCardIds ?: null);

        if (!$card) return null;

        $sc = new SessionCard();
        $sc->setSession($session);
        $sc->setCard($card);
        $sc->setOrderIndex($orderIndex);

        // Déterminer le mode selon le type de carte
        $isChallenge = in_array($card->getType() ?? '', ['challenge', 'ritual'], true);
        if ($isChallenge) {
            // Tour à tour : user1 commence
            $sc->setCurrentTurn(1);
        }

        // Appliquer le timer si configuré
        if ($session->getTimerPerCard()) {
            $sc->startTimer($session->getTimerPerCard());
        }

        $this->em->persist($sc);

        return $sc;
    }

    private function serializeSession(Session $session, Users $user, Couple $couple): array
    {
        $activeCard = $this->sessionCardRepo->findActiveCardForSession($session->getId());
        $position   = $this->getUserPosition($user, $couple);
        $partner    = $couple->getPartner($user);

        $cardData = null;
        if ($activeCard) {
            $isTurnBased = $activeCard->getCurrentTurn() !== null;
            $myTurn      = !$isTurnBased || $activeCard->getCurrentTurn() === $position;

            // Ce que je vois de mes propres données
            $myAnswer     = $position === 1 ? $activeCard->getUser1Response() : $activeCard->getUser2Response();
            $myAnsweredAt = $position === 1 ? $activeCard->getUser1RespondedAt() : $activeCard->getUser2RespondedAt();

            // Les réponses de l'autre ne sont visibles qu'après révélation
            $partnerAnswer = null;
            if ($activeCard->isRevealed()) {
                $partnerAnswer = $position === 1
                    ? $activeCard->getUser2Response()
                    : $activeCard->getUser1Response();
            }

            $timerSecondsLeft = null;
            if ($activeCard->getTimerExpiresAt()) {
                $diff = $activeCard->getTimerExpiresAt()->getTimestamp() - time();
                $timerSecondsLeft = max(0, $diff);
            }

            $cardData = [
                'sessionCardId'    => $activeCard->getId(),
                'orderIndex'       => $activeCard->getOrderIndex(),
                'questionText'     => $activeCard->getCard()->getQuestionText(),
                'type'             => $activeCard->getCard()->getType() ?? 'question',
                'themeName'        => $activeCard->getCard()->getTheme()?->getName(),
                'isTurnBased'      => $isTurnBased,
                'isMyTurn'         => $myTurn,
                'myAnswer'         => $myAnswer,
                'myAnsweredAt'     => $myAnsweredAt?->format(\DateTimeInterface::ATOM),
                'partnerAnswered'  => ($position === 1 ? $activeCard->getUser2RespondedAt() : $activeCard->getUser1RespondedAt()) !== null,
                'partnerAnswer'    => $partnerAnswer,   // null tant que non révélé
                'revealed'         => $activeCard->isRevealed(),
                'timerExpiresAt'   => $activeCard->getTimerExpiresAt()?->format(\DateTimeInterface::ATOM),
                'timerSecondsLeft' => $timerSecondsLeft,
                'skipped'          => $activeCard->getSkipped(),
            ];
        }

        $cardCount   = $this->sessionCardRepo->countForSession($session->getId());

        return [
            'sessionId'       => $session->getId(),
            'mode'            => $session->getMode(),
            'status'          => $session->getEndedAt() ? 'done' : 'active',
            'cardCount'       => $session->getCardCount(),
            'playedCount'     => $cardCount,
            'timerPerCard'    => $session->getTimerPerCard(),
            'myPosition'      => $position,
            'partner'         => [
                'id'     => $partner?->getId(),
                'pseudo' => $partner?->getPseudo(),
                'avatar' => $partner?->getProfileImage(),
            ],
            'currentCard'     => $cardData,
        ];
    }
}
