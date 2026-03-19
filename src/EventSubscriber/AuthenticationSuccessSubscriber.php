<?php

namespace App\EventSubscriber;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * S'abonne au succès d'authentification JWT.
 * Génère un refresh_token opaque, le sauvegarde en base et l'ajoute à la réponse.
 *
 * La réponse de login devient :
 *   { "token": "<jwt>", "refresh_token": "<opaque_64hex>", "user": { ... } }
 */
class AuthenticationSuccessSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public static function getSubscribedEvents(): array
    {
        return [
            'lexik_jwt_authentication.on_authentication_success' => 'onAuthSuccess',
        ];
    }

    public function onAuthSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof Users) {
            return;
        }

        // Génère un nouveau refresh token et le persiste
        $refreshToken = $user->generateRefreshToken();
        $this->em->flush();

        // Ajoute le refresh token + les infos utilisateur à la réponse JSON
        $data = $event->getData();
        $data['refresh_token'] = $refreshToken;
        $data['user'] = [
            'id'    => $user->getId(),
            'email' => $user->getEmail(),
            'pseudo' => $user->getPseudo(),
        ];
        $event->setData($data);
    }
}
