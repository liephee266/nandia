<?php

namespace App\Controller;

use App\Repository\ResponseRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class JournalController extends AbstractController
{
    /**
     * Retourne les entrées de journal d'un utilisateur :
     * liste des questions jouées avec leurs réponses éventuelles.
     */
    #[Route('/api/journal/{userId}', name: 'api_journal', methods: ['GET'])]
    public function __invoke(
        int $userId,
        ResponseRepository $responseRepository,
        UsersRepository $usersRepository
    ): JsonResponse {
        $user = $usersRepository->find($userId);

        if (!$user) {
            return $this->json(['error' => 'Utilisateur introuvable'], 404);
        }

        // Récupère toutes les réponses de l'utilisateur avec les données imbriquées
        $responses = $responseRepository->findJournalForUser($userId);

        return $this->json($responses);
    }
}
