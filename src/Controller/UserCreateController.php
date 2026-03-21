<?php
// src/Controller/UserCreateController.php

namespace App\Controller;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_')]
class UserCreateController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('/users', name: 'create_user', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email'])) {
            return $this->json(['error' => 'L\'email est obligatoire.'], 400);
        }

        if (empty($data['plainPassword'])) {
            return $this->json(['error' => 'Le mot de passe est obligatoire.'], 400);
        }

        // Vérification unicité email avant toute tentative d'insertion
        $existing = $this->entityManager
            ->getRepository(Users::class)
            ->findOneBy(['email' => $data['email']]);

        if ($existing !== null) {
            return $this->json(['error' => 'Un compte avec cet email existe déjà.'], 409);
        }

        $user = new Users();
        $user->setEmail($data['email']);
        $user->setPseudo($data['pseudo'] ?? null);

        // Hacher le mot de passe
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $data['plainPassword'])
        );

        // Validation des contraintes Symfony (@Assert)
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            return $this->json(['error' => implode(', ', $messages)], 400);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'pseudo' => $user->getPseudo(),
        ], 201);
    }
}