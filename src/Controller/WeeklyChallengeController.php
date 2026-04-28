<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\Couple;
use App\Entity\Session;
use App\Entity\SessionCard;
use App\Entity\WeeklyChallenge;
use App\Repository\CardRepository;
use App\Repository\CoupleRepository;
use App\Repository\WeeklyChallengeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/weekly-challenge', name: 'api_weekly_challenge_')]
#[IsGranted('ROLE_USER')]
class WeeklyChallengeController extends AbstractController
{
    public function __construct(
        private readonly WeeklyChallengeRepository $challengeRepo,
        private readonly EntityManagerInterface $em,
        private readonly CardRepository $cardRepo,
        private readonly CoupleRepository $coupleRepo,
    ) {}

    /**
     * GET /api/v1/weekly-challenge/current
     * Retourne le défi de la semaine en cours avec progression.
     */
    #[Route('/current', name: 'current', methods: ['GET'])]
    public function getCurrentChallenge(): JsonResponse
    {
        /** @var \App\Entity\Users $user */
        $user = $this->getUser();
        $couple = $this->coupleRepo->findActiveForUser($user);

        $challenge = $this->challengeRepo->findCurrentChallenge();

        if (!$challenge) {
            return $this->json([
                'active' => false,
                'message' => 'Aucun défi cette semaine. Revenez lundi !',
            ]);
        }

        $cards = [];
        foreach ($challenge->getCards() as $card) {
            $cards[] = [
                'id' => $card->getId(),
                'questionText' => $card->getQuestionText(),
                'difficultyLevel' => $card->getDifficultyLevel(),
            ];
        }

        $progress = 0;
        if ($couple) {
            $progress = $challenge->getProgressForCouple($couple);
        }

        $daysLeft = max(0, $challenge->getEndDate()?->diff(new \DateTimeImmutable())->days ?? 0);

        return $this->json([
            'active' => true,
            'id' => $challenge->getId(),
            'weekLabel' => $challenge->getWeekLabel(),
            'theme' => $challenge->getTheme()?->getName(),
            'cardTarget' => $challenge->getCardTarget(),
            'xpBonus' => $challenge->getXpBonus(),
            'progress' => $progress,
            'daysLeft' => $daysLeft,
            'cards' => $cards,
            'endDate' => $challenge->getEndDate()?->format('c'),
        ]);
    }

    /**
     * POST /api/v1/weekly-challenge/complete
     * Marque le défi comme complété pour le couple.
     */
    #[Route('/complete', name: 'complete', methods: ['POST'])]
    public function completeChallenge(): JsonResponse
    {
        /** @var \App\Entity\Users $user */
        $user = $this->getUser();
        $couple = $this->coupleRepo->findActiveForUser($user);

        if (!$couple) {
            return $this->json(['error' => 'Aucun couple actif.'], 404);
        }

        $challenge = $this->challengeRepo->findCurrentChallenge();
        if (!$challenge) {
            return $this->json(['error' => 'Aucun défi actif.'], 404);
        }

        if (!$challenge->isActive()) {
            return $this->json(['error' => 'Le défi est expiré.'], 400);
        }

        // Vérifier la progression
        $progress = $challenge->getProgressForCouple($couple);
        if ($progress < $challenge->getCardTarget()) {
            return $this->json([
                'error' => "Défi non complété : $progress/{$challenge->getCardTarget()} cartes.",
            ], 400);
        }

        // Marquer comme complété
        $challenge->addCompletion($couple);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'xpBonus' => $challenge->getXpBonus(),
            'message' => 'Défi complété ! +' . $challenge->getXpBonus() . ' XP',
        ]);
    }

    /**
     * GET /api/v1/weekly-challenge/history
     * Historique des défis passés.
     */
    #[Route('/history', name: 'history', methods: ['GET'])]
    public function getHistory(): JsonResponse
    {
        $challenges = $this->challengeRepo->findRecent(4);

        $data = array_map(function (WeeklyChallenge $c) {
            return [
                'weekLabel' => $c->getWeekLabel(),
                'theme' => $c->getTheme()?->getName(),
                'cardTarget' => $c->getCardTarget(),
                'isActive' => $c->isActive(),
                'endDate' => $c->getEndDate()?->format('c'),
            ];
        }, $challenges);

        return $this->json($data);
    }
}
