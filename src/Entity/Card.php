<?php
// src/Entity/Card.php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CardRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CardRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post()
    ],
    normalizationContext: ['groups' => ['card:read']],
    denormalizationContext: ['groups' => ['card:write']]
)]
class Card
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['card:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'cards')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['card:read', 'card:write'])]
    private ?Theme $theme = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Groups(['card:read', 'card:write'])]
    private ?string $questionText = null;

    #[ORM\Column(type: 'smallint', nullable: true)]
    #[Groups(['card:read', 'card:write'])]
    private ?int $difficultyLevel = 1;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['card:read', 'card:write'])]
    private bool $isBonus = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getters et Setters...
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): self
    {
        $this->theme = $theme;
        return $this;
    }

    public function getQuestionText(): ?string
    {
        return $this->questionText;
    }

    public function setQuestionText(string $questionText): self
    {
        $this->questionText = $questionText;
        return $this;
    }

    public function getDifficultyLevel(): ?int
    {
        return $this->difficultyLevel;
    }

    public function setDifficultyLevel(?int $difficultyLevel): self
    {
        $this->difficultyLevel = $difficultyLevel;
        return $this;
    }

    public function isIsBonus(): bool
    {
        return $this->isBonus;
    }

    public function setIsBonus(bool $isBonus): self
    {
        $this->isBonus = $isBonus;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}