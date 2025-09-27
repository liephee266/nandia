<?php
// src/EventSubscriber/UserPasswordHasherSubscriber.php

namespace App\EventSubscriber;


use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\PreUpdateEventArgs;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPasswordHasherSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        dd('prePersist appelé sur Users');
        if (!$entity instanceof Users) {
            return;
        }

        $this->updatePassword($entity);
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Users) {
            return;
        }

        $this->updatePassword($entity);
    }

    private function updatePassword(Users $user): void
    {
        if (null === $user->getPlainPassword()) {
            return;
        }
        

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $user->getPlainPassword())
        );
    }

    public function getSubscribedEvents(): array
    {
        return [
            'prePersist',
            'preUpdate',
        ];
    }
}