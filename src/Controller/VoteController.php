<?php
// src/Controller/VoteController.php

namespace App\Controller;

use App\Entity\CardVote;
use App\Entity\RoomParticipant;
use App\Entity\SessionCard;
use App\Entity\Users;
use App\Repository\CardVoteRepository;
use App\Repository\CoupleRepository;
use App\Repository\RoomParticipantRepository;
use App\Repository\SessionCardRepository;
use App\Service\RoomStatePublisher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Vote des couples pour les réponses des autres durant une session Room.
 *
 * Règles :
 *  - Vote uniquement en phase "voting" d'une salle
 *  - Un couple = un vote par carte
 *  - Impossible de voter pour soi-même
 *  - +2 pts attribués au couple cible à chaque vote reçu
 *
 * Endpoints :
 *  POST  /api/vote                     → Soumettre un vote
 *  GET   /api/vote/room/{roomId}/card/{sessionCardId}  → Résultats des votes d'une carte
 */
#[Route('/api/vote')]
class VoteController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface    $em,
        private readonly CoupleRepository          $coupleRepo,
        private readonly CardVoteRepository        $voteRepo,
        private readonly SessionCardRepository     $sessionCardRepo,
        private readonly RoomParticipantRepository $participantRepo,
        private readonly RoomStatePublisher        $roomPublisher,
    ) {}

    // ── POST /api/vote ──────────────────────────────────────────────────────

    #[Route('', methods: ['POST'])]
    public function vote(
        Request            $request,
        #[CurrentUser] Users $user,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        $sessionCardId  = (int) ($data['sessionCardId'] ?? 0);
        $targetCoupleId = (int) ($data['targetCoupleId'] ?? 0);

        if (!$sessionCardId || !$targetCoupleId) {
            return $this->json(['error' => 'sessionCardId et targetCoupleId sont requis.'], 400);
        }

        $voterCouple = $this->coupleRepo->findActiveForUser($user);
        if (!$voterCouple) {
            return $this->json(['error' => 'Vous devez être en couple pour voter.'], 403);
        }

        // Récupérer le couple cible
        $targetCouple = $this->em->find(\App\Entity\Couple::class, $targetCoupleId);
        if (!$targetCouple) {
            return $this->json(['error' => 'Couple cible introuvable.'], 404);
        }

        // Pas de vote pour soi-même
        if ($voterCouple->getId() === $targetCouple->getId()) {
            return $this->json(['error' => 'Vous ne pouvez pas voter pour votre propre réponse.'], 400);
        }

        /** @var SessionCard $sessionCard */
        $sessionCard = $this->sessionCardRepo->find($sessionCardId);
        if (!$sessionCard) {
            return $this->json(['error' => 'Carte de session introuvable.'], 404);
        }

        // Vérifier que la salle est bien en phase "voting"
        $room = $sessionCard->getSession()?->getRoom();
        if (!$room || $room->getCardPhase() !== 'voting') {
            return $this->json(['error' => 'Le vote n\'est pas encore ouvert pour cette carte.'], 409);
        }

        // Vérifier que voterCouple est dans la salle
        if (!$room->hasCouple($voterCouple)) {
            return $this->json(['error' => 'Accès refusé.'], 403);
        }

        // Vérifier que le couple cible est dans la salle
        if (!$room->hasCouple($targetCouple)) {
            return $this->json(['error' => ' Couple cible non présent dans cette salle.'], 400);
        }

        // Un seul vote par couple par carte
        $existing = $this->voteRepo->findOneBy([
            'sessionCard'  => $sessionCard,
            'voterCouple'  => $voterCouple,
        ]);

        if ($existing) {
            return $this->json(['error' => 'Vous avez déjà voté pour cette carte.'], 409);
        }

        // Créer le vote
        $vote = new CardVote();
        $vote->setSessionCard($sessionCard);
        $vote->setVoterCouple($voterCouple);
        $vote->setTargetCouple($targetCouple);
        $this->em->persist($vote);

        // Attribuer les points au participant cible
        foreach ($room->getParticipants() as $participant) {
            if ($participant->getCouple()?->getId() === $targetCouple->getId()) {
                $participant->addScore(CardVote::POINTS_PER_VOTE);
                break;
            }
        }

        $this->em->flush();

        // Vérifier si tous les couples ont voté → passer en phase "revealed"
        $totalParticipants = $room->getParticipantCount();
        $totalVotes        = $this->voteRepo->countForSessionCard($sessionCard->getId());

        if ($totalVotes >= $totalParticipants) {
            $room->setCardPhase('revealed');
            $sessionCard->setRevealed(true);
            $this->em->flush();
            $this->roomPublisher->publishRoomEvent($room, 'cards_revealed', [
                'sessionCardId' => $sessionCard->getId(),
            ]);
        } else {
            $this->roomPublisher->publishRoomUpdate($room, 'vote_recorded');
        }

        return $this->json([
            'message'      => 'Vote enregistré.',
            'totalVotes'   => $totalVotes,
            'allVoted'     => $totalVotes >= $totalParticipants,
            'cardPhase'    => $room->getCardPhase(),
        ]);
    }

    // ── GET /api/vote/room/{roomId}/card/{sessionCardId} ────────────────────

    #[Route('/room/{roomId}/card/{sessionCardId}', methods: ['GET'])]
    public function results(
        int                $roomId,
        int                $sessionCardId,
        #[CurrentUser] Users $user,
    ): JsonResponse {
        $voterCouple = $this->coupleRepo->findActiveForUser($user);
        if (!$voterCouple) {
            return $this->json(['error' => 'Accès refusé.'], 403);
        }

        $room = $this->em->find(\App\Entity\Room::class, $roomId);
        if (!$room || !$room->hasCouple($voterCouple)) {
            return $this->json(['error' => 'Salle introuvable ou accès refusé.'], 404);
        }

        $sessionCard = $this->sessionCardRepo->find($sessionCardId);
        if (!$sessionCard) {
            return $this->json(['error' => 'Carte introuvable.'], 404);
        }

        // Fix N+1: requete avec JOIN pour charger les relations en une requete
        $dql = '
            SELECT v, t, u1, u2
            FROM App\Entity\CardVote v
            LEFT JOIN v.targetCouple t
            LEFT JOIN t.user1 u1
            LEFT JOIN t.user2 u2
            WHERE v.sessionCard = :sessionCard
        ';
        $votes = $this->em->createQuery($dql)->setParameter('sessionCard', $sessionCard)->getResult();

        // Grouper les votes par couple cible
        $tally = [];
        foreach ($votes as $v) {
            $targetCouple = $v->getTargetCouple();
            if (!$targetCouple) continue;
            
            $tid = $targetCouple->getId();
            if (!isset($tally[$tid])) {
                $tally[$tid] = [
                    'coupleId' => $tid,
                    'pseudo1'  => $targetCouple->getUser1()?->getPseudo(),
                    'pseudo2'  => $targetCouple->getUser2()?->getPseudo(),
                    'votes'    => 0,
                    'points'   => 0,
                ];
            }
            $tally[$tid]['votes']++;
            $tally[$tid]['points'] += CardVote::POINTS_PER_VOTE;
        }

        usort($tally, fn($a, $b) => $b['votes'] - $a['votes']);

        return $this->json([
            'sessionCardId' => $sessionCardId,
            'results'       => array_values($tally),
        ]);
    }
}
