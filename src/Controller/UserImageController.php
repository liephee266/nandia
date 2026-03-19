<?php
// src/Controller/UserImageController.php

namespace App\Controller;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/api', name: 'api_')]
class UserImageController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger,
    ) {}

    /**
     * Upload de la photo de profil.
     * Multipart/form-data avec le fichier dans le champ "image".
     * L'utilisateur ne peut modifier que sa propre image.
     */
    #[Route('/users/{id}/image', name: 'user_image_upload', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function upload(int $id, Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        $user = $this->entityManager->getRepository(Users::class)->find($id);

        if (!$user) {
            return $this->json(['error' => 'Utilisateur introuvable.'], 404);
        }

        // Seul l'utilisateur lui-même peut modifier sa photo
        if ($currentUser->getUserIdentifier() !== $user->getUserIdentifier()) {
            return $this->json(['error' => 'Accès refusé.'], 403);
        }

        $file = $request->files->get('image');
        if (!$file) {
            return $this->json(['error' => 'Aucun fichier reçu.'], 400);
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return $this->json(['error' => 'Type de fichier non autorisé. Utilisez JPG, PNG, WebP ou GIF.'], 400);
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return $this->json(['error' => 'Le fichier ne doit pas dépasser 5 Mo.'], 400);
        }

        // Générer un nom de fichier unique
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        // Dossier de destination : public/uploads/profiles/
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $file->move($uploadDir, $newFilename);

        // Supprimer l'ancienne image si elle existe
        if ($user->getProfileImage()) {
            $oldFile = $this->getParameter('kernel.project_dir') . '/public' . $user->getProfileImage();
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        // Stocker le chemin public relatif
        $publicPath = '/uploads/profiles/' . $newFilename;
        $user->setProfileImage($publicPath);
        $this->entityManager->flush();

        return $this->json([
            'imageUrl' => $publicPath,
            'message' => 'Photo de profil mise à jour.',
        ], 200);
    }
}
