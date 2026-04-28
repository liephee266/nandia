<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\Session;
use App\Entity\Users;
use App\Repository\CardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Endpoint pour la "Carte du jour" — une carte quotidienne recommandée.
 *
 * GET /api/v1/daily-card
 *   Retourne la carte du jour (rotation basée sur le jour de l'année).
 *   La même carte pour tous les utilisateurs ce jour-là.
 *
 * POST /api/v1/daily-card/respond
 *   { "response": "..." }
 *   Répond à la carte du jour et crée une session éphémère si besoin.
 */
#[Route('/api/v1/daily-card', name: 'api_daily_card_')]
#[IsGranted('ROLE_USER')]
class DailyCardController extends AbstractController
{
    public function __construct(
        private readonly CardRepository $cardRepo,
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('', name: 'get', methods: ['GET'])]
    public function getDailyCard(): JsonResponse
    {
        /** @var Users $user */
        $user = $this->getUser();

        // Carte du jour : rotation basée sur le jour de l'année + seed utilisateur
        // pour varier légèrement entre utilisateurs
        $dayOfYear = (int) date('z');
        $seed = ($dayOfYear * 7 + ($user->getId() ?? 0) * 13) % 325;

        $allCards = $this->cardRepo->findAll();
        if (empty($allCards)) {
            return new JsonResponse(['error' => 'Aucune carte disponible.'], 404);
        }

        $card = $allCards[$seed % count($allCards)];

        // Vérifier si l'utilisateur a déjà répondu aujourd'hui
        $today = new \DateTimeImmutable('today');
        $tomorrow = $today->modify('+1 day');

        $existingResponse = $this->em->getConnection()->fetchAssociative(
            'SELECT r.content, sc.id as session_card_id
             FROM response r
             JOIN session_card sc ON r.session_card_id = sc.id
             JOIN session s ON sc.session_id = s.id
             WHERE s.user_id = :userId
               AND sc.card_id = :cardId
               AND sc.drawn_at >= :today
               AND sc.drawn_at < :tomorrow',
            [
                'userId' => $user->getId(),
                'cardId' => $card->getId(),
                'today' => $today->format('Y-m-d H:i:s'),
                'tomorrow' => $tomorrow->format('Y-m-d H:i:s'),
            ]
        );

        return new JsonResponse([
            'card' => [
                'id' => $card->getId(),
                'questionText' => $card->getQuestionText(),
                'theme' => $card->getTheme()?->getName(),
                'difficultyLevel' => $card->getDifficultyLevel(),
                'isBonus' => $card->isIsBonus(),
            ],
            'dayOfYear' => $dayOfYear + 1,
            'alreadyResponded' => $existingResponse !== false,
            'myResponse' => $existingResponse['content'] ?? null,
        ]);
    }

    #[Route('/respond', name: 'respond', methods: ['POST'])]
    public function respondToDailyCard(Request $request): JsonResponse
    {
        /** @var Users $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (empty($data['response']) || empty($data['cardId'])) {
            return new JsonResponse(['error' => 'Réponse et cardId requis.'], 400);
        }

        $card = $this->cardRepo->find($data['cardId']);
        if (!$card instanceof Card) {
            return new JsonResponse(['error' => 'Carte introuvable.'], 404);
        }

        // Créer une session éphémère "daily" si elle n'existe pas
        $today = new \DateTimeImmutable('today');
        $session = $this->em->getRepository(Session::class)->findOneBy([
            'user' => $user,
            'mode' => 'daily',
        ]);

        if (!$session) {
            $session = new Session();
            $session->setUser($user);
            $session->setMode('daily');
            $session->setCardCount(1);
            $this->em->persist($session);
        }

        // Créer la session card
        $sessionCard = new \App\Entity\SessionCard();
        $sessionCard->setSession($session);
        $sessionCard->setCard($card);
        $sessionCard->setOrderIndex(0);
        $this->em->persist($sessionCard);
        $this->em->flush();

        // Créer la réponse
        $response = new \App\Entity\Response();
        $response->setSessionCard($sessionCard);
        $response->setUser($user);
        $response->setAnswerText($data['response']);
        $this->em->persist($response);
        $this->em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Réponse enregistrée pour la carte du jour !',
        ]);
    }
}
