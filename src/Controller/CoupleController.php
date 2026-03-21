<?php
// src/Controller/CoupleController.php

namespace App\Controller;

use App\Entity\Couple;
use App\Entity\Users;
use App\Repository\CoupleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Gestion du lien couple entre deux utilisateurs.
 *
 * Endpoints :
 *  POST   /api/couple/create          → Créer une invitation (génère le code)
 *  POST   /api/couple/join            → Rejoindre via code
 *  GET    /api/couple/me              → Récupérer son couple actif (ou pending)
 *  POST   /api/couple/regenerate      → Regénérer le code si expiré
 *  DELETE /api/couple/leave           → Dissoudre le lien
 */
#[Route('/api/couple')]
class CoupleController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CoupleRepository       $coupleRepo,
    ) {}

    // ── POST /api/couple/create ─────────────────────────────────────────────

    #[Route('/create', methods: ['POST'])]
    public function create(#[CurrentUser] Users $user): JsonResponse
    {
        // Vérifier que l'utilisateur n'a pas déjà un couple actif
        $existing = $this->coupleRepo->findActiveForUser($user);
        if ($existing) {
            return $this->json([
                'error' => 'Vous avez déjà un partenaire actif.',
                'coupleId' => $existing->getId(),
            ], 409);
        }

        // Supprimer les anciennes invitations expirées avant d'en créer une nouvelle
        $expired = $this->coupleRepo->findPendingForUser($user);
        if ($expired && !$expired->isInviteValid()) {
            $this->em->remove($expired);
            $this->em->flush();
        }

        // Vérifier qu'il n'a pas déjà une invitation en attente (valide)
        $pending = $this->coupleRepo->findPendingForUser($user);
        if ($pending && $pending->isInviteValid()) {
            return $this->json($this->serializeCouple($pending));
        }

        // Créer une nouvelle invitation
        $couple = new Couple();
        $couple->setUser1($user);

        $this->em->persist($couple);
        $this->em->flush();

        return $this->json($this->serializeCouple($couple), 201);
    }

    // ── POST /api/couple/join ───────────────────────────────────────────────

    #[Route('/join', methods: ['POST'])]
    public function join(
        Request            $request,
        #[CurrentUser] Users $user,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];
        $code = strtoupper(trim($data['code'] ?? ''));

        if (empty($code)) {
            return $this->json(['error' => 'Code requis.'], 400);
        }

        $couple = $this->coupleRepo->findByCode($code);

        if (!$couple) {
            return $this->json(['error' => 'Code invalide ou expiré.'], 404);
        }

        if (!$couple->isInviteValid()) {
            return $this->json(['error' => 'Ce code a expiré. Demandez un nouveau code à votre partenaire.'], 410);
        }

        // L'initiateur ne peut pas rejoindre sa propre invitation
        if ($couple->getUser1()?->getId() === $user->getId()) {
            return $this->json(['error' => 'Vous ne pouvez pas rejoindre votre propre invitation.'], 400);
        }

        // Vérifier que user2 n'a pas déjà un couple actif
        $existing = $this->coupleRepo->findActiveForUser($user);
        if ($existing) {
            return $this->json([
                'error' => 'Vous avez déjà un partenaire actif.',
                'coupleId' => $existing->getId(),
            ], 409);
        }

        $couple->activate($user);
        $this->em->flush();

        return $this->json($this->serializeCouple($couple));
    }

    // ── GET /api/couple/me ──────────────────────────────────────────────────

    #[Route('/me', methods: ['GET'])]
    public function me(#[CurrentUser] Users $user): JsonResponse
    {
        // D'abord chercher un couple actif
        $couple = $this->coupleRepo->findActiveForUser($user);

        // Sinon chercher une invitation en attente
        if (!$couple) {
            $couple = $this->coupleRepo->findPendingForUser($user);
        }

        if (!$couple) {
            return $this->json(['couple' => null]);
        }

        return $this->json($this->serializeCouple($couple));
    }

    // ── POST /api/couple/regenerate ─────────────────────────────────────────

    #[Route('/regenerate', methods: ['POST'])]
    public function regenerate(#[CurrentUser] Users $user): JsonResponse
    {
        $couple = $this->coupleRepo->findPendingForUser($user);

        if (!$couple) {
            return $this->json(['error' => 'Aucune invitation en attente trouvée.'], 404);
        }

        if ($couple->getUser1()?->getId() !== $user->getId()) {
            return $this->json(['error' => 'Seul l\'initiateur peut regénérer le code.'], 403);
        }

        $couple->regenerateCode();
        $this->em->flush();

        return $this->json($this->serializeCouple($couple));
    }

    // ── DELETE /api/couple/leave ────────────────────────────────────────────

    #[Route('/leave', methods: ['DELETE'])]
    public function leave(#[CurrentUser] Users $user): JsonResponse
    {
        $couple = $this->coupleRepo->findActiveForUser($user)
            ?? $this->coupleRepo->findPendingForUser($user);

        if (!$couple) {
            return $this->json(['error' => 'Aucun lien couple à dissoudre.'], 404);
        }

        $couple->setStatus(Couple::STATUS_ENDED);
        $this->em->flush();

        return $this->json(['message' => 'Lien dissous.']);
    }

    // ── Serialisation ───────────────────────────────────────────────────────

    private function serializeCouple(Couple $c): array
    {
        $user2 = $c->getUser2();
        return [
            'id'               => $c->getId(),
            'status'           => $c->getStatus(),
            'inviteCode'       => $c->getInviteCode(),
            'inviteLink'       => 'nandia.app/join/' . $c->getInviteCode(),
            'inviteExpiresAt'  => $c->getInviteExpiresAt()?->format(\DateTimeInterface::ATOM),
            'createdAt'        => $c->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'activatedAt'      => $c->getActivatedAt()?->format(\DateTimeInterface::ATOM),
            'user1'            => $this->serializeUser($c->getUser1()),
            'user2'            => $user2 ? $this->serializeUser($user2) : null,
        ];
    }

    private function serializeUser(?Users $u): ?array
    {
        if (!$u) return null;
        return [
            'id'     => $u->getId(),
            'pseudo' => $u->getPseudo(),
            'avatar' => $u->getProfileImage(),
        ];
    }
}
