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
        ?array $excludeIds = null,
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

        if ($excludeIds !== null && count($excludeIds) > 0) {
            $qb->andWhere('c.id NOT IN (:excludeIds)')
               ->setParameter('excludeIds', $excludeIds);
        }

        if ($difficulty !== null) {
            $qb->andWhere('c.difficultyLevel = :difficulty')
               ->setParameter('difficulty', $difficulty);
        }

        $qb->orderBy('RANDOM()');

        $result = $qb->getQuery()->setMaxResults(1)->getOneOrNullResult();

        return $result;
    }

    /**
     * Retourne une carte aléatoire en excluant les cartes déjà jouées dans une session.
     */
    public function findRandomCardForSession(
        int $sessionId,
        ?int $themeId = null,
        ?int $difficulty = null,
    ): ?Card {
        $qb = $this->createQueryBuilder('c');

        // Exclure les cartes déjà dans cette session
        $qb->andWhere('c.id NOT IN (
            SELECT IDENTITY(sc2.card) FROM App\Entity\SessionCard sc2 WHERE sc2.session = :sessionId
        )')->setParameter('sessionId', $sessionId);

        if ($themeId !== null) {
            $qb->andWhere('c.theme = :themeId')
               ->setParameter('themeId', $themeId);
        }

        if ($difficulty !== null) {
            $qb->andWhere('c.difficultyLevel = :difficulty')
               ->setParameter('difficulty', $difficulty);
        }

        $qb->orderBy('RANDOM()');

        return $qb->getQuery()->setMaxResults(1)->getOneOrNullResult();
    }

}
