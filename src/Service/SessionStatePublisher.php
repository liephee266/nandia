<?php
// src/Service/SessionStatePublisher.php

namespace App\Service;

use App\Entity\Session;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

/**
 * Publishes real-time session updates via Mercure for couple live sessions.
 */
class SessionStatePublisher
{
    public function __construct(
        private readonly HubInterface $hub,
    ) {}

    /**
     * Publish a session state update to all subscribed clients.
     */
    public function publishSessionUpdate(Session $session, ?string $eventType = 'session_update'): void
    {
        $topic = "session/{$session->getId()}";

        $data = json_encode([
            'event'      => $eventType,
            'sessionId'  => $session->getId(),
            'status'     => $session->getEndedAt() ? 'done' : 'active',
        ], JSON_THROW_ON_ERROR);

        $this->hub->publish(new Update($topic, $data));
    }

    /**
     * Publish a specific session event with extra payload data.
     */
    public function publishSessionEvent(Session $session, string $eventType, array $extra = []): void
    {
        $topic = "session/{$session->getId()}";

        $data = json_encode(array_merge([
            'event'     => $eventType,
            'sessionId' => $session->getId(),
        ], $extra), JSON_THROW_ON_ERROR);

        $this->hub->publish(new Update($topic, $data));
    }
}
