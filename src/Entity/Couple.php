<?php
// src/Entity/Couple.php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\CoupleRepository;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Lien entre deux utilisateurs formant un couple dans l'application.
 *
 * Cycle de vie :
 *   1. user1 appelle POST /api/couples → status=pending, inviteCode généré
 *   2. user2 appelle POST /api/couple/join { code } → status=active
 *   3. Dissolution : PATCH /api/couples/{id} { status: "ended" }
 */
#[ORM\Entity(repositoryClass: CoupleRepository::class)]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_USER') and object.hasUser(user)"),
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Patch(security: "is_granted('ROLE_USER') and object.hasUser(user)", denormalizationContext: ['groups' => ['couple:patch']]),
    ],
    normalizationContext:   ['groups' => ['couple:read']],
    denormalizationContext: ['groups' => ['couple:write']]
)]
class Couple
{
    // ── Statuts possibles ────────────────────────────────────────────────────
    public const STATUS_PENDING = 'pending';   // code généré, partenaire pas encore rejoint
    public const STATUS_ACTIVE  = 'active';    // les deux utilisateurs sont liés
    public const STATUS_ENDED   = 'ended';     // lien dissous

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['couple:read'])]
    private ?int $id = null;

    /** Utilisateur qui a initié l'invitation */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['couple:read', 'couple:write'])]
    private ?Users $user1 = null;

    /** Utilisateur qui a rejoint (null jusqu'à l'acceptation) */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    #[Groups(['couple:read'])]
    private ?Users $user2 = null;

    /** pending | active | ended */
    #[ORM\Column(length: 20)]
    #[Groups(['couple:read', 'couple:patch'])]
    private string $status = self::STATUS_PENDING;

    /** Code 6 caractères alphanumérique (ex: XK72AB). Affiché "NANDIA-XK72AB". */
    #[ORM\Column(length: 10, unique: true)]
    #[Groups(['couple:read'])]
    private ?string $inviteCode = null;

    /** Le code expire 48 h après sa création */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['couple:read'])]
    private ?\DateTimeImmutable $inviteExpiresAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['couple:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['couple:read'])]
    private ?\DateTimeImmutable $activatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->inviteCode = $this->generateCode();
        $this->inviteExpiresAt = new \DateTimeImmutable('+48 hours');
    }

    // ── Génération du code ──────────────────────────────────────────────────

    private function generateCode(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // sans O/0/I/1 (confusion)
        $code  = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }

    public function regenerateCode(): void
    {
        $this->inviteCode      = $this->generateCode();
        $this->inviteExpiresAt = new \DateTimeImmutable('+48 hours');
    }

    public function isInviteValid(): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->inviteExpiresAt !== null
            && $this->inviteExpiresAt > new \DateTimeImmutable();
    }

    public function activate(Users $user2): void
    {
        $this->user2       = $user2;
        $this->status      = self::STATUS_ACTIVE;
        $this->activatedAt = new \DateTimeImmutable();
    }

    /** Retourne true si l'utilisateur donné appartient à ce couple */
    public function hasUser(Users $user): bool
    {
        return ($this->user1?->getId() === $user->getId())
            || ($this->user2?->getId() === $user->getId());
    }

    /** Retourne l'autre membre du couple */
    public function getPartner(Users $user): ?Users
    {
        if ($this->user1?->getId() === $user->getId()) return $this->user2;
        if ($this->user2?->getId() === $user->getId()) return $this->user1;
        return null;
    }

    // ── Getters / Setters ───────────────────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getUser1(): ?Users { return $this->user1; }
    public function setUser1(?Users $u): self { $this->user1 = $u; return $this; }

    public function getUser2(): ?Users { return $this->user2; }
    public function setUser2(?Users $u): self { $this->user2 = $u; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $s): self { $this->status = $s; return $this; }

    public function getInviteCode(): ?string { return $this->inviteCode; }

    public function getInviteExpiresAt(): ?\DateTimeImmutable { return $this->inviteExpiresAt; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function getActivatedAt(): ?\DateTimeImmutable { return $this->activatedAt; }
}
