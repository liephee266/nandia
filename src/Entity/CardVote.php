<?php
// src/Entity/CardVote.php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\CardVoteRepository;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Vote d'un couple pour la réponse d'un autre couple lors d'une session Room.
 *
 * Règles :
 *  - Un couple ne peut pas voter pour lui-même
 *  - Un couple ne peut voter qu'une fois par SessionCard
 *  - Chaque vote accordé vaut +2 points pour le couple cible
 */
#[ORM\Entity(repositoryClass: CardVoteRepository::class)]
#[ORM\UniqueConstraint(
    name: 'uniq_vote_per_card',
    columns: ['session_card_id', 'voter_couple_id']
)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
    ],
    normalizationContext:   ['groups' => ['card_vote:read']],
    denormalizationContext: ['groups' => ['card_vote:write']]
)]
class CardVote
{
    public const POINTS_PER_VOTE = 2;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['card_vote:read'])]
    private ?int $id = null;

    /** La carte de session sur laquelle porte le vote */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['card_vote:read', 'card_vote:write'])]
    private ?SessionCard $sessionCard = null;

    /** Couple qui vote */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['card_vote:read', 'card_vote:write'])]
    private ?Couple $voterCouple = null;

    /** Couple dont la réponse est préférée */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['card_vote:read', 'card_vote:write'])]
    private ?Couple $targetCouple = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['card_vote:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // ── Getters / Setters ───────────────────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getSessionCard(): ?SessionCard { return $this->sessionCard; }
    public function setSessionCard(?SessionCard $sc): self { $this->sessionCard = $sc; return $this; }

    public function getVoterCouple(): ?Couple { return $this->voterCouple; }
    public function setVoterCouple(?Couple $c): self { $this->voterCouple = $c; return $this; }

    public function getTargetCouple(): ?Couple { return $this->targetCouple; }
    public function setTargetCouple(?Couple $c): self { $this->targetCouple = $c; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
}
