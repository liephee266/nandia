<?php

namespace App\MessageHandler;

use App\Entity\Room;
use App\Entity\SessionCard;
use App\Message\RoomCardTimerExpired;
use App\Repository\RoomRepository;
use App\Repository\SessionCardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Handle the RoomCardTimerExpired message asynchronously.
 *
 * Ce handler est déclenché quand le timer d'une carte expire.
 * Il passe la room en phase voting (si assez de réponses)
 * ou révèle les réponses automatiquement.
 *
 * Plus de race condition entre la dernière réponse et le timer —
 * le handler async traite ça proprement.
 */
#[AsMessageHandler]
class RoomCardTimerExpiredHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RoomRepository         $roomRepo,
        private readonly SessionCardRepository $sessionCardRepo,
    ) {}

    public function __invoke(RoomCardTimerExpired $message): void
    {
        /** @var Room|null $room */
        $room = $this->roomRepo->find($message->roomId);
        if (!$room || $room->getStatus() !== Room::STATUS_PLAYING) {
            return; // Room already done or not found — skip
        }

        /** @var SessionCard|null $card */
        $card = $this->sessionCardRepo->find($message->sessionCardId);
        if (!$card || $card->isRevealed()) {
            return; // Card already revealed
        }

        // Only act if card phase is still "answering"
        if ($room->getCardPhase() !== 'answering') {
            return;
        }

        // Count how many participants have answered
        $answered = 0;
        $total = 0;
        foreach ($room->getParticipants() as $participant) {
            $total++;
            if ($participant->isHasAnsweredCurrentCard()) {
                $answered++;
            }
        }

        // If everyone answered OR timer expired → go to voting phase
        if ($answered >= $total && $total > 0) {
            $room->setCardPhase('voting');
        } else {
            // Force reveal (timer expired with missing answers)
            $room->setCardPhase('voting');
        }

        $card->setRevealed(true);
        $this->em->flush();
    }
}
