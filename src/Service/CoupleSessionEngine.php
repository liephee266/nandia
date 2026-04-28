<?php

namespace App\Service;

use App\Entity\Card;
use App\Entity\Couple;
use App\Entity\Session;
use App\Entity\SessionCard;
use App\Entity\Theme;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Moteur de jeu pour les sessions couple (live et relax).
 * Extrait la logique métier de SessionCoupleController (401 lignes).
 */
class CoupleSessionEngine
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    /**
     * Crée une nouvelle session couple.
     */
    public function createSession(Users $user, Couple $couple, array $options = []): array
    {
        $mode = $options['mode'] ?? 'couple_live';
        $themeId = $options['themeId'] ?? null;
        $cardCount = $options['cardCount'] ?? 10;
        $timerPerCard = $options['timerPerCard'] ?? null;

        $session = new Session();
        $session->setUser($user);
        $session->setCouple($couple);
        $session->setMode($mode);
        $session->setCardCount($cardCount);

        if ($timerPerCard !== null) {
            $session->setTimerPerCard((int) $timerPerCard);
        }

        if ($themeId !== null) {
            $theme = $this->em->getRepository(Theme::class)->find($themeId);
            if ($theme) {
                $session->setTheme($theme);
            }
        }

        $this->em->persist($session);
        $this->em->flush();

        return [
            'sessionId' => $session->getId(),
            'mode' => $session->getMode(),
            'cardCount' => $session->getCardCount(),
            'timerPerCard' => $session->getTimerPerCard(),
        ];
    }

    /**
     * Tire la prochaine carte pour une session couple.
     */
    public function nextCard(Session $session): array
    {
        $playedCount = $session->getSessionCards()->count();
        $cardCount = $session->getCardCount();

        if ($cardCount !== null && $playedCount >= $cardCount) {
            $session->setEndedAt(new \DateTimeImmutable());
            $this->em->flush();
            return ['status' => 'done'];
        }

        $this->drawNextCard($session);
        $this->em->flush();

        return $this->buildSessionState($session);
    }

    /**
     * Soumet une réponse pour un utilisateur dans la session couple.
     */
    public function submitResponse(Session $session, Users $user, string $answer): array
    {
        $sessionCard = $this->getCurrentSessionCard($session);
        if (!$sessionCard) {
            return ['error' => 'Aucune carte active.'];
        }

        $couple = $session->getCouple();
        if (!$couple) {
            return ['error' => 'Pas de couple associé.'];
        }

        $position = $couple->getUser1()?->getId() === $user->getId() ? 1 : 2;
        $sessionCard->submitResponse($position, $answer);

        $this->em->flush();

        return $this->buildSessionState($session, $user);
    }

    /**
     * Ferme la session.
     */
    public function closeSession(Session $session): bool
    {
        $session->setEndedAt(new \DateTimeImmutable());
        $this->em->flush();
        return true;
    }

    /**
     * Construit l'état de la session pour le client.
     */
    public function buildSessionState(Session $session, ?Users $requestingUser = null): array
    {
        $sessionCard = $this->getCurrentSessionCard($session);
        $couple = $session->getCouple();

        $partner = null;
        $myPosition = 1;

        if ($couple && $requestingUser) {
            $partner = $couple->getPartner($requestingUser);
            $myPosition = $couple->getUser1()?->getId() === $requestingUser->getId() ? 1 : 2;
        }

        $state = [
            'sessionId' => $session->getId(),
            'mode' => $session->getMode(),
            'cardCount' => $session->getCardCount(),
            'playedCount' => $session->getSessionCards()->count(),
            'timerPerCard' => $session->getTimerPerCard(),
            'myPosition' => $myPosition,
        ];

        if ($partner) {
            $state['partner'] = [
                'id' => $partner->getId(),
                'pseudo' => $partner->getPseudo(),
                'avatar' => $partner->getProfileImage(),
            ];
        }

        if ($sessionCard) {
            $card = $sessionCard->getCard();
            $isMyAnswer = $myPosition === 1
                ? $sessionCard->getUser1Response()
                : $sessionCard->getUser2Response();
            $partnerAnswer = $myPosition === 1
                ? $sessionCard->getUser2Response()
                : $sessionCard->getUser1Response();

            $state['currentCard'] = [
                'sessionCardId' => $sessionCard->getId(),
                'questionText' => $card?->getQuestionText(),
                'type' => $card?->getType(),
                'themeName' => $card?->getTheme()?->getName(),
                'difficultyLevel' => $card?->getDifficultyLevel(),
                'revealed' => $sessionCard->isRevealed(),
                'myAnswer' => $isMyAnswer,
                'partnerAnswer' => $partnerAnswer,
                'partnerAnswered' => ($myPosition === 1
                    ? $sessionCard->getUser2RespondedAt()
                    : $sessionCard->getUser1RespondedAt()) !== null,
                'isTurnBased' => $sessionCard->getCurrentTurn() !== null,
                'isMyTurn' => $sessionCard->getCurrentTurn() === null
                    || $sessionCard->getCurrentTurn() === $myPosition,
                'timerSecondsLeft' => $this->getTimerSecondsLeft($sessionCard),
            ];
        }

        return $state;
    }

    // ── Private helpers ────────────────────────────────────────────────────

    private function getCurrentSessionCard(Session $session): ?SessionCard
    {
        $cards = $session->getSessionCards();
        if ($cards->isEmpty()) {
            return null;
        }

        // Retourne la dernière session card non révélée (ou la dernière tout court)
        $latest = null;
        foreach ($cards as $sc) {
            if (!$sc->isRevealed()) {
                $latest = $sc;
            }
        }

        return $latest ?? $cards->last();
    }

    private function drawNextCard(Session $session): void
    {
        $qb = $this->em->getRepository(Card::class)->createQueryBuilder('c');
        $qb->orderBy('RANDOM()')->setMaxResults(1);

        // Exclure les cartes déjà jouées dans cette session
        $playedCardIds = [];
        foreach ($session->getSessionCards() as $sc) {
            if ($sc->getCard()?->getId() !== null) {
                $playedCardIds[] = $sc->getCard()->getId();
            }
        }

        if (!empty($playedCardIds)) {
            $qb->andWhere('c.id NOT IN (:played)')
               ->setParameter('played', $playedCardIds);
        }

        // Filtrer par thème si défini
        if ($session->getTheme() !== null) {
            $qb->andWhere('c.theme = :theme')
               ->setParameter('theme', $session->getTheme());
        }

        $card = $qb->getQuery()->getOneOrNullResult();
        if ($card) {
            $sessionCard = new SessionCard();
            $sessionCard->setSession($session);
            $sessionCard->setCard($card);
            $sessionCard->setOrderIndex($session->getSessionCards()->count());

            if ($session->getTimerPerCard() !== null) {
                $sessionCard->startTimer($session->getTimerPerCard());
            }

            $this->em->persist($sessionCard);
        }
    }

    private function getTimerSecondsLeft(SessionCard $sessionCard): ?int
    {
        if ($sessionCard->getTimerExpiresAt() === null) {
            return null;
        }

        $remaining = $sessionCard->getTimerExpiresAt()->getTimestamp() - time();
        return max(0, $remaining);
    }
}
