<?php

namespace App\Service;

use App\Entity\Badge;
use App\Entity\Session;
use App\Entity\UserBadge;
use App\Entity\Users;
use App\Repository\BadgeRepository;
use App\Repository\UserBadgeRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * BadgeAssigner — Vérifie et attribue les badges à un utilisateur.
 *
 * Appelé après :
 *  - Session terminée  → badges de sessions
 *  - Réponse soumise   → badges de réponses
 *  - Couple formé      → badges couple
 *
 * Les badges déjà obtenus ne sont jamais attribués deux fois.
 */
class BadgeAssigner
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly BadgeRepository       $badgeRepo,
        private readonly UserBadgeRepository   $userBadgeRepo,
    ) {}

    /**
     * Vérifie et attribue les badges après une action.
     *
     * @param string $eventType  'session_completed' | 'response_saved' | 'couple_joined' | 'streak_check'
     */
    public function checkAndAssign(Users $user, string $eventType): array
    {
        $newlyAwarded = [];

        $count = match ($eventType) {
            'session_completed'  => $this->countSessions($user),
            'response_saved'    => $this->countResponses($user),
            'couple_joined'      => 1,
            default             => 0,
        };

        $slugs = $this->getSlugsForEvent($eventType, $count);

        foreach ($slugs as $slug) {
            if ($this->userBadgeRepo->hasBadge($user, $slug)) {
                continue; // déjà obtenu
            }

            $badge = $this->badgeRepo->findOneBy(['slug' => $slug]);
            if (!$badge) continue;

            $userBadge = new UserBadge();
            $userBadge->setUser($user);
            $userBadge->setBadge($badge);
            $this->em->persist($userBadge);

            $newlyAwarded[] = $badge;
        }

        if (count($newlyAwarded) > 0) {
            $this->em->flush();
        }

        return $newlyAwarded;
    }

    /** badges attribués à un utilisateur */
    public function getBadgesForUser(Users $user): array
    {
        return $this->userBadgeRepo->findByUserWithBadge($user);
    }

    /** tous les badges disponibles */
    public function getAllBadges(): array
    {
        return $this->badgeRepo->findAllOrdered();
    }

    /**
     * Vérifie et attribue les badges de série (streak).
     * À appeler après chaque session complétée.
     */
    public function checkStreak(Users $user): array
    {
        $streak = $this->calculateStreak($user);
        $newlyAwarded = [];

        $slugs = $this->getSlugsForEvent('streak_check', $streak);

        foreach ($slugs as $slug) {
            if ($this->userBadgeRepo->hasBadge($user, $slug)) {
                continue;
            }
            $badge = $this->badgeRepo->findOneBy(['slug' => $slug]);
            if (!$badge) continue;

            $userBadge = new UserBadge();
            $userBadge->setUser($user);
            $userBadge->setBadge($badge);
            $this->em->persist($userBadge);
            $newlyAwarded[] = $badge;
        }

        if (count($newlyAwarded) > 0) {
            $this->em->flush();
        }

        return $newlyAwarded;
    }

    /**
     * Calcule la série actuelle de jours consécutifs avec au moins une session.
     */
    private function calculateStreak(Users $user): int
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('s.endedAt')
            ->from(Session::class, 's')
            ->andWhere('s.user = :user')
            ->andWhere('s.endedAt IS NOT NULL')
            ->setParameter('user', $user)
            ->orderBy('s.endedAt', 'DESC');

        $dates = array_map(
            fn($r) => $r['endedAt']->format('Y-m-d'),
            $qb->getQuery()->getArrayResult()
        );

        $dates = array_values(array_unique($dates));
        if (count($dates) === 0) {
            return 0;
        }

        // Sécurité : on ne compte pas un streak qui commence par plus de 24h depuis aujourd'hui
        $today = (new \DateTimeImmutable())->format('Y-m-d');
        $yesterday = (new \DateTimeImmutable('-1 day'))->format('Y-m-d');
        if ($dates[0] !== $today && $dates[0] !== $yesterday) {
            return 0;
        }

        $streak = 1;
        for ($i = 1; $i < count($dates); $i++) {
            $prev = new \DateTimeImmutable($dates[$i - 1]);
            $curr = new \DateTimeImmutable($dates[$i]);
            $diff = $prev->diff($curr)->days;

            if ($diff === 1) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }

    // ── Helpers privés ─────────────────────────────────────────────────────

    private function countSessions(Users $user): int
    {
        return (int) $this->em->createQueryBuilder()
            ->select('COUNT(s.id)')
            ->from(Session::class, 's')
            ->andWhere('s.user = :user')
            ->andWhere('s.endedAt IS NOT NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function countResponses(Users $user): int
    {
        return (int) $this->em->createQueryBuilder()
            ->select('COUNT(r.id)')
            ->from(\App\Entity\Response::class, 'r')
            ->andWhere('r.user = :user')
            ->andWhere('r.answerText IS NOT NULL')
            ->andWhere('r.answerText != :empty')
            ->setParameter('user', $user)
            ->setParameter('empty', '')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function getSlugsForEvent(string $eventType, int $count): array
    {
        return match ($eventType) {
            'session_completed' => match (true) {
                $count >= 50 => [Badge::SLUG_FIFTY_SESSIONS, Badge::SLUG_TEN_SESSIONS, Badge::SLUG_FIRST_SESSION],
                $count >= 10 => [Badge::SLUG_TEN_SESSIONS, Badge::SLUG_FIRST_SESSION],
                $count >= 1  => [Badge::SLUG_FIRST_SESSION],
                default      => [],
            },
            'response_saved' => match (true) {
                $count >= 100 => [Badge::SLUG_HUNDRED_RESPONSES, Badge::SLUG_TEN_RESPONSES, Badge::SLUG_FIRST_RESPONSE],
                $count >= 10  => [Badge::SLUG_TEN_RESPONSES, Badge::SLUG_FIRST_RESPONSE],
                $count >= 1   => [Badge::SLUG_FIRST_RESPONSE],
                default       => [],
            },
            'couple_joined' => [Badge::SLUG_COUPLE_JOINED],
            'streak_check' => match (true) {
                $count >= 30 => [Badge::SLUG_STREAK_30, Badge::SLUG_STREAK_7, Badge::SLUG_STREAK_3],
                $count >= 7  => [Badge::SLUG_STREAK_7, Badge::SLUG_STREAK_3],
                $count >= 3  => [Badge::SLUG_STREAK_3],
                default      => [],
            },
            default         => [],
        };
    }
}
