<?php
// src/Entity/Response.php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\ResponseRepository;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ResponseRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(security: "object.user == user"),
        new Post()
    ],
    normalizationContext: ['groups' => ['response:read']],
    denormalizationContext: ['groups' => ['response:write']]
)]
class Response
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['response:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'responses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['response:read', 'response:write'])]
    private ?Users $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['response:read', 'response:write'])]
    private ?SessionCard $sessionCard = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['response:read', 'response:write'])]
    private ?string $answerText = null;

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

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getSessionCard(): ?SessionCard
    {
        return $this->sessionCard;
    }

    public function setSessionCard(?SessionCard $sessionCard): self
    {
        $this->sessionCard = $sessionCard;
        return $this;
    }

    public function getAnswerText(): ?string
    {
        return $this->answerText;
    }

    public function setAnswerText(?string $answerText): self
    {
        $this->answerText = $answerText;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}