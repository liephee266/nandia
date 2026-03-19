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
        return $this->findRandomCard($themeId);
    }

    /**
     * Retourne une carte aléatoire, avec possibilité d'exclure une carte
     * (pour éviter la répétition consécutive en mode Room).
     *
     * @param int|null $themeId     Filtrer par thème (null = tous)
     * @param int|null $excludeId   Exclure cette carte (éviter répétition)
     * @param int|null $difficulty  Filtrer par niveau de difficulté (1, 2 ou 3)
     */
    public function findRandomCard(
        ?int $themeId = null,
        ?int $excludeId = null,
        ?int $difficulty = null,
    ): ?Card {
        $qb = $this->createQueryBuilder('c');

        if ($themeId !== null) {
            $qb->andWhere('c.theme = :themeId')
               ->setParameter('themeId', $themeId);
        }

        if ($excludeId !== null) {
            $qb->andWhere('c.id != :excludeId')
               ->setParameter('excludeId', $excludeId);
        }

        if ($difficulty !== null) {
            $qb->andWhere('c.difficultyLevel = :difficulty')
               ->setParameter('difficulty', $difficulty);
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

    /**
     * @deprecated  Utilisez findRandomCard() avec le paramètre difficulty
     */
    public function findRandom(?int $themeId = null): ?Card
    {
        return $this->findRandomCard($themeId);
    }
}
