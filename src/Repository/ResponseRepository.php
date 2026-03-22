<?php

namespace App\Repository;

use App\Entity\Response;
use App\Entity\SessionCard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Response>
 */
class ResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Response::class);
    }

    /**
     * Retourne les entrées de journal pour un utilisateur (solo + couple).
     * Inclut le flag `favorited` pour允许 le toggle depuis le journal.
     *
     * @return array<int, array{id: int, type: string, questionText: ?string,
     *            answerText: ?string, user1Response: ?string, user2Response: ?string,
     *            themeName: ?string, themeColor: ?string,
     *            favorited: bool, createdAt: ?string}>
     */
    public function findJournalForUser(int $userId): array
    {
        // ── Réponses solo (Response) ──────────────────────────────────────────
        $soloEntries = $this->createQueryBuilder('r')
            ->leftJoin('r.sessionCard', 'sc')
            ->leftJoin('sc.card', 'c')
            ->leftJoin('c.theme', 't')
            ->join('r.user', 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $solo = array_map(fn(Response $r) => [
            'id'            => $r->getId(),
            'type'          => 'solo',
            'sessionCardId' => $r->getSessionCard()?->getId(),
            'questionText'  => $r->getSessionCard()?->getCard()?->getQuestionText(),
            'answerText'    => $r->getAnswerText(),
            'note'          => $r->getSessionCard()?->getNote(),
            'user1Response' => null,
            'user2Response' => null,
            'themeName'     => $r->getSessionCard()?->getCard()?->getTheme()?->getName(),
            'themeColor'    => $r->getSessionCard()?->getCard()?->getTheme()?->getColorCode(),
            'favorited'     => $r->getSessionCard()?->isFavorited() ?? false,
            'createdAt'     => $r->getCreatedAt()?->format(\DateTimeInterface::ATOM),
        ], $soloEntries);

        // ── Réponses couple (SessionCard avec au moins une réponse) ───────────
        $coupleEntries = $this->getEntityManager()
            ->getRepository(SessionCard::class)
            ->createQueryBuilder('sc')
            ->leftJoin('sc.card', 'c')
            ->leftJoin('c.theme', 't')
            ->join('sc.session', 's')
            ->join('s.user', 'u')
            ->where('u.id = :userId')
            ->andWhere('(sc.user1Response IS NOT NULL OR sc.user2Response IS NOT NULL)')
            ->setParameter('userId', $userId)
            ->orderBy('sc.user1RespondedAt', 'DESC')
            ->getQuery()
            ->getResult();

        $couple = array_map(fn(SessionCard $sc) => [
            'id'            => 0,
            'type'          => 'couple',
            'sessionCardId' => $sc->getId(),
            'questionText'  => $sc->getCard()?->getQuestionText(),
            'answerText'    => null,
            'note'          => $sc->getNote(),
            'user1Response' => $sc->getUser1Response(),
            'user2Response' => $sc->getUser2Response(),
            'themeName'     => $sc->getCard()?->getTheme()?->getName(),
            'themeColor'    => $sc->getCard()?->getTheme()?->getColorCode(),
            'favorited'     => $sc->isFavorited(),
            'createdAt'     => $sc->getUser1RespondedAt()?->format(\DateTimeInterface::ATOM)
                ?? $sc->getUser2RespondedAt()?->format(\DateTimeInterface::ATOM),
        ], $coupleEntries);

        // ── Fusion + tri par date ─────────────────────────────────────────────
        $all = array_merge($solo, $couple);
        usort($all, fn($a, $b) => ($b['createdAt'] ?? '') <=> ($a['createdAt'] ?? ''));

        return $all;
    }
}
