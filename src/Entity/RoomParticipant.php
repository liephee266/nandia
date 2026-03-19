<?php
// src/Entity/RoomParticipant.php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\RoomParticipantRepository;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Participation d'un couple à une salle de jeu.
 * Le score est incrémenté à chaque vote reçu (+2 pts/vote).
 */
#[ORM\Entity(repositoryClass: RoomParticipantRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['room_participant:read']]
)]
class RoomParticipant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['room_participant:read', 'room:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['room_participant:read'])]
    private ?Room $room = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['room_participant:read', 'room:read'])]
    private ?Couple $couple = null;

    /** Score cumulatif (votes reçus × 2) */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    #[Groups(['room_participant:read', 'room:read'])]
    private int $score = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['room_participant:read'])]
    private ?\DateTimeImmutable $joinedAt = null;

    /** true si le couple a soumis sa réponse pour la carte en cours */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['room_participant:read', 'room:read'])]
    private bool $hasAnsweredCurrentCard = false;

    /** Réponse soumise pour la carte en cours (remise à null à chaque nouvelle carte) */
    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['room_participant:read', 'room:read'])]
    private ?string $currentAnswer = null;

    public function __construct()
    {
        $this->joinedAt = new \DateTimeImmutable();
    }

    public function addScore(int $points): void
    {
        $this->score += $points;
    }

    public function resetCardStatus(): void
    {
        $this->hasAnsweredCurrentCard = false;
        $this->currentAnswer          = null;
    }

    // ── Getters / Setters ───────────────────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getRoom(): ?Room { return $this->room; }
    public function setRoom(?Room $r): self { $this->room = $r; return $this; }

    public function getCouple(): ?Couple { return $this->couple; }
    public function setCouple(?Couple $c): self { $this->couple = $c; return $this; }

    public function getScore(): int { return $this->score; }
    public function setScore(int $s): self { $this->score = $s; return $this; }

    public function getJoinedAt(): ?\DateTimeImmutable { return $this->joinedAt; }

    public function isHasAnsweredCurrentCard(): bool { return $this->hasAnsweredCurrentCard; }
    public function setHasAnsweredCurrentCard(bool $b): self { $this->hasAnsweredCurrentCard = $b; return $this; }

    public function getCurrentAnswer(): ?string { return $this->currentAnswer; }
    public function setCurrentAnswer(?string $a): self { $this->currentAnswer = $a; return $this; }
}
