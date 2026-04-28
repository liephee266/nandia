<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\WeeklyChallengeRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Défi hebdomadaire pour les couples.
 *
 * Chaque semaine, un nouveau défi est proposé :
 *  - 5 cartes d'un thème spécifique
 *  - Objectif : répondre à toutes les cartes avant dimanche minuit
 *  - Récompense : badge exclusif + XP bonus
 */
#[ORM\Entity(repositoryClass: WeeklyChallengeRepository::class)]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_USER')"),
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Patch(security: "is_granted('ROLE_USER')"),
    ],
    normalizationContext: ['groups' => ['weekly_challenge:read']],
    denormalizationContext: ['groups' => ['weekly_challenge:write']]
)]
class WeeklyChallenge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['weekly_challenge:read'])]
    private ?int $id = null;

    /** Semaine ISO (ex: 2025-W03) */
    #[ORM\Column(length: 10, unique: true)]
    #[Groups(['weekly_challenge:read'])]
    private ?string $weekLabel = null;

    /** Date de début du défi */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['weekly_challenge:read'])]
    private ?\DateTimeImmutable $startDate = null;

    /** Date de fin du défi */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['weekly_challenge:read'])]
    private ?\DateTimeImmutable $endDate = null;

    /** Thème imposé pour le défi */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['weekly_challenge:read'])]
    private ?Theme $theme = null;

    /** Nombre de cartes à compléter */
    #[ORM\Column(type: 'integer', options: ['default' => 5])]
    #[Groups(['weekly_challenge:read'])]
    private int $cardTarget = 5;

    /** XP bonus pour les compléteurs */
    #[ORM\Column(type: 'integer', options: ['default' => 50])]
    #[Groups(['weekly_challenge:read'])]
    private int $xpBonus = 50;

    /** Cartes sélectionnées pour ce défi */
    #[ORM\ManyToMany(targetEntity: Card::class)]
    #[ORM\JoinTable(name: 'weekly_challenge_cards')]
    #[Groups(['weekly_challenge:read'])]
    private Collection $cards;

    /** Couples qui ont complété le défi */
    #[ORM\ManyToMany(targetEntity: Couple::class)]
    #[ORM\JoinTable(name: 'weekly_challenge_completions')]
    private Collection $completedBy;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
        $this->completedBy = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getWeekLabel(): ?string { return $this->weekLabel; }
    public function setWeekLabel(string $w): self { $this->weekLabel = $w; return $this; }

    public function getStartDate(): ?\DateTimeImmutable { return $this->startDate; }
    public function setStartDate(\DateTimeImmutable $d): self { $this->startDate = $d; return $this; }

    public function getEndDate(): ?\DateTimeImmutable { return $this->endDate; }
    public function setEndDate(\DateTimeImmutable $d): self { $this->endDate = $d; return $this; }

    public function getTheme(): ?Theme { return $this->theme; }
    public function setTheme(?Theme $t): self { $this->theme = $t; return $this; }

    public function getCardTarget(): int { return $this->cardTarget; }
    public function setCardTarget(int $n): self { $this->cardTarget = $n; return $this; }

    public function getXpBonus(): int { return $this->xpBonus; }
    public function setXpBonus(int $n): self { $this->xpBonus = $n; return $this; }

    public function getCards(): Collection { return $this->cards; }
    public function addCard(Card $c): self { $this->cards->add($c); return $this; }

    public function getCompletedBy(): Collection { return $this->completedBy; }
    public function addCompletion(Couple $c): self { $this->completedBy->add($c); return $this; }

    public function isActive(): bool
    {
        $now = new \DateTimeImmutable();
        return $this->startDate !== null
            && $this->endDate !== null
            && $now >= $this->startDate
            && $now <= $this->endDate;
    }

    public function getProgressForCouple(Couple $couple): int
    {
        $count = 0;
        foreach ($this->completedBy as $c) {
            if ($c->getId() === $couple->getId()) {
                $count++;
            }
        }
        return $count;
    }
}
