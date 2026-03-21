<?php
// src/Service/PushNotificationService.php

namespace App\Service;

use App\Entity\Users;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Envoie des push notifications via l'API REST OneSignal v1.
 *
 * Configuration requise dans .env :
 *   ONESIGNAL_APP_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
 *   ONESIGNAL_REST_API_KEY=votre-rest-api-key
 *
 * OneSignal identifie les utilisateurs par leur "External User ID"
 * (= l'ID numérique de l'utilisateur côté Nandia).
 * Le SDK Flutter appelle OneSignal.login(userId) après la connexion,
 * ce qui associe automatiquement l'appareil à cet ID — aucun token
 * device n'est stocké dans la base Nandia.
 *
 * Docs : https://documentation.onesignal.com/reference/create-notification
 */
class PushNotificationService
{
    private const ONESIGNAL_URL = 'https://onesignal.com/api/v1/notifications';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface     $logger,
        private readonly string              $oneSignalAppId,
        private readonly string              $oneSignalRestApiKey,
    ) {}

    /**
     * Envoie une notification à un seul utilisateur via son External User ID.
     *
     * @param Users  $recipient  Destinataire
     * @param string $title      Titre de la notification
     * @param string $body       Corps du message
     * @param array  $data       Données supplémentaires (route, id, etc.)
     */
    public function sendToUser(
        Users  $recipient,
        string $title,
        string $body,
        array  $data = [],
    ): void {
        $this->dispatch([(string) $recipient->getId()], $title, $body, $data);
    }

    /**
     * Envoie une notification à plusieurs utilisateurs.
     *
     * @param Users[] $recipients
     */
    public function sendToUsers(
        array  $recipients,
        string $title,
        string $body,
        array  $data = [],
    ): void {
        $ids = array_map(fn(Users $u) => (string) $u->getId(), $recipients);
        $ids = array_values(array_filter($ids));

        if (empty($ids)) return;

        // OneSignal accepte jusqu'à 2000 external_user_ids par requête
        foreach (array_chunk($ids, 2000) as $chunk) {
            $this->dispatch($chunk, $title, $body, $data);
        }
    }

    // ── Implémentation interne ────────────────────────────────────────────────

    private function dispatch(array $externalUserIds, string $title, string $body, array $data): void
    {
        try {
            $this->httpClient->request('POST', self::ONESIGNAL_URL, [
                'headers' => [
                    'Authorization' => 'Basic ' . $this->oneSignalRestApiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'app_id'                => $this->oneSignalAppId,
                    'include_external_user_ids' => $externalUserIds,
                    'channel_for_external_user_ids' => 'push',
                    'headings'              => ['en' => $title, 'fr' => $title],
                    'contents'              => ['en' => $body,  'fr' => $body],
                    'data'                  => $data,
                    'priority'              => 10, // 10 = high (Android)
                    'ios_sound'             => 'default',
                    'android_sound'         => 'default',
                ],
            ])->getContent();
        } catch (\Throwable $e) {
            $this->logger->warning('[OneSignal] Échec envoi notification: ' . $e->getMessage());
        }
    }
}
