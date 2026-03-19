<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\ResponseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class JournalController extends AbstractController
{
    /**
     * Retourne les entrées de journal de l'utilisateur connecté.
     * On utilise @CurrentUser plutôt qu'un userId en paramètre
     * pour éviter qu'un utilisateur puisse consulter le journal d'un autre.
     */
    #[Route('/api/journal/me', name: 'api_journal_me', methods: ['GET'])]
    public function __invoke(
        #[CurrentUser] ?Users $currentUser,
        ResponseRepository $responseRepository,
    ): JsonResponse {
        if (!$currentUser) {
            return $this->json(['error' => 'Non authentifié.'], 401);
        }

        // Récupère toutes les réponses de l'utilisateur connecté
        $responses = $responseRepository->findJournalForUser($currentUser->getId());

        return $this->json($responses);
    }
}
