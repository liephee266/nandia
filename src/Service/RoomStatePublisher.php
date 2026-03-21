<?php
// src/Service/RoomStatePublisher.php

namespace App\Service;

use App\Entity\Room;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

/**
 * Publishes real-time room state updates via Mercure SSE.
 *
 * Mercure delivers updates to all connected clients subscribed to
 * the "room/{id}" topic, keeping them in sync without polling.
 */
class RoomStatePublisher
{
    public function __construct(
        private readonly HubInterface $hub,
    ) {}

    /**
     * Publish a room state update to all subscribed clients.
     *
     * @param Room $room  The room whose state changed
     * @param string|null $eventType  Event type for the SSE payload
     */
    public function publishRoomUpdate(Room $room, ?string $eventType = 'room_update'): void
    {
        $topic = "room/{$room->getId()}";

        $data = json_encode([
            'event'   => $eventType,
            'roomId'  => $room->getId(),
            'status'  => $room->getStatus(),
            'phase'   => $room->getCardPhase(),
            'cardIdx' => $room->getCurrentCardIndex(),
        ], JSON_THROW_ON_ERROR);

        $this->hub->publish(new Update($topic, $data));
    }

    /**
     * Publish a generic room event (e.g., "participant_joined", "phase_changed").
     */
    public function publishRoomEvent(Room $room, string $eventType, array $extra = []): void
    {
        $topic = "room/{$room->getId()}";

        $data = json_encode(array_merge([
            'event'  => $eventType,
            'roomId' => $room->getId(),
        ], $extra), JSON_THROW_ON_ERROR);

        $this->hub->publish(new Update($topic, $data));
    }
}
