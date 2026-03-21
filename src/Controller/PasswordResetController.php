<?php
// src/Controller/PasswordResetController.php

namespace App\Controller;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Réinitialisation de mot de passe en deux étapes :
 *
 *  1. POST /api/password-reset/request  — génère un token et l'envoie par email
 *  2. POST /api/password-reset/confirm  — valide le token et change le mot de passe
 */
#[Route('/api/password-reset', name: 'api_password_reset_')]
class PasswordResetController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface      $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly MailerInterface             $mailer,
        private readonly LoggerInterface             $logger,
        private readonly string                      $mailerFromEmail,
        private readonly string                      $mailerFromName,
    ) {}

    // ── Étape 1 : demander un token ───────────────────────────────────────────

    /**
     * Body JSON : { "email": string }
     *
     * Génère un token sécurisé valable 1 heure et l'envoie par email.
     * La réponse est volontairement générique pour ne pas révéler
     * si l'adresse email existe en base (prévention de l'énumération).
     */
    #[Route('/request', name: 'request', methods: ['POST'])]
    public function request(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        if (empty($data['email'])) {
            return $this->json(['error' => 'Email requis.'], 400);
        }

        /** @var Users|null $user */
        $user = $this->em->getRepository(Users::class)
            ->findOneBy(['email' => $data['email']]);

        // Réponse identique qu'il y ait un compte ou non (anti-énumération)
        $genericResponse = $this->json([
            'message' => 'Si un compte correspond à cet email, un code de réinitialisation vient d\'être envoyé.',
        ]);

        if ($user === null) {
            return $genericResponse;
        }

        $token = $user->generateResetToken();
        $this->em->flush();

        // Envoi de l'email
        try {
            $email = (new TemplatedEmail())
                ->from(new Address($this->mailerFromEmail, $this->mailerFromName))
                ->to(new Address($user->getEmail()))
                ->subject('Réinitialisation de votre mot de passe Nandia')
                ->htmlTemplate('emails/password_reset.html.twig')
                ->context([
                    'token'      => $token,
                    'expiresAt'  => $user->getResetTokenExpiresAt()?->format('H:i'),
                    'prenom'     => $user->getPrenom() ?? $user->getPseudo() ?? 'Utilisateur',
                ]);

            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Password reset email failed', [
                'email' => $data['email'],
                'error' => $e->getMessage(),
            ]);
            return $this->json(['error' => 'Impossible d\'envoyer l\'email. Réessayez dans quelques minutes.'], 503);
        }

        return $genericResponse;
    }

    // ── Étape 2 : confirmer avec le token ─────────────────────────────────────

    /**
     * Body JSON : { "email": string, "token": string, "newPassword": string }
     */
    #[Route('/confirm', name: 'confirm', methods: ['POST'])]
    public function confirm(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        if (empty($data['email']) || empty($data['token']) || empty($data['newPassword'])) {
            return $this->json(['error' => 'email, token et newPassword sont requis.'], 400);
        }

        // Validation du mot de passe (≥ 8 chars + au moins 1 chiffre)
        $pwd = $data['newPassword'];
        if (strlen($pwd) < 8) {
            return $this->json(['error' => 'Le mot de passe doit contenir au moins 8 caractères.'], 400);
        }
        if (!preg_match('/\d/', $pwd)) {
            return $this->json(['error' => 'Le mot de passe doit contenir au moins un chiffre.'], 400);
        }

        /** @var Users|null $user */
        $user = $this->em->getRepository(Users::class)
            ->findOneBy(['email' => $data['email']]);

        // Réponse générique pour ne pas révéler si l'email existe
        if ($user === null) {
            return $this->json(['error' => 'Token invalide ou expiré.'], 400);
        }

        // Vérifier que le token fourni correspond et n'est pas expiré
        // Timing-safe comparison (prevents timing attacks)
        if ($user->getResetToken() === null || !hash_equals($user->getResetToken(), $data['token'])) {
            return $this->json(['error' => 'Token invalide ou expiré.'], 400);
        }
        if (!$user->isResetTokenValid()) {
            return $this->json(['error' => 'Token invalide ou expiré.'], 400);
        }

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $pwd)
        );
        $user->clearResetToken();

        $this->em->flush();

        return $this->json(['message' => 'Mot de passe réinitialisé avec succès.']);
    }
}
