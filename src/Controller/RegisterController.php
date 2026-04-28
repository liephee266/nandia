<?php

namespace App\Controller;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterController extends AbstractController
{
    #[Route('/api/v1/register', name: 'api_register', methods: ['POST'])]
    public function __invoke(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password']) || !isset($data['pseudo'])) {
            return $this->json(['error' => ' email, password et pseudo sont requis.'], 400);
        }

        // Check if user already exists
        $existing = $em->getRepository(Users::class)->findOneBy(['email' => $data['email']]);
        if ($existing) {
            return $this->json(['error' => 'Cet email est déjà utilisé.'], 409);
        }

        // Create new user
        $user = new Users();
        $user->setEmail($data['email']);
        $user->setPseudo($data['pseudo']);
        
        // Encode password
        $encoded = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($encoded);

        $em->persist($user);
        $em->flush();

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'pseudo' => $user->getPseudo(),
        ], 201);
    }
}
