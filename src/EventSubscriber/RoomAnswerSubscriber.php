<?php

namespace App\EventSubscriber;

use App\Entity\Room;
use App\Entity\SessionCard;

/**
 * RoomAnswerSubscriber — Placeholder pour expiration asynchrone des timers.
 *
 * Explication : la vérification du timer est actuellement faite
 * **synhchronement** dans `RoomController::answer()` via
 * `SessionCard::isTimerExpired()`.
 *
 * Ce subscriber existe en préparation d'une migration vers
 * Symfony Messenger (async) pour les raisons suivantes :
 *
 *  - Éliminer la race condition si un client envoie pile au moment de l'expiration
 *  - Décharger le thread HTTP principal (important en room avec beaucoup de participants)
 *  - Permettre un vrai "scheduled job" pour les expirations
 *
 * USAGE FUTUR ( Messenger ) :
 *   - Créer un message : RoomCardTimerExpired(roomId, sessionCardId)
 *   - Dans ce subscriber : dispatcher ce message au bus Messenger
 *   - Un handler async traiterait le changement de phase + notification
 *
 * En attendant, la logique synchrone dans RoomController::answer()
 * reste correcte et safe.
 */
class RoomAnswerSubscriber
{
    // ─── Étape 1 : remplacer par un Message + Handler Messenger ───────────
    //
    // Message :
    // class RoomCardTimerExpired
    // {
    //     public function __construct(public readonly int $roomId,
    //                                 public readonly int $sessionCardId) {}
    // }
    //
    // Handler (async) :
    // #[AsMessageHandler]
    // class RoomCardTimerExpiredHandler
    // {
    //     public function __invoke(RoomCardTimerExpired $cmd,
    //                              EntityManagerInterface $em,
    //                              RoomRepository $roomRepo) { ... }
    // }
    //
    // ─── Étape 2 : modifier SessionCard::startTimer() ─────────────────────
    // Au lieu de définir timerExpiresAt directement :
    //   $dispatcher->dispatch(new RoomCardTimerExpired($roomId, $cardId));
    // Et laisser le handler définir le timer + changer la phase.
}
