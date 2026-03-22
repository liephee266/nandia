<?php
// src/Controller/FavoriteCardController.php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\FavoriteCard;
use App\Entity\Users;
use App\Repository\FavoriteCardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Gestion des cartes favorites de l'utilisateur connecté.
 *
 * Endpoints :
 *  GET    /api/favorites          → Lister mes favoris
 *  POST   /api/favorites          → Ajouter une carte en favori
 *  DELETE /api/favorites/{cardId} → Retirer une carte des favoris
 */
#[Route('/api/favorites')]
class FavoriteCardController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface  $em,
        private readonly FavoriteCardRepository  $favoriteRepo,
    ) {}

    // ── GET /api/favorites ───────────────────────────────────────────────────

    #[Route('', methods: ['GET'])]
    public function list(#[CurrentUser] Users $user): JsonResponse
    {
        $favorites = $this->favoriteRepo->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        $data = array_map(function (FavoriteCard $fav): array {
            $card = $fav->getCard();
            return [
                'id'            => $fav->getId(),
                'cardId'        => $card?->getId(),
                'questionText'  => $card?->getQuestionText(),
                'type'          => $card?->getType() ?? 'question',
                'difficultyLevel' => $card?->getDifficultyLevel(),
                'themeName'     => $card?->getTheme()?->getName(),
                'themeColor'    => $card?->getTheme()?->getColorCode(),
                'savedAt'       => $fav->getCreatedAt()->format(\DateTimeInterface::ATOM),
            ];
        }, $favorites);

        return $this->json($data);
    }

    // ── POST /api/favorites ──────────────────────────────────────────────────

    #[Route('', methods: ['POST'])]
    public function add(
        Request            $request,
        #[CurrentUser] Users $user,
    ): JsonResponse {
        $data   = json_decode($request->getContent(), true) ?? [];
        $cardId = (int) ($data['cardId'] ?? 0);

        if (!$cardId) {
            return $this->json(['error' => 'cardId requis.'], 400);
        }

        $card = $this->em->find(Card::class, $cardId);
        if (!$card) {
            return $this->json(['error' => 'Carte introuvable.'], 404);
        }

        $existing = $this->favoriteRepo->findOneBy(['user' => $user, 'card' => $card]);
        if ($existing) {
            return $this->json(['id' => $existing->getId(), 'cardId' => $cardId], 200);
        }

        $fav = new FavoriteCard();
        $fav->setUser($user);
        $fav->setCard($card);

        $this->em->persist($fav);
        $this->em->flush();

        return $this->json(['id' => $fav->getId(), 'cardId' => $cardId], 201);
    }

    // ── DELETE /api/favorites/{cardId} ───────────────────────────────────────

    #[Route('/{cardId}', methods: ['DELETE'])]
    public function remove(
        int                $cardId,
        #[CurrentUser] Users $user,
    ): JsonResponse {
        $card = $this->em->find(Card::class, $cardId);
        if (!$card) {
            return $this->json(['error' => 'Carte introuvable.'], 404);
        }

        $fav = $this->favoriteRepo->findOneBy(['user' => $user, 'card' => $card]);
        if (!$fav) {
            return $this->json(null, 204); // Idempotent : déjà retiré
        }

        $this->em->remove($fav);
        $this->em->flush();

        return $this->json(null, 204);
    }
}
