<?php
// src/Entity/Theme.php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ThemeRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ThemeRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        // Création et modification réservées aux admins
        new Post(security: "is_granted('ROLE_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['theme:read']],
    denormalizationContext: ['groups' => ['theme:write']]
)]
class Theme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['theme:read', 'card:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Groups(['theme:read', 'theme:write', 'card:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 400, type: 'text', nullable: true)]
    #[Assert\NotBlank]
    #[Groups(['theme:read', 'theme:write', 'card:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\NotBlank]
    #[Groups(['theme:read', 'theme:write'])]
    private ?string $size = '1x1';

    #[ORM\Column(length: 400, type: 'text', nullable: true)]
    #[Assert\NotBlank]
    #[Groups(['theme:read', 'theme:write', 'card:read'])]
    private ?string $backgroundImage = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['theme:read', 'theme:write'])]
    private ?string $icon = null;

    #[ORM\Column(length: 7, nullable: true)]
    #[Groups(['theme:read', 'theme:write'])]
    private ?string $colorCode = null;

    #[ORM\OneToMany(mappedBy: 'theme', targetEntity: Card::class)]
    private Collection $cards;

    #[ORM\OneToMany(mappedBy: 'theme', targetEntity: Ritual::class)]
    // Inclus dans theme:read ; chaque Ritual est sérialisé avec les champs
    // tagués theme:read dans Ritual.php (id, title, description, type)
    // — le back-reference Ritual→Theme n'est pas tagué theme:read → pas de boucle circulaire
    #[Groups(['theme:read'])]
    private Collection $rituals;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
        $this->rituals = new ArrayCollection();
    }

    // Getters et Setters...
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(?string $size): self
    {
        $this->size = $size;
        return $this;
    }

    public function getBackgroundImage(): ?string
    {
        return $this->backgroundImage;
    }

    public function setBackgroundImage(?string $backgroundImage): self
    {
        $this->backgroundImage = $backgroundImage;
        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function getColorCode(): ?string
    {
        return $this->colorCode;
    }

    public function setColorCode(?string $colorCode): self
    {
        $this->colorCode = $colorCode;
        return $this;
    }

    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function getRituals(): Collection
    {
        return $this->rituals;
    }
}