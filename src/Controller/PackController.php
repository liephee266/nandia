<?php

namespace App\Controller;

use App\Entity\Pack;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/packs', name: 'api_packs_')]
class PackController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    /**
     * GET /api/v1/packs
     * Liste tous les packs actifs disponibles.
     */
    #[Route('', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof Users) {
            return $this->json(['error' => 'Utilisateur introuvable.'], 401);
        }

        $packs = $this->em->getRepository(Pack::class)
            ->findBy(['isActive' => true], ['createdAt' => 'DESC']);

        $purchasedIds = [];
        foreach ($user->getPurchasedPacks() as $p) {
            $purchasedIds[] = $p->getId();
        }

        $data = array_map(function (Pack $pack) use ($purchasedIds) {
            return [
                'id' => $pack->getId(),
                'name' => $pack->getName(),
                'description' => $pack->getDescription(),
                'price' => $pack->getPrice(),
                'cardCount' => $pack->getCardCount(),
                'coverImage' => $pack->getCoverImage(),
                'iapProductId' => $pack->getIapProductId(),
                'isFree' => $pack->isFree(),
                'isPurchased' => in_array($pack->getId(), $purchasedIds),
            ];
        }, $packs);

        return $this->json($data);
    }

    /**
     * POST /api/v1/packs/{id}/purchase
     * Simule l'achat d'un pack (gratuit ou via vérification IAP).
     * En production : valider le reçu Apple/Google côté serveur.
     */
    #[Route('/{id}/purchase', name: 'purchase', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function purchase(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof Users) {
            return $this->json(['error' => 'Utilisateur introuvable.'], 401);
        }

        $pack = $this->em->getRepository(Pack::class)->find($id);
        if (!$pack || !$pack->isActive()) {
            return $this->json(['error' => 'Pack introuvable.'], 404);
        }

        // Vérifier si déjà acheté
        if ($user->getPurchasedPacks()->contains($pack)) {
            return $this->json(['error' => 'Pack déjà acheté.'], 409);
        }

        $data = json_decode($request->getContent(), true);

        // Si le pack n'est pas gratuit, on attend une preuve d'achat
        if (!$pack->isFree()) {
            $receipt = $data['receipt'] ?? null;
            $platform = $data['platform'] ?? null; // 'ios' | 'android'

            if (!$receipt || !$platform) {
                return $this->json([
                    'error' => 'Preuve d\'achat requise.',
                    'requiresIap' => true,
                    'iapProductId' => $pack->getIapProductId(),
                ], 400);
            }

            // TODO: Valider le reçu auprès d'Apple/Google
            // Pour l'instant, on accepte sans validation
        }

        // Ajouter le pack à l'utilisateur
        $user->getPurchasedPacks()->add($pack);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => sprintf('Pack "%s" activé !', $pack->getName()),
            'cardCount' => $pack->getCardCount(),
        ]);
    }

    /**
     * GET /api/v1/packs/my
     * Liste les packs achetés par l'utilisateur avec leurs cartes.
     */
    #[Route('/my', name: 'my', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myPacks(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof Users) {
            return $this->json(['error' => 'Utilisateur introuvable.'], 401);
        }

        $data = [];
        foreach ($user->getPurchasedPacks() as $pack) {
            $cards = [];
            foreach ($pack->getCards() as $card) {
                $cards[] = [
                    'id' => $card->getId(),
                    'questionText' => $card->getQuestionText(),
                    'theme' => $card->getTheme()?->getName(),
                    'difficultyLevel' => $card->getDifficultyLevel(),
                ];
            }

            $data[] = [
                'id' => $pack->getId(),
                'name' => $pack->getName(),
                'description' => $pack->getDescription(),
                'cardCount' => $pack->getCardCount(),
                'coverImage' => $pack->getCoverImage(),
                'cards' => $cards,
            ];
        }

        return $this->json($data);
    }
}
