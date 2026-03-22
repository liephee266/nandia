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
        $conn = $this->getEntityManager()->getConnection();

        $where  = [];
        $params = [];

        if ($themeId !== null) {
            $where[]           = 'c.theme_id = :themeId';
            $params['themeId'] = $themeId;
        }
        if ($excludeId !== null) {
            $where[]            = 'c.id != :excludeId';
            $params['excludeId'] = $excludeId;
        }
        if (!empty($excludeIds)) {
            $where[]             = 'c.id NOT IN (:excludeIds)';
            $params['excludeIds'] = implode(',', array_map('intval', $excludeIds));
        }
        if ($difficulty !== null) {
            $where[]              = 'c.difficulty_level = :difficulty';
            $params['difficulty'] = $difficulty;
        }

        $sql = 'SELECT c.id FROM card c'
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
            . ' ORDER BY RANDOM() LIMIT 1';

        // Remplace le placeholder IN manuellement pour éviter les problèmes de binding de tableau
        if (!empty($excludeIds)) {
            $sql = str_replace(':excludeIds', $params['excludeIds'], $sql);
            unset($params['excludeIds']);
        }

        $id = $conn->fetchOne($sql, $params);

        return $id !== false ? $this->find((int) $id) : null;
    }

    /**
     * Retourne une carte aléatoire en excluant les cartes déjà jouées dans une session.
     */
    public function findRandomCardForSession(
        int $sessionId,
        ?int $themeId = null,
        ?int $difficulty = null,
    ): ?Card {
        $conn   = $this->getEntityManager()->getConnection();
        $where  = ['c.id NOT IN (SELECT sc.card_id FROM session_card sc WHERE sc.session_id = :sessionId)'];
        $params = ['sessionId' => $sessionId];

        if ($themeId !== null) {
            $where[]           = 'c.theme_id = :themeId';
            $params['themeId'] = $themeId;
        }
        if ($difficulty !== null) {
            $where[]              = 'c.difficulty_level = :difficulty';
            $params['difficulty'] = $difficulty;
        }

        $sql = 'SELECT c.id FROM card c WHERE ' . implode(' AND ', $where) . ' ORDER BY RANDOM() LIMIT 1';
        $id  = $conn->fetchOne($sql, $params);

        return $id !== false ? $this->find((int) $id) : null;
    }

}
