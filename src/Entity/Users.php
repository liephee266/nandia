<?php
// src/Entity/User.php

namespace App\Entity;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UsersRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_USER') and (object == user or is_granted('ROLE_ADMIN'))"),
        new GetCollection(security: "is_granted('ROLE_ADMIN')"),
        // Un utilisateur ne peut modifier que son propre compte
        new Patch(security: "is_granted('ROLE_USER') and object == user"),
        // Un utilisateur peut supprimer uniquement son propre compte
        new Delete(security: "is_granted('ROLE_USER') and object == user"),
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']]
)]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups(['user:read', 'user:write'])]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    // Champ non-mappé (pas de colonne BDD) — utilisé uniquement pendant la requête
    #[Groups(['user:write'])]
    #[Assert\Length(
        min: 8,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/\d/',
        message: 'Le mot de passe doit contenir au moins un chiffre.'
    )]
    private ?string $plainPassword = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $pseudo = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $prenom = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $nom = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?\DateTimeInterface $dateNaissance = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $telephone = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $sexe = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $situationAmoureuse = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $biographie = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $profileImage = null;

    // Stocke les rôles additionnels (ex: ['ROLE_ADMIN']). ROLE_USER est toujours ajouté dynamiquement.
    #[ORM\Column(type: 'json', options: ['default' => '[]'])]
    private array $roles = [];

    /** Token de rafraîchissement JWT (opaque, 64 hex chars). */
    #[ORM\Column(length: 128, nullable: true, unique: true)]
    private ?string $refreshToken = null;

    /**
     * Date d'expiration du refresh token (30 jours par défaut).
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $refreshTokenExpiresAt = null;

    /**
     * Date à laquelle le refresh token a été révoqué (logout).
     * Si ce champ est non null, le refresh token est considéré comme invalide
     * même si sa date d'expiration n'est pas atteinte.
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $refreshTokenRevokedAt = null;

    /**
     * Date d'émission du refresh token courant.
     * Permet de détecter si un token a été révoqué APRÈS son émission
     * (ce qui signifie qu'il doit être considéré comme invalide).
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $refreshTokenIssuedAt = null;

    /**
     * Token FCM (Firebase Cloud Messaging) pour les push notifications.
     * Mis à jour au login ou à chaque démarrage de l'app.
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deviceToken = null;

    /**
     * Token opaque de réinitialisation de mot de passe (64 hex chars).
     * Valable 1 heure, effacé après usage.
     */
    #[ORM\Column(length: 128, nullable: true, unique: true)]
    private ?string $resetToken = null;

    /** Expiration du reset token. */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $resetTokenExpiresAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Session::class)]
    private Collection $sessions;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Response::class)]
    private Collection $responses;



    public function __construct()
    {
        $this->sessions = new ArrayCollection();
        $this->responses = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    // ── Refresh Token ─────────────────────────────────────────────────────────

    public function getRefreshToken(): ?string { return $this->refreshToken; }

    public function getRefreshTokenExpiresAt(): ?\DateTimeImmutable { return $this->refreshTokenExpiresAt; }

    public function getRefreshTokenRevokedAt(): ?\DateTimeImmutable { return $this->refreshTokenRevokedAt; }

    public function getRefreshTokenIssuedAt(): ?\DateTimeImmutable { return $this->refreshTokenIssuedAt; }

    /**
     * Génère un nouveau refresh token opaque (64 hex chars) valable 30 jours.
     * Réinitialise la date de révocation.
     * Retourne le token en clair pour l'inclure dans la réponse de login.
     */
    public function generateRefreshToken(): string
    {
        $token = bin2hex(random_bytes(32)); // 64 chars hex
        $this->refreshToken         = $token;
        $this->refreshTokenExpiresAt = new \DateTimeImmutable('+30 days');
        $this->refreshTokenIssuedAt  = new \DateTimeImmutable();
        $this->refreshTokenRevokedAt = null; // réinitialise après un refresh réussi
        return $token;
    }

    /**
     * Vérifie si le refresh token est actuellement utilisable.
     * Retourne false si :
     *   - le token est null ou expiré
     *   - le token a été révoqué APRÈS son émission (logout)
     */
    public function isRefreshTokenValid(): bool
    {
        if ($this->refreshToken === null || $this->refreshTokenExpiresAt === null) {
            return false;
        }

        // Token expiré
        if ($this->refreshTokenExpiresAt < new \DateTimeImmutable()) {
            return false;
        }

        // Token révoqué après son émission (logout explicite)
        if ($this->refreshTokenRevokedAt !== null
            && $this->refreshTokenIssuedAt !== null
            && $this->refreshTokenRevokedAt > $this->refreshTokenIssuedAt) {
            return false;
        }

        return true;
    }

    /**
     * Révoque le refresh token courant (appelé lors du logout).
     * Le token reste en BDD jusqu'à expiration mais devient inutilisable.
     */
    public function revokeRefreshToken(): void
    {
        $this->refreshTokenRevokedAt = new \DateTimeImmutable();
    }

    // ── Device Token (FCM) ────────────────────────────────────────────────────

    public function getDeviceToken(): ?string { return $this->deviceToken; }
    public function setDeviceToken(?string $deviceToken): self
    {
        $this->deviceToken = $deviceToken;
        return $this;
    }

    // ── Reset Token ───────────────────────────────────────────────────────────

    public function getResetToken(): ?string { return $this->resetToken; }
    public function getResetTokenExpiresAt(): ?\DateTimeImmutable { return $this->resetTokenExpiresAt; }

    /**
     * Génère un token opaque valable 1 heure et le retourne en clair.
     */
    public function generateResetToken(): string
    {
        $token = bin2hex(random_bytes(32)); // 64 chars hex
        $this->resetToken          = $token;
        $this->resetTokenExpiresAt = new \DateTimeImmutable('+1 hour');
        return $token;
    }

    /** Invalide le token après usage. */
    public function clearResetToken(): void
    {
        $this->resetToken          = null;
        $this->resetTokenExpiresAt = null;
    }

    public function isResetTokenValid(): bool
    {
        return $this->resetToken !== null
            && $this->resetTokenExpiresAt !== null
            && $this->resetTokenExpiresAt > new \DateTimeImmutable();
    }

    // Getters et Setters...
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    #[Groups(['user:write'])]
    public function setPlainPassword(string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(?string $pseudo): self
    {
        $this->pseudo = $pseudo;
        return $this;
    }

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(?string $prenom): self { $this->prenom = $prenom; return $this; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(?string $nom): self { $this->nom = $nom; return $this; }

    public function getDateNaissance(): ?\DateTimeInterface { return $this->dateNaissance; }
    public function setDateNaissance(?\DateTimeInterface $dateNaissance): self { $this->dateNaissance = $dateNaissance; return $this; }

    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $telephone): self { $this->telephone = $telephone; return $this; }

    public function getSexe(): ?string { return $this->sexe; }
    public function setSexe(?string $sexe): self { $this->sexe = $sexe; return $this; }

    public function getSituationAmoureuse(): ?string { return $this->situationAmoureuse; }
    public function setSituationAmoureuse(?string $situationAmoureuse): self { $this->situationAmoureuse = $situationAmoureuse; return $this; }

    public function getBiographie(): ?string { return $this->biographie; }
    public function setBiographie(?string $biographie): self { $this->biographie = $biographie; return $this; }

    public function getProfileImage(): ?string { return $this->profileImage; }
    public function setProfileImage(?string $profileImage): self { $this->profileImage = $profileImage; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function getResponses(): Collection
    {
        return $this->responses;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        // ROLE_USER est toujours garanti ; les rôles additionnels (ex: ROLE_ADMIN) viennent de la BDD
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getSalt(): ?string
    {
        return null;
    }
}