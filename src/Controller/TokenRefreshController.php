<?php

namespace App\Controller;

use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Renouvelle le JWT à partir d'un refresh_token valide.
 *
 * POST /api/token/refresh
 * Body : { "refresh_token": "<opaque_64hex>" }
 *
 * Réponse succès (200) :
 *   { "token": "<new_jwt>", "refresh_token": "<new_opaque>" }
 *
 * Réponse erreur (401) :
 *   { "error": "Refresh token invalide ou expiré." }
 */
class TokenRefreshController extends AbstractController
{
    public function __construct(
        private readonly UsersRepository          $usersRepository,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly EntityManagerInterface   $em,
    ) {}

    #[Route('/api/token/refresh', name: 'api_token_refresh', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $body         = json_decode($request->getContent(), true) ?? [];
        $refreshToken = $body['refresh_token'] ?? null;

        if (!$refreshToken) {
            return $this->json(['error' => 'Refresh token manquant.'], 400);
        }

        $user = $this->usersRepository->findOneBy(['refreshToken' => $refreshToken]);

        if (!$user || !$user->isRefreshTokenValid()) {
            return $this->json(['error' => 'Refresh token invalide ou expiré.'], 401);
        }

        // Rotation : génère un nouveau refresh token à chaque utilisation
        $newRefreshToken = $user->generateRefreshToken();
        $this->em->flush();

        $newJwt = $this->jwtManager->create($user);

        return $this->json([
            'token'         => $newJwt,
            'refresh_token' => $newRefreshToken,
        ]);
    }
}
