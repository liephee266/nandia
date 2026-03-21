<?php

namespace App\Message;

/**
 * Message dispatched quand le timer d'une SessionCard expire.
 * Traité de manière asynchrone par RoomCardTimerExpiredHandler.
 */
class RoomCardTimerExpired
{
    public function __construct(
        public readonly int $roomId,
        public readonly int $sessionCardId,
    ) {}
}
