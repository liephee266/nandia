<?php
// src/Repository/FavoriteCardRepository.php

namespace App\Repository;

use App\Entity\FavoriteCard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FavoriteCard>
 */
class FavoriteCardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FavoriteCard::class);
    }

    /**
     * Retourne les IDs de cartes mises en favori par un utilisateur.
     *
     * @return int[]
     */
    public function findFavoriteCardIdsByUser(int $userId): array
    {
        $rows = $this->createQueryBuilder('f')
            ->select('IDENTITY(f.card) as cardId')
            ->where('IDENTITY(f.user) = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getScalarResult();

        return array_column($rows, 'cardId');
    }
}
