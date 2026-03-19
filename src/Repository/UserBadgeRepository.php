<?php

namespace App\Repository;

use App\Entity\UserBadge;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserBadge>
 */
class UserBadgeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBadge::class);
    }

    /**
     * Retourne les badges obtenus par un utilisateur avec les données Badge.
     */
    public function findByUserWithBadge(Users $user): array
    {
        return $this->createQueryBuilder('ub')
            ->join('ub.badge', 'b')
            ->andWhere('ub.user = :user')
            ->setParameter('user', $user)
            ->orderBy('ub.awardedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si un utilisateur a déjà un badge donné.
     */
    public function hasBadge(Users $user, string $badgeSlug): bool
    {
        $result = $this->createQueryBuilder('ub')
            ->join('ub.badge', 'b')
            ->andWhere('ub.user = :user')
            ->andWhere('b.slug = :slug')
            ->setParameter('user', $user)
            ->setParameter('slug', $badgeSlug)
            ->select('1')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result !== null;
    }
}
