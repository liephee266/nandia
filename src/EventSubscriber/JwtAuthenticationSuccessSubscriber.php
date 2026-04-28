<?php

namespace App\EventSubscriber;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Enrichit la réponse de login JWT avec :
 *  - refresh_token opaque (généré et persisté en base)
 *  - utilisateur complet sérialisé (groupe 'user:read')
 *  - rôle principal
 *
 * Fusion de l'ancien AuthenticationSuccessSubscriber et JWTEventExceptionListenerSubscriber
 * pour éviter les conflits d'ordre d'exécution.
 */
class JwtAuthenticationSuccessSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SerializerInterface $serializer,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            // Priorité haute pour s'assurer qu'on est le seul à modifier la réponse
            'lexik_jwt_authentication.on_authentication_success' => ['onAuthSuccess', 100],
        ];
    }

    public function onAuthSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof Users) {
            return;
        }

        // 1. Générer et persister le refresh token
        $refreshToken = $user->generateRefreshToken();
        $this->em->flush();

        // 2. Sérialiser l'utilisateur complet depuis la BDD
        $trueUser = $this->em->getRepository(Users::class)->find($user->getId());
        $userData = $this->serializer->serialize($trueUser, 'json', ['groups' => 'user:read']);
        $userData = json_decode($userData, true);

        // 3. Ajouter le rôle principal et nettoyer
        $userData['role'] = $user->getRoles()[0] ?? 'ROLE_USER';
        unset($userData['roles']);

        // 4. Construire la réponse finale
        $data = $event->getData();
        $data['token'] = $data['token'] ?? null;
        $data['refresh_token'] = $refreshToken;
        $data['user'] = $userData;

        $event->setData($data);
    }
}
