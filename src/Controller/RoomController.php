<?php
// src/Controller/RoomController.php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\Room;
use App\Entity\RoomParticipant;
use App\Entity\Session;
use App\Entity\SessionCard;
use App\Entity\Users;
use App\Message\RoomCardTimerExpired;
use App\Repository\SessionRepository;
use App\Repository\CardRepository;
use App\Repository\CoupleRepository;
use App\Repository\RoomRepository;
use App\Repository\RoomParticipantRepository;
use App\Repository\CardVoteRepository;
use App\Service\RoomStatePublisher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Gestion des salles de jeu multi-couples.
 *
 * Endpoints :
 *  POST   /api/room/create            → Créer une salle (hôte)
 *  POST   /api/room/join              → Rejoindre via code
 *  GET    /api/room/{id}/state        → Polling — état complet de la salle
 *  POST   /api/room/{id}/start        → Démarrer la partie (hôte uniquement)
 *  POST   /api/room/{id}/next-card    → Carte suivante (hôte ou auto)
 *  DELETE /api/room/{id}/leave        → Quitter la salle
 */
#[Route('/api/room')]
class RoomController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface    $em,
        private readonly RoomRepository            $roomRepo,
        private readonly CoupleRepository          $coupleRepo,
        private readonly RoomParticipantRepository $participantRepo,
        private readonly CardRepository            $cardRepo,
        private readonly CardVoteRepository        $voteRepo,
        private readonly SessionRepository         $sessionRepo,
        private readonly RoomStatePublisher         $roomPublisher,
        private readonly MessageBusInterface       $messageBus,
    ) {}

    // ── POST /api/room/create ───────────────────────────────────────────────

    #[Route('/create', methods: ['POST'])]
    public function create(
        Request            $request,
        #[CurrentUser] Users $user,
    ): JsonResponse {
        $couple = $this->coupleRepo->findActiveForUser($user);
        if (!$couple) {
            return $this->json(['error' => 'Vous devez être en couple pour créer une salle.'], 403);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $room = new Room();
        $room->setHostCouple($couple);
        $room->setCardCount((int) ($data['cardCount'] ?? 10));
        $room->setMaxCouples((int) ($data['maxCouples'] ?? 8));

        if (!empty($data['timerPerCard'])) {
            $room->setTimerPerCard((int) $data['timerPerCard']);
        }

        if (!empty($data['difficulty'])) {
            $difficulty = (int) $data['difficulty'];
            if (in_array($difficulty, [1, 2, 3], true)) {
                $room->setDifficulty($difficulty);
            }
        }

        if (!empty($data['themeId'])) {
            $theme = $this->em->find(\App\Entity\Theme::class, (int) $data['themeId']);
            if ($theme) $room->setTheme($theme);
        }

        // Ajouter le couple hôte comme premier participant
        $participant = new RoomParticipant();
        $participant->setCouple($couple);
        $room->addParticipant($participant);
        $this->em->persist($participant);

        $this->em->persist($room);
        $this->em->flush();

        return $this->json($this->serializeRoom($room, $couple), 201);
    }

    // ── POST /api/room/join ─────────────────────────────────────────────────

    #[Route('/join', methods: ['POST'])]
    public function join(
        Request            $request,
        #[CurrentUser] Users $user,
    ): JsonResponse {
        $couple = $this->coupleRepo->findActiveForUser($user);
        if (!$couple) {
            return $this->json(['error' => 'Vous devez être en couple pour rejoindre une salle.'], 403);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $code = strtoupper(trim($data['code'] ?? ''));

        if (empty($code)) {
            return $this->json(['error' => 'Code de salle requis.'], 400);
        }

        $room = $this->roomRepo->findByCode($code);

        if (!$room) {
            return $this->json(['error' => 'Code invalide.'], 404);
        }

        if ($room->getStatus() !== Room::STATUS_WAITING) {
            return $this->json(['error' => 'Cette partie a déjà commencé ou est terminée.'], 409);
        }

        if ($room->isFull()) {
            return $this->json(['error' => 'La salle est complète.'], 409);
        }

        if ($room->hasCouple($couple)) {
            // Déjà dans la salle → retourner l'état actuel
            return $this->json($this->serializeRoom($room, $couple));
        }

        $participant = new RoomParticipant();
        $participant->setCouple($couple);
        $room->addParticipant($participant);
        $this->em->persist($participant);
        $this->em->flush();

        return $this->json($this->serializeRoom($room, $couple));
    }

    // ── GET /api/room/{id}/state ────────────────────────────────────────────

    #[Route('/{id}/state', methods: ['GET'])]
    public function state(
        int                $id,
        #[CurrentUser] Users $user,
    ): JsonResponse {
        $room   = $this->roomRepo->find($id);
        $couple = $this->coupleRepo->findActiveForUser($user);

        if (!$room) return $this->json(['error' => 'Salle introuvable.'], 404);
        if (!$couple || !$room->hasCouple($couple)) {
            return $this->json(['error' => 'Accès refusé.'], 403);
        }

        return $this->json($this->serializeRoom($room, $couple, detailed: true));
    }

    // ── POST /api/room/{id}/start ───────────────────────────────────────────

    #[Route('/{id}/start', methods: ['POST'])]
    public function start(
        int                $id,
        #[CurrentUser] Users $user,
    ): JsonResponse {
        $room   = $this->roomRepo->find($id);
        $couple = $this->coupleRepo->findActiveForUser($user);

        if (!$room) return $this->json(['error' => 'Salle introuvable.'], 404);

        if ($room->getHostCouple()?->getId() !== $couple?->getId()) {
            return $this->json(['error' => 'Seul l\'hôte peut démarrer la partie.'], 403);
        }

        if ($room->getStatus() !== Room::STATUS_WAITING) {
            return $this->json(['error' => 'La partie ne peut pas être démarrée dans cet état.'], 409);
        }

        if ($room->getParticipantCount() < 2) {
            return $this->json(['error' => 'Il faut au moins 2 couples pour commencer.'], 422);
        }

        // Tirer la première carte
        $card = $this->drawCardForRoom($room);
        if (!$card) {
            return $this->json(['error' => 'Aucune carte disponible pour ce thème.'], 500);
        }

        $room->setStatus(Room::STATUS_PLAYING);
        $room->setStartedAt(new \DateTimeImmutable());
        $room->setCurrentCard($card);
        $room->setCurrentCardIndex(0);
        $room->setCardPhase('answering');

        // Réinitialiser le statut de réponse pour tous les participants
        foreach ($room->getParticipants() as $p) {
            $p->resetCardStatus();
        }

        $this->em->flush();
        $this->roomPublisher->publishRoomUpdate($room);

        return $this->json($this->serializeRoom($room, $couple, detailed: true));
    }

    // ── POST /api/room/{id}/next-card ───────────────────────────────────────

    #[Route('/{id}/next-card', methods: ['POST'])]
    public function nextCard(
        int                $id,
        #[CurrentUser] Users $user,
    ): JsonResponse {
        $room   = $this->roomRepo->find($id);
        $couple = $this->coupleRepo->findActiveForUser($user);

        if (!$room) return $this->json(['error' => 'Salle introuvable.'], 404);
        if (!$couple || !$room->hasCouple($couple)) {
            return $this->json(['error' => 'Accès refusé.'], 403);
        }

        if ($room->getStatus() !== Room::STATUS_PLAYING) {
            return $this->json(['error' => 'Partie non active.'], 409);
        }

        // La carte suivante n'est accessible que depuis la phase 'revealed'.
        // Même l'hôte ne peut pas forcer tant que la révélation n'a pas eu lieu.
        if ($room->getCardPhase() !== 'revealed') {
            return $this->json([
                'error' => 'La phase de révélation n\'est pas encore terminée.',
                'phase' => $room->getCardPhase(),
            ], 409);
        }

        $nextIndex = $room->getCurrentCardIndex() + 1;

        // Fin de partie ?
        if ($nextIndex >= $room->getCardCount()) {
            $room->setStatus(Room::STATUS_DONE);
            $room->setEndedAt(new \DateTimeImmutable());
            $this->em->flush();
            $this->roomPublisher->publishRoomEvent($room, 'room_done');
            return $this->json([
                'status' => 'done',
                'scores' => $this->buildScores($room),
            ]);
        }

        // Carte suivante
        $card = $this->drawCardForRoom($room);
        if (!$card) {
            // Plus de cartes → clôture
            $room->setStatus(Room::STATUS_DONE);
            $room->setEndedAt(new \DateTimeImmutable());
            $this->em->flush();
            $this->roomPublisher->publishRoomEvent($room, 'room_done');
            return $this->json([
                'status' => 'done',
                'scores' => $this->buildScores($room),
            ]);
        }

        $room->setCurrentCard($card);
        $room->setCurrentCardIndex($nextIndex);
        $room->setCardPhase('answering');

        foreach ($room->getParticipants() as $p) {
            $p->resetCardStatus();
        }

        $this->em->flush();
        $this->roomPublisher->publishRoomUpdate($room);

        return $this->json($this->serializeRoom($room, $couple, detailed: true));
    }

    // ── POST /api/room/{id}/answer ──────────────────────────────────────────
    // Soumet la réponse d'un couple pour la carte en cours.
    // Quand tous les participants ont répondu → phase passe à 'voting'.

    #[Route('/{id}/answer', methods: ['POST'])]
    public function answer(
        int                $id,
        Request            $request,
        #[CurrentUser] Users $user,
    ): JsonResponse {
        $room   = $this->roomRepo->find($id);
        $couple = $this->coupleRepo->findActiveForUser($user);

        if (!$room) return $this->json(['error' => 'Salle introuvable.'], 404);
        if (!$couple || !$room->hasCouple($couple)) {
            return $this->json(['error' => 'Accès refusé.'], 403);
        }
        if ($room->getStatus() !== Room::STATUS_PLAYING) {
            return $this->json(['error' => 'Partie non active.'], 409);
        }
        if ($room->getCardPhase() !== 'answering') {
            return $this->json(['error' => 'La phase de réponse est terminée.'], 409);
        }

        $data       = json_decode($request->getContent(), true) ?? [];
        $answerText = trim($data['answer'] ?? '');
        if (empty($answerText)) {
            return $this->json(['error' => 'Réponse vide.'], 400);
        }

        // Trouver le participant et enregistrer sa réponse
        $myParticipant = null;
        foreach ($room->getParticipants() as $p) {
            if ($p->getCouple()?->getId() === $couple->getId()) {
                $myParticipant = $p;
                break;
            }
        }

        if (!$myParticipant) {
            return $this->json(['error' => 'Participant introuvable.'], 404);
        }
        if ($myParticipant->isHasAnsweredCurrentCard()) {
            return $this->json(['error' => 'Vous avez déjà répondu.'], 409);
        }

        // ── Timer : rejeter si le temps est écoulé ───────────────────────────
        $sessionCard = $room->getCurrentSessionCard();
        if ($sessionCard !== null && $sessionCard->isTimerExpired()) {
            return $this->json([
                'error' => 'Le temps est écoulé pour cette carte.',
                'phase' => $room->getCardPhase(),
            ], 409);
        }

        $myParticipant->setCurrentAnswer($answerText);
        $myParticipant->setHasAnsweredCurrentCard(true);

        // Vérifier si assez de participants restent pour continuer
        $activeParticipants = $room->getParticipants()->toArray();
        $count = count($activeParticipants);

        // Abandonner la partie s'il ne reste qu'un seul couple
        if ($count < 2) {
            $room->setStatus(Room::STATUS_DONE);
            $room->setEndedAt(new \DateTimeImmutable());
            $this->em->flush();
            return $this->json([
                'error'  => 'Plus assez de participants — partie terminée.',
                'status' => 'done',
                'scores' => $this->buildScores($room),
            ], 409);
        }

        // Vérifier si TOUS les participants actifs ont répondu
        $allAnswered = true;
        foreach ($activeParticipants as $p) {
            if (!$p->isHasAnsweredCurrentCard()) {
                $allAnswered = false;
                break;
            }
        }

        if ($allAnswered) {
            $room->setCardPhase('voting');
        }

        $this->em->flush();
        $this->roomPublisher->publishRoomUpdate($room, $allAnswered ? 'voting_started' : 'answer_submitted');

        return $this->json($this->serializeRoom($room, $couple, detailed: true));
    }

    // ── DELETE /api/room/{id}/leave ─────────────────────────────────────────

    #[Route('/{id}/leave', methods: ['DELETE'])]
    public function leave(
        int                $id,
        #[CurrentUser] Users $user,
    ): JsonResponse {
        $room   = $this->roomRepo->find($id);
        $couple = $this->coupleRepo->findActiveForUser($user);

        if (!$room || !$couple) {
            return $this->json(['error' => 'Salle ou couple introuvable.'], 404);
        }

        // Empêcher de quitter en cours de partie — solo ou multi.
        // En cours de jeu, le joueur doit finir ou utiliser "abandonner la partie".
        if ($room->getStatus() === Room::STATUS_PLAYING) {
            return $this->json([
                'error' => 'Vous ne pouvez pas quitter en cours de partie. Abandonnez la salle à la place.',
            ], 409);
        }

        foreach ($room->getParticipants() as $p) {
            if ($p->getCouple()?->getId() === $couple->getId()) {
                $this->em->remove($p);
                break;
            }
        }

        // Si l'hôte quitte en WAITING → transférer à un autre participant
        if ($room->getStatus() === Room::STATUS_WAITING
            && $room->getHostCouple()?->getId() === $couple->getId()
        ) {
            $remaining = $room->getParticipants()->toArray();
            if (count($remaining) > 0) {
                $room->setHostCouple($remaining[0]->getCouple());
            }
        }

        // Si l'hôte quitte pendant la partie → fin de partie pour tout le monde
        if ($room->getStatus() === Room::STATUS_PLAYING
            && $room->getHostCouple()?->getId() === $couple->getId()
        ) {
            $room->setStatus(Room::STATUS_DONE);
            $room->setEndedAt(new \DateTimeImmutable());
            $this->em->flush();
            return $this->json([
                'message'  => 'L\'hôte a quitté — partie terminée.',
                'status'   => 'done',
                'scores'   => $this->buildScores($room),
            ]);
        }

        $this->em->flush();

        return $this->json(['message' => 'Vous avez quitté la salle.']);
    }

    // ── Helpers privés ──────────────────────────────────────────────────────

    private function drawCardForRoom(Room $room): ?Card
    {
        $themeId    = $room->getTheme()?->getId();
        $excludeId  = $room->getCurrentCard()?->getId();
        $difficulty = $room->getDifficulty();

        // Exclure toutes les cartes déjà jouées dans cette room
        $session = $room->getCurrentSessionCard()?->getSession();
        $playedCardIds = $session !== null
            ? array_map(fn($sc) => $sc->getCard()?->getId(), $session->getSessionCards()->toArray())
            : [];
        $playedCardIds = array_filter($playedCardIds, fn($id) => $id !== null);

        $card = $this->cardRepo->findRandomCard($themeId, $excludeId, $playedCardIds ?: null, $difficulty);

        if (!$card) return null;

        // Créer (ou récupérer) la Session associée à cette salle
        $session = $this->getOrCreateRoomSession($room);

        // Créer une SessionCard pour cette carte (nécessaire pour les votes)
        $sc = new SessionCard();
        $sc->setSession($session);
        $sc->setCard($card);
        $sc->setOrderIndex($room->getCurrentCardIndex());

        // Démarrer le timer si la salle a un timer configuré
        if ($room->getTimerPerCard() !== null) {
            $sc->startTimer($room->getTimerPerCard());
        }

        $this->em->persist($sc);

        // Stocker la référence sur la salle
        $room->setCurrentSessionCard($sc);

        // Flush pour obtenir l'ID avant de dispatch le message async
        $this->em->flush();

        // Dispatcher le message d'expiration timer (traité en async par Messenger)
        if ($room->getTimerPerCard() !== null) {
            $this->messageBus->dispatch(
                new RoomCardTimerExpired($room->getId(), $sc->getId())
            );
        }

        return $card;
    }

    /**
     * Retourne la Session dédiée à cette salle (mode='room').
     * La crée si elle n'existe pas encore.
     */
    private function getOrCreateRoomSession(Room $room): Session
    {
        // Chercher une session existante liée à cette salle
        $existing = $this->sessionRepo->findOneBy(['room' => $room]);
        if ($existing) return $existing;

        $session = new Session();
        $session->setUser($room->getHostCouple()?->getUser1());
        $session->setCouple($room->getHostCouple());
        $session->setRoom($room);
        $session->setMode('room');
        $this->em->persist($session);

        return $session;
    }

    private function buildScores(Room $room): array
    {
        $scores = [];
        foreach ($room->getParticipants() as $p) {
            $scores[] = [
                'coupleId'  => $p->getCouple()?->getId(),
                'pseudo1'   => $p->getCouple()?->getUser1()?->getPseudo(),
                'pseudo2'   => $p->getCouple()?->getUser2()?->getPseudo(),
                'score'     => $p->getScore(),
            ];
        }
        usort($scores, fn($a, $b) => $b['score'] - $a['score']);
        return $scores;
    }

    private function serializeRoom(Room $room, ?Couple $myCouple, bool $detailed = false): array
    {
        $card     = $room->getCurrentCard();
        $myPart   = null;
        $participants = [];

        foreach ($room->getParticipants() as $p) {
            $isMe = $myCouple && $p->getCouple()?->getId() === $myCouple->getId();
            if ($isMe) $myPart = $p;

            // La réponse n'est visible que pendant la phase voting/revealed
            $showAnswer = in_array($room->getCardPhase(), ['voting', 'revealed'], true);
            $participants[] = [
                'coupleId'    => $p->getCouple()?->getId(),
                'pseudo1'     => $p->getCouple()?->getUser1()?->getPseudo(),
                'pseudo2'     => $p->getCouple()?->getUser2()?->getPseudo(),
                'score'       => $p->getScore(),
                'hasAnswered' => $p->isHasAnsweredCurrentCard(),
                'answer'      => $showAnswer ? $p->getCurrentAnswer() : null,
            ];
        }

        $result = [
            'id'               => $room->getId(),
            'code'             => $room->getCode(),
            'status'           => $room->getStatus(),
            'cardPhase'        => $room->getCardPhase(),
            'currentCardIndex' => $room->getCurrentCardIndex(),
            'cardCount'        => $room->getCardCount(),
            'timerPerCard'     => $room->getTimerPerCard(),
            'isHost'           => $myCouple && $room->getHostCouple()?->getId() === $myCouple->getId(),
            'myCoupleId'       => $myCouple?->getId(),  // needed by Flutter to determine who "me" is for voting
            'myScore'          => $myPart?->getScore() ?? 0,
            'participants'     => $participants,
            'currentCard'          => $card ? [
                'id'               => $card->getId(),
                'questionText'     => $card->getQuestionText(),
                'type'             => $card->getType() ?? 'question',
                'themeName'        => $card->getTheme()?->getName(),
            ] : null,
            'currentSessionCardId' => $room->getCurrentSessionCard()?->getId(),
            'myAnswer'             => $myPart?->getCurrentAnswer(),
            'hasAnswered'          => $myPart?->isHasAnsweredCurrentCard() ?? false,
        ];

        if ($detailed && $room->getStatus() === Room::STATUS_DONE) {
            $result['finalScores'] = $this->buildScores($room);
        }

        return $result;
    }
}
