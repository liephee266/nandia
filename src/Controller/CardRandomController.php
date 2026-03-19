<?php

namespace App\Controller;

use App\Repository\CardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CardRandomController extends AbstractController
{
    #[Route('/api/cards/random', name: 'api_cards_random', methods: ['GET'])]
    public function __invoke(Request $request, CardRepository $cardRepository, SerializerInterface $serializer): JsonResponse
    {
        $themeId    = $request->query->get('themeId') ? (int) $request->query->get('themeId') : null;
        $difficulty = $request->query->get('difficulty') ? (int) $request->query->get('difficulty') : null;

        $card = $cardRepository->findRandomCard($themeId, null, $difficulty);

        if ($card === null) {
            return $this->json(['error' => 'Aucune carte disponible'], 404);
        }

        $data = $serializer->serialize($card, 'json', ['groups' => ['card:read']]);

        return new JsonResponse($data, 200, [], true);
    }
}
