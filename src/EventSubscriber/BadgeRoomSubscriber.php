<?php

namespace App\EventSubscriber;

use App\Entity\Room;
use App\Service\BadgeAssigner;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

/**
 * Attribution automatique du badge room_host quand un couple devient hôte d'une salle.
 *
 * Se déclenche quand un couple est assigné comme hostCouple sur une Room.
 * Le badge est attribué aux deux membres du couple hôte.
 */
#[AsDoctrineListener(event: Events::postUpdate)]
class BadgeRoomSubscriber
{
    public function __construct(
        private readonly BadgeAssigner $badgeAssigner,
    ) {}

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Room) {
            return;
        }

        $changeSet = $args->getObjectManager()->getUnitOfWork()->getEntityChangeSet($entity);

        // Détecter l'assignation de hostCouple
        if (!isset($changeSet['hostCouple'])) {
            return;
        }

        /** @var \App\Entity\Couple|null $couple */
        $couple = $entity->getHostCouple();
        if (!$couple) {
            return;
        }

        $user1 = $couple->getUser1();
        $user2 = $couple->getUser2();

        foreach ([$user1, $user2] as $user) {
            if (!$user) {
                continue;
            }
            try {
                $this->badgeAssigner->checkAndAssign($user, 'room_host');
            } catch (\Throwable) {
                // silent fail
            }
        }
    }
}
