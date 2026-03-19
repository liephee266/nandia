<?php

// src/Entity/UserBadge.php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserBadgeRepository;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Lien many-to-many entre un utilisateur et ses badges obtenus.
 * La clé primaire composite (user_id, badge_id) garantit l'unicité.
 */
#[ORM\Entity(repositoryClass: UserBadgeRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Get(security: "is_granted('ROLE_USER')"),
    ],
    normalizationContext: ['groups' => ['user_badge:read']],
)]
#[ORM\Table(name: 'user_badge')]
#[ORM\UniqueConstraint(name: 'uq_user_badge', columns: ['user_id', 'badge_id'])]
class UserBadge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user_badge:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['user_badge:read'])]
    private ?Users $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['user_badge:read'])]
    private ?Badge $badge = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['user_badge:read'])]
    private ?\DateTimeImmutable $awardedAt = null;

    public function __construct()
    {
        $this->awardedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getUser(): ?Users { return $this->user; }
    public function setUser(?Users $u): self { $this->user = $u; return $this; }
    public function getBadge(): ?Badge { return $this->badge; }
    public function setBadge(?Badge $b): self { $this->badge = $b; return $this; }
    public function getAwardedAt(): ?\DateTimeImmutable { return $this->awardedAt; }
}
