<?php

namespace App\Repository;

use App\Entity\Card;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Card>
 */
class CardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    /**
     * Retourne une carte aléatoire, optionnellement filtrée par thème.
     */
    public function findRandom(?int $themeId = null): ?Card
    {
        $qb = $this->createQueryBuilder('c');

        if ($themeId !== null) {
            $qb->andWhere('c.theme = :themeId')
               ->setParameter('themeId', $themeId);
        }

        $ids = $qb->select('c.id')
            ->getQuery()
            ->getSingleColumnResult();

        if (empty($ids)) {
            return null;
        }

        $randomId = $ids[array_rand($ids)];

        return $this->find($randomId);
    }
}
