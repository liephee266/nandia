<?php

namespace App\Controller;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Déconnexion : révoque le refresh token côté backend.
 *
 * POST /api/logout
 * Headers : Authorization: Bearer <jwt>
 *
 * Le JWT ne peut pas être invalidé individuellement (stateless),
 * il reste valide jusqu'à son expiration naturelle.
 * Le refresh token est révoqué immédiatement.
 */
class LogoutController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function __invoke(
        #[CurrentUser] ?Users $user,
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Non authentifié.'], 401);
        }

        // Révoquer le refresh token (le JWT reste valide jusqu'à expiration)
        $user->revokeRefreshToken();
        $this->em->flush();

        return $this->json(['message' => 'Déconnexion réussie.']);
    }
}
