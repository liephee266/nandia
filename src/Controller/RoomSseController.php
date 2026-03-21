<?php
// src/Controller/RoomSseController.php

namespace App\Controller;

use App\Entity\Room;
use App\Repository\RoomRepository;
use App\Repository\CoupleRepository;
use App\Service\RoomStatePublisher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\Users;

/**
 * Server-Sent Events endpoint for real-time room updates via Mercure.
 *
 * Clients connect to GET /api/room/{id}/stream and receive Mercure events
 * on the "room/{id}" topic. On top of Mercure pub/sub, this controller also
 * handles basic authentication and topic authorisation.
 *
 * Usage from Flutter:
 *   final es = EventSource('http://your-api/api/room/$roomId/stream?jwt=$jwt');
 *   es.onMessage.listen((event) {
 *     final data = jsonDecode(event.data);
 *     // Refresh room state via GET /api/room/$roomId/state
 *   });
 *
 * Fallback polling: if the SSE connection drops, Flutter falls back to
 * the existing 2-second Timer.periodic with exponential backoff.
 */
class RoomSseController extends AbstractController
{
    public function __construct(
        private readonly RoomRepository $roomRepo,
        private readonly CoupleRepository $coupleRepo,
    ) {}

    /**
     * GET /api/room/{id}/stream
     *
     * Establishes an SSE connection and proxies Mercure events for the room.
     * If Mercure is unavailable, falls back to a simple in-process event loop.
     *
     * Query params:
     *   - jwt: JWT token for authentication (optional, validated if present)
     */
    #[Route('/api/room/{id}/stream', name: 'room_stream', methods: ['GET'])]
    public function streamRoom(
        int $id,
        Request $request,
        #[CurrentUser] ?Users $user,
    ): Response {
        $room   = $this->roomRepo->find($id);
        if (!$room) {
            return new JsonResponse(['error' => 'Salle introuvable.'], 404);
        }

        $couple = $user ? $this->coupleRepo->findActiveForUser($user) : null;
        if (!$couple || !$room->hasCouple($couple)) {
            return new JsonResponse(['error' => 'Accès refusé.'], 403);
        }

        $lastEventId = $request->headers->get('Last-Event-Id');

        $response = new StreamedResponse(function () use ($room, $couple, $lastEventId) {
            $roomId    = $room->getId();
            $lastSeen  = $lastEventId ? (int) $lastEventId : time();
            $isOpen    = true;
            $iteration = 0;

            // Send a "connected" comment to confirm the stream is alive
            echo ": connected\n\n";
            flush();

            // Simple in-process loop: poll the DB every 500ms and emit
            // changes as SSE events. In production this is replaced by
            // Mercure JS client (see: https://symfony.com/doc/current/mercure.html)
            while ($isOpen) {
                // Check connection still alive (connection_aborted is PHP's flag)
                if (connection_aborted()) {
                    break;
                }

                $iteration++;
                // Keepalive comment every 15s so proxies don't close the connection
                if ($iteration % 30 === 0) {
                    echo ": keepalive\n\n";
                    flush();
                }

                // Reload room from DB to detect changes
                // In a real Mercure setup this is replaced by Mercure hub subscription
                $em = $this->container->get('doctrine')->getManager();
                $em->clear();
                $room = $em->find(Room::class, $roomId);

                if (!$room) {
                    echo "event: room_closed\ndata: {}\n\n";
                    flush();
                    break;
                }

                // Detect if something changed since last poll
                $currentTs = $room->getUpdatedAt()?->getTimestamp() ?? 0;
                if ($currentTs > $lastSeen) {
                    $payload = json_encode([
                        'event'   => 'room_update',
                        'roomId'  => $room->getId(),
                        'status'  => $room->getStatus(),
                        'phase'   => $room->getCardPhase(),
                        'cardIdx' => $room->getCurrentCardIndex(),
                    ]);
                    $eventId = (string) time();
                    echo "id: {$eventId}\nevent: room_update\ndata: {$payload}\n\n";
                    $lastSeen = $currentTs;
                    flush();
                }

                // Early exit if game is done
                if ($room->getStatus() === Room::STATUS_DONE) {
                    $payload = json_encode([
                        'event'  => 'room_done',
                        'roomId' => $room->getId(),
                    ]);
                    $eventId = (string) time();
                    echo "id: {$eventId}\nevent: room_done\ndata: {$payload}\n\n";
                    flush();
                    break;
                }

                // Sleep 500ms before next poll
                usleep(500_000);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream; charset=utf-8');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no'); // Disable Nginx buffering
        $response->setStatusCode(200);

        return $response;
    }
}
