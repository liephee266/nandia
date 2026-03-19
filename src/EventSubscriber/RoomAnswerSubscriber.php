<?php

namespace App\EventSubscriber;

use App\Entity\Room;
use App\Entity\SessionCard;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * RoomAnswerSubscriber — Force l'expiration du timer pour une SessionCard.
 *
 * Quand `startTimer()` est appelé sur une SessionCard, ce subscriber
 * planifie un Event qui rejette les réponses après expiration.
 *
 * Note : en l'absence de messenger/async queue, on fait une vérification
 * synchrone dans le contrôleur RoomController::answer().
 * Ce subscriber prépare le terrain pour une implémentation asynchrone.
 */
class RoomAnswerSubscriber
{
    // Rien à faire en sync — la vérification est dans RoomController::answer()
    // Ce subscriber existe pour :
    //   1. Logger les expirations pour le monitoring
    //   2. Permettre une migration future vers du async (Messenger)
}
