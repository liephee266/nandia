<?php

namespace App\Service;

use App\Entity\Card;
use App\Entity\CardVote;
use App\Entity\Couple;
use App\Entity\Room;
use App\Entity\RoomParticipant;
use App\Entity\SessionCard;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Moteur de jeu pour les rooms multi-couples.
 * Extrait la logique métier de RoomController (579 lignes).
 */
class RoomGameEngine
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    /**
     * Crée une nouvelle room avec le couple créateur comme participant.
     */
    public function createRoom(Users $creator, Couple $creatorCouple, array $options = []): array
    {
        $room = new Room();
        $room->setHostCouple($creatorCouple);

        if (isset($options['maxCouples'])) {
            $room->setMaxCouples((int) $options['maxCouples']);
        }
        if (isset($options['cardCount'])) {
            $room->setCardCount((int) $options['cardCount']);
        }
        if (isset($options['timerPerCard'])) {
            $room->setTimerPerCard((int) $options['timerPerCard']);
        }
        if (isset($options['difficulty'])) {
            $room->setDifficulty((int) $options['difficulty']);
        }

        $participant = new RoomParticipant();
        $participant->setCouple($creatorCouple);
        $room->addParticipant($participant);

        $this->em->persist($room);
        $this->em->flush();

        return [
            'roomId' => $room->getId(),
            'code' => $room->getCode(),
            'maxCouples' => $room->getMaxCouples(),
            'cardCount' => $room->getCardCount(),
            'timerPerCard' => $room->getTimerPerCard(),
            'difficulty' => $room->getDifficulty(),
        ];
    }

    /**
     * Fait rejoindre un couple à une room.
     */
    public function joinRoom(Couple $couple, string $code): array
    {
        $room = $this->em->getRepository(Room::class)->findOneBy(['code' => $code]);
        if (!$room) {
            return ['error' => 'Salle introuvable.'];
        }

        if ($room->getStatus() !== Room::STATUS_WAITING) {
            return ['error' => 'La partie a déjà commencé.'];
        }

        if ($room->isFull()) {
            return ['error' => 'La salle est pleine.'];
        }

        if ($room->hasCouple($couple)) {
            return ['error' => 'Vous êtes déjà dans cette salle.'];
        }

        $participant = new RoomParticipant();
        $participant->setCouple($couple);
        $room->addParticipant($participant);

        $this->em->flush();

        return [
            'roomId' => $room->getId(),
            'code' => $room->getCode(),
            'status' => $room->getStatus(),
            'participantCount' => $room->getParticipantCount(),
        ];
    }

    /**
     * Démarre la room : passe en status PLAYING, tire la première carte.
     */
    public function startRoom(Room $room): array
    {
        if ($room->getStatus() !== Room::STATUS_WAITING) {
            return ['error' => 'La room n\'est pas en attente.'];
        }

        $room->setStatus(Room::STATUS_PLAYING);
        $room->setStartedAt(new \DateTimeImmutable());

        $this->drawNextCard($room);
        $this->em->flush();

        return $this->buildRoomState($room);
    }

    /**
     * Tire la prochaine carte pour la room.
     */
    public function nextCard(Room $room): array
    {
        $cardCount = $room->getCardCount();
        $currentIndex = $room->getCurrentCardIndex();

        if ($currentIndex >= $cardCount) {
            $room->setStatus(Room::STATUS_DONE);
            $room->setEndedAt(new \DateTimeImmutable());
            $this->em->flush();
            return ['status' => 'done', 'finalScores' => $this->buildFinalScores($room)];
        }

        $this->drawNextCard($room);
        $this->em->flush();

        return $this->buildRoomState($room);
    }

    /**
     * Soumet une réponse pour le couple dans la room.
     */
    public function submitAnswer(Room $room, Couple $couple, string $answer): array
    {
        $sessionCard = $room->getCurrentSessionCard();
        if (!$sessionCard) {
            return ['error' => 'Aucune carte active.'];
        }

        // Position 1 = user1 du couple, 2 = user2
        $position = $room->getHostCouple()?->getId() === $couple->getId() ? 1 : 2;
        $sessionCard->submitResponse($position, $answer);
        $this->em->flush();

        return $this->buildRoomState($room);
    }

    /**
     * Vote pour un couple cible. Chaque vote vaut CardVote::POINTS_PER_VOTE points.
     */
    public function submitVote(Room $room, Couple $voterCouple, int $targetCoupleId): array
    {
        $sessionCard = $room->getCurrentSessionCard();
        if (!$sessionCard) {
            return ['error' => 'Aucune carte active.'];
        }

        // Récupérer le couple cible
        $targetCouple = $this->em->getRepository(Couple::class)->find($targetCoupleId);
        if (!$targetCouple) {
            return ['error' => 'Couple cible introuvable.'];
        }

        // Vérifier si le vote existe déjà
        $existingVote = $this->em->getRepository(CardVote::class)->findOneBy([
            'sessionCard' => $sessionCard,
            'voterCouple' => $voterCouple,
        ]);

        if ($existingVote) {
            $existingVote->setTargetCouple($targetCouple);
        } else {
            $vote = new CardVote();
            $vote->setSessionCard($sessionCard);
            $vote->setVoterCouple($voterCouple);
            $vote->setTargetCouple($targetCouple);
            $this->em->persist($vote);
        }

        $this->em->flush();

        return $this->buildRoomState($room);
    }

    /**
     * Fait quitter un couple de la room.
     */
    public function leaveRoom(Room $room, Couple $couple): bool
    {
        foreach ($room->getParticipants() as $p) {
            if ($p->getCouple()?->getId() === $couple->getId()) {
                $room->getParticipants()->removeElement($p);
                $this->em->flush();
                return true;
            }
        }
        return false;
    }

    /**
     * Construit l'état complet de la room pour le client.
     */
    public function buildRoomState(Room $room, ?Couple $requestingCouple = null): array
    {
        $sessionCard = $room->getCurrentSessionCard();
        $participants = [];

        foreach ($room->getParticipants() as $p) {
            $c = $p->getCouple();
            $hasAnswered = false;
            $hasVoted = false;

            if ($sessionCard) {
                $hasAnswered = ($c->getId() === $room->getHostCouple()?->getId())
                    ? $sessionCard->getUser1Response() !== null
                    : $sessionCard->getUser2Response() !== null;

                if ($requestingCouple) {
                    $voteRepo = $this->em->getRepository(CardVote::class);
                    $hasVoted = $voteRepo->findOneBy([
                        'sessionCard' => $sessionCard,
                        'voterCouple' => $requestingCouple,
                    ]) !== null;
                }
            }

            $participants[] = [
                'pseudo1' => $c->getUser1()?->getPseudo(),
                'pseudo2' => $c->getUser2()?->getPseudo(),
                'avatar1' => $c->getUser1()?->getProfileImage(),
                'avatar2' => $c->getUser2()?->getProfileImage(),
                'coupleId' => $c->getId(),
                'hasAnswered' => $hasAnswered,
                'hasVoted' => $hasVoted,
            ];
        }

        $state = [
            'id' => $room->getId(),
            'code' => $room->getCode(),
            'status' => $room->getStatus(),
            'maxCouples' => $room->getMaxCouples(),
            'cardCount' => $room->getCardCount(),
            'currentCardIndex' => $room->getCurrentCardIndex(),
            'timerPerCard' => $room->getTimerPerCard(),
            'difficulty' => $room->getDifficulty(),
            'participants' => $participants,
            'cardPhase' => $room->getCardPhase(),
            'startedAt' => $room->getStartedAt()?->format('c'),
        ];

        if ($sessionCard) {
            $card = $sessionCard->getCard();
            $state['currentCard'] = [
                'sessionCardId' => $sessionCard->getId(),
                'questionText' => $card?->getQuestionText(),
                'type' => $card?->getType(),
                'themeName' => $card?->getTheme()?->getName(),
                'revealed' => $sessionCard->isRevealed(),
                'bothAnswered' => $sessionCard->isBothResponded(),
                'timerSecondsLeft' => $this->getTimerSecondsLeft($sessionCard, $room),
            ];

            if ($requestingCouple) {
                $isHost = $requestingCouple->getId() === $room->getHostCouple()?->getId();
                $state['currentCard']['myAnswer'] = $isHost
                    ? $sessionCard->getUser1Response()
                    : $sessionCard->getUser2Response();
                $state['hasAnswered'] = $isHost
                    ? $sessionCard->getUser1Response() !== null
                    : $sessionCard->getUser2Response() !== null;
            }
        }

        if ($room->getStatus() === Room::STATUS_DONE) {
            $state['finalScores'] = $this->buildFinalScores($room);
            $state['endedAt'] = $room->getEndedAt()?->format('c');
        }

        return $state;
    }

    /**
     * Construit le classement final avec scores.
     */
    public function buildFinalScores(Room $room): array
    {
        $scores = [];

        foreach ($room->getParticipants() as $p) {
            $c = $p->getCouple();
            $totalScore = 0;

            // Somme des scores reçus de tous les autres couples
            foreach ($room->getParticipants() as $other) {
                if ($other->getCouple()?->getId() === $c->getId()) continue;

                $votes = $this->em->getRepository(CardVote::class)->findBy([
                    'voterCouple' => $other->getCouple(),
                    'targetCoupleId' => $c->getId(),
                ]);

                foreach ($votes as $vote) {
                    $totalScore += CardVote::POINTS_PER_VOTE;
                }
            }

            $scores[] = [
                'coupleId' => $c->getId(),
                'pseudo1' => $c->getUser1()?->getPseudo(),
                'pseudo2' => $c->getUser2()?->getPseudo(),
                'score' => $totalScore,
            ];
        }

        usort($scores, fn($a, $b) => $b['score'] <=> $a['score']);
        return $scores;
    }

    // ── Private helpers ────────────────────────────────────────────────────

    private function drawNextCard(Room $room): void
    {
        $room->setCurrentCardIndex($room->getCurrentCardIndex() + 1);

        $qb = $this->em->getRepository(Card::class)->createQueryBuilder('c');
        $qb->orderBy('RANDOM()')->setMaxResults(1);

        if ($room->getDifficulty() !== null) {
            $qb->andWhere('c.difficultyLevel = :diff')
               ->setParameter('diff', $room->getDifficulty());
        }

        $card = $qb->getQuery()->getOneOrNullResult();
        if ($card) {
            $sessionCard = new SessionCard();
            $sessionCard->setCard($card);
            $room->setCurrentCard($card);
            $room->setCurrentSessionCard($sessionCard);
            $room->setCardPhase('answering');

            if ($room->getTimerPerCard() !== null) {
                $sessionCard->startTimer($room->getTimerPerCard());
            }

            $this->em->persist($sessionCard);
        }
    }

    private function getTimerSecondsLeft(SessionCard $sessionCard, Room $room): ?int
    {
        if ($room->getTimerPerCard() === null || $sessionCard->getTimerExpiresAt() === null) {
            return null;
        }

        $remaining = $sessionCard->getTimerExpiresAt()->getTimestamp() - time();
        return max(0, $remaining);
    }
}
