<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PackRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Pack de cartes premium (monétisation).
 *
 * Un pack contient un ensemble de cartes exclusives accessibles après achat.
 * L'utilisateur peut acheter un pack via Stripe (web) ou In-App Purchase (mobile).
 */
#[ORM\Entity(repositoryClass: PackRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['pack:read']]),
        new GetCollection(normalizationContext: ['groups' => ['pack:read']]),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            denormalizationContext: ['groups' => ['pack:write']]
        ),
    ],
    normalizationContext: ['groups' => ['pack:read']],
    denormalizationContext: ['groups' => ['pack:write']]
)]
class Pack
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['pack:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Groups(['pack:read', 'pack:write'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['pack:read', 'pack:write'])]
    private ?string $description = null;

    /** Prix en euros (ex: "4.99") */
    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2, nullable: true)]
    #[Groups(['pack:read', 'pack:write'])]
    private ?string $price = null;

    /** Nombre de cartes dans le pack */
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    #[Groups(['pack:read'])]
    private int $cardCount = 0;

    /** Image de couverture du pack */
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['pack:read', 'pack:write'])]
    private ?string $coverImage = null;

    /** Identifiant produit Apple/Google pour les achats in-app */
    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['pack:read', 'pack:write'])]
    private ?string $iapProductId = null;

    /** true = pack gratuit (ex: pack découverte) */
    #[ORM\Column(options: ['default' => false])]
    #[Groups(['pack:read', 'pack:write'])]
    private bool $isFree = false;

    /** true = pack affiché dans la boutique */
    #[ORM\Column(options: ['default' => true])]
    #[Groups(['pack:read', 'pack:write'])]
    private bool $isActive = true;

    /** Cartes associées au pack */
    #[ORM\ManyToMany(targetEntity: Card::class)]
    #[ORM\JoinTable(name: 'pack_cards')]
    #[Groups(['pack:read'])]
    private Collection $cards;

    /** Utilisateurs ayant acheté ce pack */
    #[ORM\ManyToMany(targetEntity: Users::class, mappedBy: 'purchasedPacks')]
    private Collection $purchasedBy;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
        $this->purchasedBy = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getPrice(): ?string { return $this->price; }
    public function setPrice(?string $price): self { $this->price = $price; return $this; }

    public function getCardCount(): int { return $this->cardCount; }
    public function setCardCount(int $n): self { $this->cardCount = $n; return $this; }

    public function getCoverImage(): ?string { return $this->coverImage; }
    public function setCoverImage(?string $img): self { $this->coverImage = $img; return $this; }

    public function getIapProductId(): ?string { return $this->iapProductId; }
    public function setIapProductId(?string $id): self { $this->iapProductId = $id; return $this; }

    public function isFree(): bool { return $this->isFree; }
    public function setIsFree(bool $free): self { $this->isFree = $free; return $this; }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $active): self { $this->isActive = $active; return $this; }

    public function getCards(): Collection { return $this->cards; }
    public function addCard(Card $card): self {
        if (!$this->cards->contains($card)) {
            $this->cards->add($card);
            $this->cardCount = $this->cards->count();
        }
        return $this;
    }
    public function removeCard(Card $card): self {
        $this->cards->removeElement($card);
        $this->cardCount = $this->cards->count();
        return $this;
    }

    public function getPurchasedBy(): Collection { return $this->purchasedBy; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
}
