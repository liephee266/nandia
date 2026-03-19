<?php
// src/Controller/PasswordResetController.php

namespace App\Controller;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class PasswordResetController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    /**
     * Réinitialise le mot de passe d'un utilisateur.
     * Body JSON : { "email": string, "newPassword": string }
     *
     * Note : dans un contexte de production, cette route enverrait d'abord
     * un email avec un token. Ici le changement est direct (MVP).
     */
    #[Route('/password-reset', name: 'password_reset', methods: ['POST'])]
    public function reset(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['newPassword'])) {
            return $this->json(['error' => 'Email et nouveau mot de passe requis.'], 400);
        }

        if (strlen($data['newPassword']) < 6) {
            return $this->json(['error' => 'Le mot de passe doit faire au moins 6 caractères.'], 400);
        }

        $user = $this->entityManager
            ->getRepository(Users::class)
            ->findOneBy(['email' => $data['email']]);

        // On retourne toujours 200 pour ne pas révéler si l'email existe
        if ($user === null) {
            return $this->json(['message' => 'Si ce compte existe, le mot de passe a été réinitialisé.'], 200);
        }

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $data['newPassword'])
        );

        $this->entityManager->flush();

        return $this->json(['message' => 'Mot de passe réinitialisé avec succès.'], 200);
    }
}
