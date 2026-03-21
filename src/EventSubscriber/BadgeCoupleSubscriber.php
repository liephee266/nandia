<?php

namespace App\EventSubscriber;

use App\Entity\Couple;
use App\Service\BadgeAssigner;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

/**
 * Attribution automatique du badge couple_joined quand un couple est activé.
 *
 * Se déclenche quand un utilisateur rejoint une invitation ( Couple::activate() ).
 * Les deux membres recoivent le badge.
 */
#[AsDoctrineListener(event: Events::postUpdate)]
class BadgeCoupleSubscriber
{
    public function __construct(
        private readonly BadgeAssigner $badgeAssigner,
    ) {}

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Couple) {
            return;
        }

        $changeSet = $args->getObjectManager()->getUnitOfWork()->getEntityChangeSet($entity);

        // Détecter le passage à STATUS_ACTIVE (user2 a rejoint)
        if (!isset($changeSet['status'])) {
            return;
        }

        $newStatus = $changeSet['status'][1] ?? null;
        if ($newStatus !== Couple::STATUS_ACTIVE) {
            return;
        }

        $user1 = $entity->getUser1();
        $user2 = $entity->getUser2();

        foreach ([$user1, $user2] as $user) {
            if (!$user) {
                continue;
            }
            try {
                $this->badgeAssigner->checkAndAssign($user, 'couple_joined');
            } catch (\Throwable) {
                // silent fail
            }
        }
    }
}
