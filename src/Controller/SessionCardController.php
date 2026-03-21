<?php

namespace App\Controller;

use App\Entity\SessionCard;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SessionCardController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    #[Route('/api/session-cards/{id}/favorite', name: 'api_session_card_favorite', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function toggleFavorite(int $id): JsonResponse
    {
        $sc = $this->em->getRepository(SessionCard::class)->find($id);

        if (!$sc) {
            return $this->json(['error' => 'SessionCard non trouvée.'], 404);
        }

        // Sécurité : seul le user lié à cette session peut toggle
        $session = $sc->getSession();
        if (!$session || $session->getUser()?->getId() !== $this->getUser()->getId()) {
            return $this->json(['error' => 'Accès refusé.'], 403);
        }

        $sc->toggleFavorite();
        $this->em->flush();

        return $this->json([
            'id'        => $sc->getId(),
            'favorited' => $sc->isFavorited(),
        ]);
    }
}
