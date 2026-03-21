<?php

namespace App\Controller;

use App\Repository\SessionRepository;
use App\Repository\SessionCardRepository;
use App\Repository\ResponseRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;

#[Route('/api', name: 'api_')]
class StatsController extends AbstractController
{
    private const CACHE_TTL = 300; // 5 minutes

    public function __construct(
        #[Target('app.stats_cache')]
        private readonly CacheItemPoolInterface $statsCache,
    ) {}

    /**
     * Statistiques de jeu d'un utilisateur.
     * GET /api/stats/{userId}
     *
     * Retourne :
     *  - sessionsCount     : nombre total de sessions
     *  - sessionsCompleted : sessions ayant une date de fin
     *  - cardsCount        : nombre total de cartes piochées
     *  - responsesCount    : réponses écrites (solo via Response + couple via SessionCard)
     *
     * Toutes les valeurs sont calculées en SQL agrégé (pas de N+1).
     * Les stats sont mises en cache 5 min par utilisateur.
     */
    #[Route('/stats/{userId}', name: 'user_stats', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function stats(
        int                   $userId,
        UsersRepository       $usersRepository,
        SessionRepository     $sessionRepository,
        SessionCardRepository $sessionCardRepository,
        ResponseRepository    $responseRepository,
    ): JsonResponse {
        $user = $usersRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur introuvable.'], 404);
        }

        // Seul l'utilisateur lui-même peut voir ses stats
        if ($this->getUser()->getUserIdentifier() !== $user->getUserIdentifier()) {
            return $this->json(['error' => 'Accès refusé.'], 403);
        }

        $cacheKey = "user_stats_v2_{$userId}";

        $item = $this->statsCache->getItem($cacheKey);

        if ($item->isHit()) {
            $stats = $item->get();
        } else {
            // Réponses en mode solo (table Response)
            $soloResponses = $responseRepository->count(['user' => $user]);

            // Réponses en mode couple (user1Response + user2Response sur SessionCard)
            $coupleResponsesUser1 = $sessionCardRepository->countCoupleResponsesByUserAndPosition($userId, 1);
            $coupleResponsesUser2 = $sessionCardRepository->countCoupleResponsesByUserAndPosition($userId, 2);

            $stats = [
                'sessionsCount'     => $sessionRepository->countByUserId($userId),
                'sessionsCompleted' => $sessionRepository->countCompletedByUserId($userId),
                'cardsCount'        => $sessionCardRepository->countByUserId($userId),
                'responsesCount'    => $soloResponses + $coupleResponsesUser1 + $coupleResponsesUser2,
            ];

            $item->set($stats);
            $item->expiresAfter(self::CACHE_TTL);
            $this->statsCache->save($item);
        }

        return $this->json($stats);
    }
}
