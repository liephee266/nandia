<?php
// src/Controller/CoupleSessionSseController.php

namespace App\Controller;

use App\Entity\Session;
use App\Repository\SessionRepository;
use App\Repository\CoupleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\Users;

/**
 * Server-Sent Events endpoint for real-time couple session updates.
 *
 * GET /api/couple-session/{id}/stream
 *
 * Clients subscribe to session events and receive immediate notifications
 * when the partner submits a response, when a new card is drawn, or when
 * the session ends. Falls back to polling if SSE is unavailable.
 */
class CoupleSessionSseController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SessionRepository      $sessionRepo,
        private readonly CoupleRepository       $coupleRepo,
    ) {}

    #[Route('/api/couple-session/{id}/stream', name: 'couple_session_stream', methods: ['GET'])]
    public function streamSession(
        int     $id,
        Request $request,
        #[CurrentUser] ?Users $user,
    ): Response {
        $session = $this->sessionRepo->find($id);
        if (!$session) {
            return new JsonResponse(['error' => 'Session introuvable.'], 404);
        }

        $couple = $user ? $this->coupleRepo->findActiveForUser($user) : null;
        if (!$couple || $session->getCouple()?->getId() !== $couple->getId()) {
            return new JsonResponse(['error' => 'Accès refusé.'], 403);
        }

        $lastEventId = $request->headers->get('Last-Event-Id');

        $response = new StreamedResponse(function () use ($session, $couple, $lastEventId) {
            $sessionId = $session->getId();
            $lastSeen  = $lastEventId ? (int) $lastEventId : time();
            $iteration = 0;

            echo ": connected\n\n";
            flush();

            while (true) {
                if (connection_aborted()) {
                    break;
                }

                $iteration++;
                if ($iteration % 30 === 0) {
                    echo ": keepalive\n\n";
                    flush();
                }

                // Reload session from DB to detect changes
                $em = $this->container->get('doctrine')->getManager();
                $em->clear();
                $session = $em->find(Session::class, $sessionId);

                if (!$session || $session->getEndedAt() !== null) {
                    $payload = json_encode(['event' => 'session_done', 'sessionId' => $sessionId]);
                    echo "id: " . time() . "\nevent: session_done\ndata: {$payload}\n\n";
                    flush();
                    break;
                }

                $currentTs = $session->getUpdatedAt()?->getTimestamp() ?? 0;
                if ($currentTs > $lastSeen) {
                    $payload = json_encode([
                        'event'      => 'session_update',
                        'sessionId' => $sessionId,
                        'status'    => $session->getEndedAt() ? 'done' : 'active',
                    ]);
                    $eventId = (string) time();
                    echo "id: {$eventId}\nevent: session_update\ndata: {$payload}\n\n";
                    $lastSeen = $currentTs;
                    flush();
                }

                usleep(500_000);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->setStatusCode(200);

        return $response;
    }
}
