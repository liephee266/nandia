<?php

namespace App\Repository;

use App\Entity\CardVote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CardVote>
 */
class CardVoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CardVote::class);
    }

    public function countForSessionCard(int $sessionCardId): int
    {
        return (int) $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->andWhere('v.sessionCard = :scId')
            ->setParameter('scId', $sessionCardId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
