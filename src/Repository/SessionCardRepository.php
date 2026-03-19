<?php

namespace App\Repository;

use App\Entity\SessionCard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SessionCard>
 */
class SessionCardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SessionCard::class);
    }

    /**
     * Retourne la carte active (non révélée et non passée) d'une session.
     * En mode couple, la "carte active" est la dernière carte tirée non encore révélée.
     */
    public function findActiveCardForSession(int $sessionId): ?SessionCard
    {
        return $this->createQueryBuilder('sc')
            ->andWhere('sc.session = :sessionId')
            ->andWhere('sc.skipped = false')
            ->andWhere('sc.revealed = false')
            ->setParameter('sessionId', $sessionId)
            ->orderBy('sc.orderIndex', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Nombre total de cartes dans une session (révélées + en cours).
     */
    public function countForSession(int $sessionId): int
    {
        return (int) $this->createQueryBuilder('sc')
            ->select('COUNT(sc.id)')
            ->andWhere('sc.session = :sessionId')
            ->setParameter('sessionId', $sessionId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
