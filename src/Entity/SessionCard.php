<?php
// src/Entity/SessionCard.php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\SessionCardRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SessionCardRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
            new Patch(denormalizationContext: ['groups' => ['session_card:patch']]),
    ],
    normalizationContext: ['groups' => ['session_card:read']],
    denormalizationContext: ['groups' => ['session_card:write']]
)]
class SessionCard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['session_card:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sessionCards')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['session_card:read', 'session_card:write'])]
    private ?Session $session = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['session_card:read', 'session_card:write'])]
    private ?Card $card = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['session_card:read', 'session_card:write'])]
    private ?\DateTimeImmutable $drawnAt = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['session_card:read', 'session_card:write', 'session_card:patch'])]
    private bool $skipped = false;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['session_card:read', 'session_card:write'])]
    private ?int $orderIndex = null;

    public function __construct()
    {
        $this->drawnAt = new \DateTimeImmutable();
    }

    // Getters et Setters...
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): self
    {
        $this->session = $session;
        return $this;
    }

    public function getCard(): ?Card
    {
        return $this->card;
    }

    public function setCard(?Card $card): self
    {
        $this->card = $card;
        return $this;
    }

    public function getDrawnAt(): ?\DateTimeImmutable
    {
        return $this->drawnAt;
    }

    public function getSkipped(): bool
    {
        return $this->skipped;
    }

    public function setSkipped(bool $skipped): self
    {
        $this->skipped = $skipped;
        return $this;
    }

    public function getOrderIndex(): ?int
    {
        return $this->orderIndex;
    }

    public function setOrderIndex(?int $orderIndex): self
    {
        $this->orderIndex = $orderIndex;
        return $this;
    }
}