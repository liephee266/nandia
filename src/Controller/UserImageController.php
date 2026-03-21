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

#[Route('/api', name: 'api_')]
class UserImageController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
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

        // Vérification du type MIME réel via finfo (inspecte les magic bytes du fichier,
        // pas l'extension ni l'en-tête Content-Type déclaré par le client).
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file->getPathname());

        if (!in_array($realMime, $allowedMimeTypes, true)) {
            return $this->json(['error' => 'Type de fichier non autorisé. Utilisez JPG, PNG, WebP ou GIF.'], 400);
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return $this->json(['error' => 'Le fichier ne doit pas dépasser 5 Mo.'], 400);
        }

        // Valider les dimensions de l'image (éviter les images 4K+ qui causent des problèmes mobile)
        $imageInfo = @getimagesize($file->getPathname());
        if ($imageInfo === false) {
            return $this->json(['error' => 'Fichier image invalide.'], 400);
        }
        $width  = $imageInfo[0];
        $height = $imageInfo[1];
        $maxDim = 2048;
        if ($width > $maxDim || $height > $maxDim) {
            return $this->json([
                'error' => "L'image ne doit pas dépasser {$maxDim}px de côté. Taille reçue : {$width}×{$height}px.",
            ], 400);
        }

        // Déduire l'extension depuis le MIME réel (pas depuis le nom client)
        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];
        $extension = $mimeToExt[$realMime];

        // Générer un nom de fichier unique basé sur un identifiant aléatoire (pas sur le nom client)
        $newFilename = bin2hex(random_bytes(16)) . '.' . $extension;

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
