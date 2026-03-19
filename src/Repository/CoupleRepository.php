<?php

namespace App\Repository;

use App\Entity\Couple;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Couple>
 */
class CoupleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Couple::class);
    }

    /** Couple actif dont l'utilisateur est user1 ou user2 */
    public function findActiveForUser(Users $user): ?Couple
    {
        return $this->createQueryBuilder('c')
            ->andWhere('(c.user1 = :user OR c.user2 = :user)')
            ->andWhere('c.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', Couple::STATUS_ACTIVE)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** Invitation en attente initiée par l'utilisateur (user1 uniquement) */
    public function findPendingForUser(Users $user): ?Couple
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user1 = :user')
            ->andWhere('c.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', Couple::STATUS_PENDING)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** Recherche par code d'invitation (insensible à la casse) */
    public function findByCode(string $code): ?Couple
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.inviteCode = :code')
            ->andWhere('c.status = :status')
            ->setParameter('code', strtoupper($code))
            ->setParameter('status', Couple::STATUS_PENDING)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
