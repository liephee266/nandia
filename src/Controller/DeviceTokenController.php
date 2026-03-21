<?php
// src/Controller/DeviceTokenController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * ℹ️  Ce contrôleur était utilisé pour enregistrer les tokens FCM.
 *
 * Depuis la migration vers OneSignal, l'enregistrement des appareils
 * est géré directement par le SDK Flutter (OneSignal.login(userId)).
 * Le backend n'a plus besoin de stocker de device token.
 *
 * Ces routes sont conservées en stub (204 No Content) pour ne pas casser
 * les éventuels anciens clients mobiles encore en circulation.
 */
#[Route('/api', name: 'api_')]
#[IsGranted('ROLE_USER')]
class DeviceTokenController extends AbstractController
{
    /** POST /api/device-token — stub OneSignal (plus d'action côté backend) */
    #[Route('/device-token', name: 'device_token_register', methods: ['POST'])]
    public function register(): JsonResponse
    {
        return new JsonResponse(null, 204);
    }

    /** DELETE /api/device-token — stub OneSignal */
    #[Route('/device-token', name: 'device_token_delete', methods: ['DELETE'])]
    public function delete(): JsonResponse
    {
        return new JsonResponse(null, 204);
    }
}
