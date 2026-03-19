<?php

namespace App\EventSubscriber;

use App\Entity\Response;
use App\Service\BadgeAssigner;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

/**
 * Attribution automatique des badges de réponse après chaque Response créée.
 *
 * Se déclenche quand une nouvelle réponse est persistée en base.
 * Les badges 'response_saved' sont vérifiés et attribués silencieusement.
 */
#[AsDoctrineListener(event: Events::postPersist)]
class BadgeResponseSubscriber
{
    public function __construct(
        private readonly BadgeAssigner $badgeAssigner,
    ) {}

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Response) {
            return;
        }

        $user = $entity->getUser();
        if (!$user) {
            return;
        }

        try {
            $this->badgeAssigner->checkAndAssign($user, 'response_saved');
        } catch (\Throwable) {
            // On ne bloque pas le flux pour un échec de badging
        }
    }
}
