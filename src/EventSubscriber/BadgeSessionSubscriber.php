<?php

namespace App\EventSubscriber;

use App\Entity\Session;
use App\Service\BadgeAssigner;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

/**
 * Attribution automatique des badges après complétion d'une session.
 *
 * Se déclenche quand une session passe à endedAt != null.
 * Les badges sont vérifiés et attribués silencieusement — pas d'erreur
 * si l'utilisateur n'en a pas de nouveau.
 */
#[AsDoctrineListener(event: Events::postUpdate)]
class BadgeSessionSubscriber
{
    public function __construct(
        private readonly BadgeAssigner $badgeAssigner,
    ) {}

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Session) {
            return;
        }

        // Vérifier que la session vient d'être terminée (endedAt vient d'être défini)
        // On vérifie que le changeset contient endedAt
        $changeSet = $args->getObjectManager()->getUnitOfWork()->getEntityChangeSet($entity);

        if (!isset($changeSet['endedAt'])) {
            return;
        }

        $user = $entity->getUser();
        if (!$user) {
            return;
        }

        // Attribuer les badges de session (silencieux — aucune erreur si rien de nouveau)
        try {
            $this->badgeAssigner->checkAndAssign($user, 'session_completed');
            $this->badgeAssigner->checkStreak($user);
        } catch (\Throwable) {
            // On ne bloque pas le flux pour un échec de badging
        }
    }
}
