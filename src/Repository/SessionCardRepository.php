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

    /**
     * Nombre total de cartes piochées sur toutes les sessions d'un utilisateur,
     * en une seule requête SQL (évite le N+1 de StatsController).
     *
     * @param int $userId
     */
    public function countByUserId(int $userId): int
    {
        return (int) $this->createQueryBuilder('sc')
            ->select('COUNT(sc.id)')
            ->join('sc.session', 's')
            ->andWhere('s.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Nombre de réponses soumises en mode couple pour un utilisateur.
     * Utilise user1Response / user2Response sur SessionCard (pas la table Response).
     * Les deux partenaires du couple comptent leurs propres réponses.
     *
     * @param int $userId  L'initiateur de la session (Session.user)
     * @param int $position  1 = user1, 2 = user2 du couple
     */
    public function countCoupleResponsesByUserAndPosition(int $userId, int $position): int
    {
        $field = $position === 1 ? 'sc.user1Response' : 'sc.user2Response';

        return (int) $this->createQueryBuilder('sc')
            ->select('COUNT(sc.id)')
            ->join('sc.session', 's')
            ->join('s.couple', 'c')
            ->andWhere('s.user = :userId')
            ->andWhere('s.mode IN (:modes)')
            ->andWhere("$field IS NOT NULL")
            ->andWhere("$field != :empty")
            ->setParameter('userId', $userId)
            ->setParameter('modes', ['couple_live', 'couple_relax'])
            ->setParameter('empty', '')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
