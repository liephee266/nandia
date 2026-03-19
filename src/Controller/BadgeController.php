<?php

namespace App\Controller;

use App\Entity\Users;
use App\Service\BadgeAssigner;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BadgeController extends AbstractController
{
    public function __construct(
        private readonly BadgeAssigner $badgeAssigner,
    ) {}

    // ── GET /api/badges ────────────────────────────────────────────────────

    /**
     * Retourne la liste de tous les badges disponibles
     * (réservé aux admins — les users ont /api/badges/me).
     */
    #[Route('/api/badges', name: 'api_badges_all', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function all(): JsonResponse
    {
        return $this->json($this->badgeAssigner->getAllBadges());
    }

    // ── GET /api/badges/me ─────────────────────────────────────────────────

    /**
     * Retourne les badges de l'utilisateur connecté,
     * avec 'earnedAt' pour ceux qui sont obtenus
     * et earned=false pour les autres.
     *
     * Avant de retourner, on vérifie et attribue les badges en attente
     * (mode "claim" — l'utilisateur "réclame" ses badges à chaque visite
     * de la page, ce qui met à jour ses achievements).
     */
    #[Route('/api/badges/me', name: 'api_badges_me', methods: ['GET'])]
    public function myBadges(
        #[CurrentUser] ?Users $user,
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Non authentifié.'], 401);
        }

        // Tentative d'attribution des badges en attente (silencieux)
        try {
            $this->badgeAssigner->checkAndAssign($user, 'session_completed');
            $this->badgeAssigner->checkAndAssign($user, 'response_saved');
        } catch (\Throwable) {
            // Si le badging échoue (Bdd pas prête, etc.), on continue sans erreur
        }

        $allBadges    = $this->badgeAssigner->getAllBadges();
        $earnedBadges = $this->badgeAssigner->getBadgesForUser($user);

        // Map pour accès O(1) par slug
        $earnedMap = [];
        foreach ($earnedBadges as $ub) {
            $slug = $ub->getBadge()?->getSlug();
            if ($slug) {
                $earnedMap[$slug] = $ub->getAwardedAt();
            }
        }

        $result = array_map(fn($badge) => [
            'id'          => $badge->getId(),
            'slug'        => $badge->getSlug(),
            'name'        => $badge->getName(),
            'description' => $badge->getDescription(),
            'type'        => $badge->getType(),
            'threshold'   => $badge->getThreshold(),
            'iconPath'    => $badge->getIconPath(),
            'earned'      => isset($earnedMap[$badge->getSlug()]),
            'earnedAt'    => $earnedMap[$badge->getSlug()]
                ? $earnedMap[$badge->getSlug()]->format(\DateTimeInterface::ATOM)
                : null,
        ], $allBadges);

        return $this->json($result);
    }
}
