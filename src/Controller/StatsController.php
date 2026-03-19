<?php
// src/Controller/StatsController.php

namespace App\Controller;

use App\Repository\SessionRepository;
use App\Repository\SessionCardRepository;
use App\Repository\ResponseRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_')]
class StatsController extends AbstractController
{
    /**
     * Statistiques de jeu d'un utilisateur.
     * GET /api/stats/{userId}
     *
     * Retourne :
     *  - sessionsCount  : nombre total de sessions
     *  - sessionsCompleted : sessions ayant une date de fin
     *  - cardsCount     : nombre total de cartes piochées
     *  - responsesCount : nombre de réponses écrites
     */
    #[Route('/stats/{userId}', name: 'user_stats', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function stats(
        int $userId,
        UsersRepository $usersRepository,
        SessionRepository $sessionRepository,
        SessionCardRepository $sessionCardRepository,
        ResponseRepository $responseRepository,
    ): JsonResponse {
        $user = $usersRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur introuvable.'], 404);
        }

        // Seul l'utilisateur lui-même peut voir ses stats
        if ($this->getUser()->getUserIdentifier() !== $user->getUserIdentifier()) {
            return $this->json(['error' => 'Accès refusé.'], 403);
        }

        // Nombre de sessions
        $sessions = $sessionRepository->findBy(['user' => $user]);
        $sessionsCount = count($sessions);
        $sessionsCompleted = count(array_filter(
            $sessions,
            fn($s) => $s->getEndedAt() !== null
        ));

        // Nombre de cartes piochées (SessionCard liées aux sessions de l'utilisateur)
        $cardsCount = 0;
        foreach ($sessions as $session) {
            $cardsCount += $sessionCardRepository->count(['session' => $session]);
        }

        // Nombre de réponses
        $responsesCount = $responseRepository->count(['user' => $user]);

        return $this->json([
            'sessionsCount'     => $sessionsCount,
            'sessionsCompleted' => $sessionsCompleted,
            'cardsCount'        => $cardsCount,
            'responsesCount'    => $responsesCount,
        ]);
    }
}
