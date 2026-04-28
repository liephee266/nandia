<?php

namespace App\Repository;

use App\Entity\WeeklyChallenge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WeeklyChallengeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeeklyChallenge::class);
    }

    /**
     * Retourne le défi de la semaine courante.
     */
    public function findCurrentChallenge(): ?WeeklyChallenge
    {
        $now = new \DateTimeImmutable();
        return $this->createQueryBuilder('wc')
            ->andWhere('wc.startDate <= :now')
            ->andWhere('wc.endDate >= :now')
            ->setParameter('now', $now)
            ->orderBy('wc.startDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Retourne les N derniers défis.
     */
    public function findRecent(int $limit = 4): array
    {
        return $this->createQueryBuilder('wc')
            ->orderBy('wc.startDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
