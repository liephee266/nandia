<?php
// src/Entity/Session.php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\SessionRepository;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(),
    ],
    normalizationContext: ['groups' => ['session:read']],
    denormalizationContext: ['groups' => ['session:write']]
)]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['session:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['session:read', 'session:write'])]
    private ?Users $user = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['session:read', 'session:write'])]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['session:read', 'session:write'])]
    private ?\DateTimeImmutable $endedAt = null;

    /**
     * Mode de jeu :
     *  - solo          : un seul joueur (mode existant)
     *  - couple_live   : deux partenaires en temps réel (polling)
     *  - couple_relax  : deux partenaires asynchrones
     *  - room          : session liée à une salle multi-couples
     */
    #[ORM\Column(length: 20, options: ['default' => 'solo'])]
    #[Groups(['session:read', 'session:write'])]
    private string $mode = 'solo';

    // Thème choisi au démarrage de la session (null = mode aléatoire toutes thèmes)
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['session:read', 'session:write'])]
    private ?Theme $theme = null;

    /** Couple associé (null en mode solo) */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['session:read', 'session:write'])]
    private ?Couple $couple = null;

    /** Salle associée (uniquement en mode room) */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['session:read', 'session:write'])]
    private ?Room $room = null;

    /** Nombre de cartes à jouer dans cette session (null = illimité) */
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['session:read', 'session:write'])]
    private ?int $cardCount = null;

    /** Durée par carte en secondes (null = sans limite) */
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['session:read', 'session:write'])]
    private ?int $timerPerCard = null;

    #[ORM\OneToMany(mappedBy: 'session', targetEntity: SessionCard::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $sessionCards;

    public function __construct()
    {
        $this->sessionCards = new ArrayCollection();
        $this->startedAt = new \DateTimeImmutable();
    }

    // Getters et Setters...
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): self
    {
        $this->startedAt = $startedAt;
        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): self
    {
        $this->endedAt = $endedAt;
        return $this;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(?string $mode): self
    {
        $this->mode = $mode;
        return $this;
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

    public function getCouple(): ?Couple { return $this->couple; }
    public function setCouple(?Couple $c): self { $this->couple = $c; return $this; }

    public function getRoom(): ?Room { return $this->room; }
    public function setRoom(?Room $r): self { $this->room = $r; return $this; }

    public function getCardCount(): ?int { return $this->cardCount; }
    public function setCardCount(?int $n): self { $this->cardCount = $n; return $this; }

    public function getTimerPerCard(): ?int { return $this->timerPerCard; }
    public function setTimerPerCard(?int $s): self { $this->timerPerCard = $s; return $this; }

    public function getSessionCards(): Collection
    {
        return $this->sessionCards;
    }

    public function addSessionCard(SessionCard $sessionCard): self
    {
        if (!$this->sessionCards->contains($sessionCard)) {
            $this->sessionCards->add($sessionCard);
            $sessionCard->setSession($this);
        }
        return $this;
    }

    public function removeSessionCard(SessionCard $sessionCard): self
    {
        if ($this->sessionCards->removeElement($sessionCard)) {
            if ($sessionCard->getSession() === $this) {
                $sessionCard->setSession(null);
            }
        }
        return $this;
    }
}