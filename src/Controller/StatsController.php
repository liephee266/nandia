<?php

namespace App\Controller;

use App\Repository\CoupleRepository;
use App\Repository\SessionRepository;
use App\Repository\SessionCardRepository;
use App\Repository\ResponseRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
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

    /**
     * Statistiques communes d'un couple.
     * GET /api/stats/couple
     *
     * Retourne :
     *  - partnerPseudo     : pseudo du partenaire
     *  - sessionsCouple    : nombre de sessions couple
     *  - sessionsCompleted : sessions couple terminées
     *  - cardsCouple       : cartes jouées ensemble
     *  - totalFavorites    : cartes favorites issues de sessions couple
     *  - topThemes         : top 3 thèmes joués ensemble [{name, count}]
     */
    #[Route('/stats/couple', name: 'couple_stats', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function coupleStats(
        CoupleRepository      $coupleRepository,
        EntityManagerInterface $em,
    ): JsonResponse {
        /** @var \App\Entity\Users $me */
        $me     = $this->getUser();
        $couple = $coupleRepository->findActiveForUser($me);

        if (!$couple) {
            return $this->json(['error' => 'Aucun couple actif.'], 404);
        }

        $partner = $couple->getUser1()->getId() === $me->getId()
            ? $couple->getUser2()
            : $couple->getUser1();

        $cacheKey = "couple_stats_v1_{$couple->getId()}";
        $item     = $this->statsCache->getItem($cacheKey);

        if ($item->isHit()) {
            return $this->json($item->get());
        }

        // Sessions couple (les deux partenaires peuvent être Session.user)
        $modes = ['couple_live', 'couple_relax'];

        $sessionsCouple = (int) $em->createQuery(
            'SELECT COUNT(s.id) FROM App\Entity\Session s
             WHERE s.couple = :couple AND s.mode IN (:modes)'
        )->setParameters(['couple' => $couple, 'modes' => $modes])
         ->getSingleScalarResult();

        $sessionsCompleted = (int) $em->createQuery(
            'SELECT COUNT(s.id) FROM App\Entity\Session s
             WHERE s.couple = :couple AND s.mode IN (:modes) AND s.endedAt IS NOT NULL'
        )->setParameters(['couple' => $couple, 'modes' => $modes])
         ->getSingleScalarResult();

        $cardsCouple = (int) $em->createQuery(
            'SELECT COUNT(sc.id) FROM App\Entity\SessionCard sc
             JOIN sc.session s
             WHERE s.couple = :couple AND s.mode IN (:modes)'
        )->setParameters(['couple' => $couple, 'modes' => $modes])
         ->getSingleScalarResult();

        $totalFavorites = (int) $em->createQuery(
            'SELECT COUNT(sc.id) FROM App\Entity\SessionCard sc
             JOIN sc.session s
             WHERE s.couple = :couple AND s.mode IN (:modes) AND sc.favorited = true'
        )->setParameters(['couple' => $couple, 'modes' => $modes])
         ->getSingleScalarResult();

        // Top thèmes (GROUP BY theme name, ORDER BY count DESC, LIMIT 3)
        $rows = $em->createQuery(
            'SELECT t.name AS themeName, COUNT(sc.id) AS cnt
             FROM App\Entity\SessionCard sc
             JOIN sc.session s
             JOIN sc.card c
             JOIN c.theme t
             WHERE s.couple = :couple AND s.mode IN (:modes)
             GROUP BY t.id, t.name
             ORDER BY cnt DESC'
        )->setParameters(['couple' => $couple, 'modes' => $modes])
         ->setMaxResults(3)
         ->getArrayResult();

        $topThemes = array_map(fn($r) => [
            'name'  => $r['themeName'],
            'count' => (int) $r['cnt'],
        ], $rows);

        $data = [
            'partnerPseudo'     => $partner?->getPseudo() ?? $partner?->getEmail() ?? '?',
            'sessionsCouple'    => $sessionsCouple,
            'sessionsCompleted' => $sessionsCompleted,
            'cardsCouple'       => $cardsCouple,
            'totalFavorites'    => $totalFavorites,
            'topThemes'         => $topThemes,
        ];

        $item->set($data)->expiresAfter(self::CACHE_TTL);
        $this->statsCache->save($item);

        return $this->json($data);
    }
}
