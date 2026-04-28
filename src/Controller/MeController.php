<?php

namespace App\Controller;

use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class MeController extends AbstractController
{
    #[Route('/api/v1/me', name: 'api_me', methods: ['GET'])]
    public function __invoke(
        #[CurrentUser] ?Users $user,
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Non authentifié.'], 401);
        }

        return $this->json([
            'id'                 => $user->getId(),
            'email'              => $user->getEmail(),
            'pseudo'             => $user->getPseudo(),
            'prenom'             => $user->getPrenom(),
            'nom'                => $user->getNom(),
            'dateNaissance'      => $user->getDateNaissance()?->format('Y-m-d\TH:i:sP'),
            'telephone'          => $user->getTelephone(),
            'sexe'               => $user->getSexe(),
            'situationAmoureuse' => $user->getSituationAmoureuse(),
            'biographie'         => $user->getBiographie(),
            'profileImage'       => $user->getProfileImage(),
            'purchasedPacks'     => [],
        ]);
    }
}
