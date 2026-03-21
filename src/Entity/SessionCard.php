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
        new Get(security: "is_granted('ROLE_USER') and object.getSession().getUser() == user"),
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Post(security: "is_granted('ROLE_USER')"),
        new Patch(security: "is_granted('ROLE_USER') and object.getSession().getUser() == user", denormalizationContext: ['groups' => ['session_card:patch']]),
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

    // ── Champs mode couple ───────────────────────────────────────────────────

    /**
     * Réponse du user1 (en mode couple, remplace la table Response pour le polling).
     * En mode solo, la table Response est toujours utilisée.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['session_card:read', 'session_card:patch'])]
    private ?string $user1Response = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['session_card:read'])]
    private ?\DateTimeImmutable $user1RespondedAt = null;

    /** Réponse du user2 (partenaire) */
    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['session_card:read', 'session_card:patch'])]
    private ?string $user2Response = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['session_card:read'])]
    private ?\DateTimeImmutable $user2RespondedAt = null;

    /**
     * true quand les deux partenaires ont répondu → révélation déclenchée.
     * En mode room : true quand la phase passe à "voting".
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['session_card:read', 'session_card:patch'])]
    private bool $revealed = false;

    /**
     * Numéro du joueur dont c'est le tour (1 ou 2).
     * Utilisé uniquement pour les défis/rituels (mode tour à tour).
     * null en mode simultané.
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['session_card:read', 'session_card:patch'])]
    private ?int $currentTurn = null;

    /** Timestamp d'expiration du timer pour cette carte (null = pas de timer) */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['session_card:read'])]
    private ?\DateTimeImmutable $timerExpiresAt = null;

    /** true = l'utilisateur a ajouté cette carte à ses favoris (journal) */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['session_card:read', 'session_card:patch'])]
    private bool $favorited = false;

    public function __construct()
    {
        $this->drawnAt = new \DateTimeImmutable();
    }

    // ── Helpers couple ───────────────────────────────────────────────────────

    /** Soumet la réponse d'un utilisateur selon sa position dans le couple (1 ou 2) */
    public function submitResponse(int $userPosition, string $text): void
    {
        if ($userPosition === 1) {
            $this->user1Response    = $text;
            $this->user1RespondedAt = new \DateTimeImmutable();
        } else {
            $this->user2Response    = $text;
            $this->user2RespondedAt = new \DateTimeImmutable();
        }
        // Révéler automatiquement si les deux ont répondu
        if ($this->user1RespondedAt !== null && $this->user2RespondedAt !== null) {
            $this->revealed = true;
        }
    }

    public function isBothResponded(): bool
    {
        return $this->user1RespondedAt !== null && $this->user2RespondedAt !== null;
    }

    public function isTimerExpired(): bool
    {
        return $this->timerExpiresAt !== null
            && $this->timerExpiresAt < new \DateTimeImmutable();
    }

    public function startTimer(int $seconds): void
    {
        $this->timerExpiresAt = new \DateTimeImmutable("+{$seconds} seconds");
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

    public function getOrderIndex(): ?int { return $this->orderIndex; }
    public function setOrderIndex(?int $i): self { $this->orderIndex = $i; return $this; }

    public function getUser1Response(): ?string { return $this->user1Response; }
    public function setUser1Response(?string $r): self { $this->user1Response = $r; return $this; }

    public function getUser1RespondedAt(): ?\DateTimeImmutable { return $this->user1RespondedAt; }
    public function setUser1RespondedAt(?\DateTimeImmutable $d): self { $this->user1RespondedAt = $d; return $this; }

    public function getUser2Response(): ?string { return $this->user2Response; }
    public function setUser2Response(?string $r): self { $this->user2Response = $r; return $this; }

    public function getUser2RespondedAt(): ?\DateTimeImmutable { return $this->user2RespondedAt; }
    public function setUser2RespondedAt(?\DateTimeImmutable $d): self { $this->user2RespondedAt = $d; return $this; }

    public function isRevealed(): bool { return $this->revealed; }
    public function setRevealed(bool $r): self { $this->revealed = $r; return $this; }

    public function getCurrentTurn(): ?int { return $this->currentTurn; }
    public function setCurrentTurn(?int $t): self { $this->currentTurn = $t; return $this; }

    public function getTimerExpiresAt(): ?\DateTimeImmutable { return $this->timerExpiresAt; }
    public function setTimerExpiresAt(?\DateTimeImmutable $d): self { $this->timerExpiresAt = $d; return $this; }

    public function isFavorited(): bool { return $this->favorited; }
    public function setFavorited(bool $f): self { $this->favorited = $f; return $this; }
    public function toggleFavorite(): self { $this->favorited = !$this->favorited; return $this; }
}