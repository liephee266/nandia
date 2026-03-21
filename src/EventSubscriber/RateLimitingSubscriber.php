<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 * Applique le rate limiting sur les endpoints publics.
 *
 * Routes limitées :
 *  - POST /api/v1/connexion       →  5 tentatives / 15 min par IP
 *  - POST /api/users              →  3 inscriptions / heure par IP
 *  - POST /api/token/refresh      → 10 refresh / min par IP
 */
class RateLimitingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RateLimiterFactory $loginLimiter,
        private readonly RateLimiterFactory $registerLimiter,
        private readonly RateLimiterFactory $tokenRefreshLimiter,
        private readonly RateLimiterFactory $passwordResetLimiter,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            // Doit tourner APRÈS le firewall mais AVANT le contrôleur
            // PRIORITY = -10 est approprié
            KernelEvents::REQUEST => ['onKernelRequest', -10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path    = $request->getPathInfo();
        $method  = $request->getMethod();

        $limiter = match (true) {
            $path === '/api/v1/connexion'    && $method === 'POST' => $this->loginLimiter,
            $path === '/api/users'           && $method === 'POST' => $this->registerLimiter,
            $path === '/api/token/refresh'  && $method === 'POST' => $this->tokenRefreshLimiter,
            str_starts_with($path, '/api/password-reset') && $method === 'POST' => $this->passwordResetLimiter,
            default                                               => null,
        };

        if ($limiter === null) {
            return;
        }

        // Identification par IP pour login et register
        $identifier = $request->getClientIp() ?? 'unknown';

        $limit = $limiter->create($identifier)->consume(1);

        if (!$limit->isAccepted()) {
            $retryAfter = $limit->getRetryAfter()->getTimestamp() - time();

            $event->setResponse(new JsonResponse(
                ['error' => 'Trop de tentatives. Réessayez dans quelques minutes.'],
                429,
                [
                    'Retry-After'       => (string) $retryAfter,
                    'X-RateLimit-Remaining' => (string) $limit->getRemainingTokens(),
                    'X-RateLimit-Limit'     => (string) $limit->getLimit(),
                ]
            ));
        }
    }
}
