<?php

// src/Entity/Badge.php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\BadgeRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Badges obtainable par un utilisateur.
 *
 * Chaque badge a un identifiant unique (slug) et une condition d'obtention.
 * Les badges sont assignés automatiquement par le BadgeAssigner.
 *
 * Types :
 *  - sessions  : basé sur le nombre de sessions terminées
 *  - responses : basé sur le nombre de réponses données
 *  - streak    : basé sur le nombre de jours consécutifs
 *  - couple    : basé sur l'inscription dans un couple
 */
#[ORM\Entity(repositoryClass: BadgeRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(security: "is_granted('ROLE_ADMIN')"),
        new Post(security: "is_granted('ROLE_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['badge:read']],
    denormalizationContext: ['groups' => ['badge:write']]
)]
class Badge
{
    // ── Slugs de badges disponibles ─────────────────────────────────────────
    public const SLUG_FIRST_SESSION    = 'first_session';
    public const SLUG_TEN_SESSIONS    = 'ten_sessions';
    public const SLUG_FIFTY_SESSIONS  = 'fifty_sessions';
    public const SLUG_FIRST_RESPONSE   = 'first_response';
    public const SLUG_TEN_RESPONSES   = 'ten_responses';
    public const SLUG_HUNDRED_RESPONSES = 'hundred_responses';
    public const SLUG_COUPLE_JOINED   = 'couple_joined';
    public const SLUG_ROOM_HOST       = 'room_host';
    public const SLUG_STREAK_3        = 'streak_3';
    public const SLUG_STREAK_7        = 'streak_7';
    public const SLUG_STREAK_30       = 'streak_30';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['badge:read'])]
    private ?int $id = null;

    /** Slug unique (ex: "ten_sessions") */
    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    #[Groups(['badge:read', 'badge:write'])]
    private ?string $slug = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Groups(['badge:read', 'badge:write'])]
    private ?string $name = null;

    #[ORM\Column(type: 'text')]
    #[Groups(['badge:read', 'badge:write'])]
    private ?string $description = null;

    /**
     * Type de condition : 'sessions' | 'responses' | 'streak' | 'couple' | 'room'
     */
    #[ORM\Column(length: 20)]
    #[Groups(['badge:read', 'badge:write'])]
    private string $type = 'sessions';

    /** Seuil numérique à atteindre pour débloquer le badge */
    #[ORM\Column(type: 'integer')]
    #[Groups(['badge:read', 'badge:write'])]
    private int $threshold = 1;

    /** Chemin de l'icône (à servir depuis /build/badges/) */
    #[ORM\Column(length: 200, nullable: true)]
    #[Groups(['badge:read', 'badge:write'])]
    private ?string $iconPath = null;

    /** Ordre d'affichage dans le mur de badges */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    #[Groups(['badge:read'])]
    private int $displayOrder = 0;

    public function getId(): ?int { return $this->id; }
    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(string $s): self { $this->slug = $s; return $this; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $n): self { $this->name = $n; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $d): self { $this->description = $d; return $this; }
    public function getType(): string { return $this->type; }
    public function setType(string $t): self { $this->type = $t; return $this; }
    public function getThreshold(): int { return $this->threshold; }
    public function setThreshold(int $t): self { $this->threshold = $t; return $this; }
    public function getIconPath(): ?string { return $this->iconPath; }
    public function setIconPath(?string $p): self { $this->iconPath = $p; return $this; }
    public function getDisplayOrder(): int { return $this->displayOrder; }
    public function setDisplayOrder(int $o): self { $this->displayOrder = $o; return $this; }
}
