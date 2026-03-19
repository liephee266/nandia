<?php
// src/Entity/Room.php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\RoomRepository;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
// SessionCard est importé pour la relation currentSessionCard

/**
 * Salle de jeu multi-couples.
 *
 * Cycle de vie :
 *   1. Le couple hôte crée la salle → status=waiting, code généré
 *   2. D'autres couples rejoignent via POST /api/room/join { code }
 *   3. L'hôte lance → status=playing, currentCard initialisée
 *   4. Après toutes les cartes → status=done, scores calculés
 */
#[ORM\Entity(repositoryClass: RoomRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Patch(denormalizationContext: ['groups' => ['room:patch']]),
    ],
    normalizationContext:   ['groups' => ['room:read']],
    denormalizationContext: ['groups' => ['room:write']]
)]
class Room
{
    public const STATUS_WAITING = 'waiting';   // en attente des participants
    public const STATUS_PLAYING = 'playing';   // partie en cours
    public const STATUS_DONE    = 'done';      // partie terminée

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['room:read'])]
    private ?int $id = null;

    /** Couple qui a créé la salle (hôte) */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['room:read', 'room:write'])]
    private ?Couple $hostCouple = null;

    /** Code 6 caractères pour rejoindre la salle (ex: ROOM-AB34) */
    #[ORM\Column(length: 10, unique: true)]
    #[Groups(['room:read'])]
    private ?string $code = null;

    /** waiting | playing | done */
    #[ORM\Column(length: 20)]
    #[Groups(['room:read', 'room:patch'])]
    private string $status = self::STATUS_WAITING;

    /** Nombre maximum de couples admis */
    #[ORM\Column(type: 'integer', options: ['default' => 8])]
    #[Groups(['room:read', 'room:write'])]
    private int $maxCouples = 8;

    /** Nombre total de cartes à jouer dans la session */
    #[ORM\Column(type: 'integer', options: ['default' => 10])]
    #[Groups(['room:read', 'room:write'])]
    private int $cardCount = 10;

    /** Timer par carte en secondes (null = sans limite) */
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['room:read', 'room:write', 'room:patch'])]
    private ?int $timerPerCard = null;

    /** Niveau de difficulté des cartes (1, 2 ou 3 — null = toutes) */
    #[ORM\Column(type: 'smallint', nullable: true)]
    #[Groups(['room:read', 'room:write'])]
    private ?int $difficulty = null;

    /** Thème joué (null = toutes thèmes mélangés) */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['room:read', 'room:write'])]
    private ?Theme $theme = null;

    /** Index de la carte en cours (0-based) */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    #[Groups(['room:read', 'room:patch'])]
    private int $currentCardIndex = 0;

    /**
     * Carte actuellement affichée à tous les couples.
     * Avancée par l'hôte ou automatiquement quand tous ont répondu.
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['room:read', 'room:patch'])]
    private ?Card $currentCard = null;

    /** Phase de la carte en cours : answering | voting | revealed */
    #[ORM\Column(length: 20, options: ['default' => 'answering'])]
    #[Groups(['room:read', 'room:patch'])]
    private string $cardPhase = 'answering';

    /**
     * SessionCard créée pour la carte en cours (utilisée pour les votes).
     * Nullable car null tant qu'aucune partie n'est lancée.
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['room:read'])]
    private ?SessionCard $currentSessionCard = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['room:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['room:read', 'room:patch'])]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['room:read', 'room:patch'])]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\OneToMany(mappedBy: 'room', targetEntity: RoomParticipant::class, cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['room:read'])]
    private Collection $participants;

    public function __construct()
    {
        $this->createdAt    = new \DateTimeImmutable();
        $this->participants = new ArrayCollection();
        $this->code         = $this->generateCode();
    }

    private function generateCode(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code  = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }

    public function getParticipantCount(): int
    {
        return $this->participants->count();
    }

    public function isFull(): bool
    {
        return $this->participants->count() >= $this->maxCouples;
    }

    public function hasCouple(Couple $couple): bool
    {
        foreach ($this->participants as $p) {
            if ($p->getCouple()?->getId() === $couple->getId()) return true;
        }
        return false;
    }

    // ── Getters / Setters ───────────────────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getHostCouple(): ?Couple { return $this->hostCouple; }
    public function setHostCouple(?Couple $c): self { $this->hostCouple = $c; return $this; }

    public function getCode(): ?string { return $this->code; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $s): self { $this->status = $s; return $this; }

    public function getMaxCouples(): int { return $this->maxCouples; }
    public function setMaxCouples(int $n): self { $this->maxCouples = $n; return $this; }

    public function getCardCount(): int { return $this->cardCount; }
    public function setCardCount(int $n): self { $this->cardCount = $n; return $this; }

    public function getTimerPerCard(): ?int { return $this->timerPerCard; }
    public function setTimerPerCard(?int $s): self { $this->timerPerCard = $s; return $this; }

    public function getDifficulty(): ?int { return $this->difficulty; }
    public function setDifficulty(?int $d): self { $this->difficulty = $d; return $this; }

    public function getTheme(): ?Theme { return $this->theme; }
    public function setTheme(?Theme $t): self { $this->theme = $t; return $this; }

    public function getCurrentCardIndex(): int { return $this->currentCardIndex; }
    public function setCurrentCardIndex(int $i): self { $this->currentCardIndex = $i; return $this; }

    public function getCurrentCard(): ?Card { return $this->currentCard; }
    public function setCurrentCard(?Card $c): self { $this->currentCard = $c; return $this; }

    public function getCardPhase(): string { return $this->cardPhase; }
    public function setCardPhase(string $p): self { $this->cardPhase = $p; return $this; }

    public function getCurrentSessionCard(): ?SessionCard { return $this->currentSessionCard; }
    public function setCurrentSessionCard(?SessionCard $sc): self { $this->currentSessionCard = $sc; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function getStartedAt(): ?\DateTimeImmutable { return $this->startedAt; }
    public function setStartedAt(?\DateTimeImmutable $d): self { $this->startedAt = $d; return $this; }

    public function getEndedAt(): ?\DateTimeImmutable { return $this->endedAt; }
    public function setEndedAt(?\DateTimeImmutable $d): self { $this->endedAt = $d; return $this; }

    public function getParticipants(): Collection { return $this->participants; }

    public function addParticipant(RoomParticipant $p): self
    {
        if (!$this->participants->contains($p)) {
            $this->participants->add($p);
            $p->setRoom($this);
        }
        return $this;
    }
}
