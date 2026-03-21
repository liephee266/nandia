<?php
namespace App\Services;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class GenericEntityManager
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        PropertyAccessorInterface $propertyAccessor,
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * Insère une entité basée sur des données dynamiques.
     *
     * @param string $entityClass Nom complet de l'entité (e.g., App\Entity\User)
     * @param array $data Données à mapper sur l'entité
     * @return array Liste des erreurs ou un tableau vide si succès
     */
    public function persistEntity(string $entityClass, array $data, bool $update = false): array
    {
        // Normalise les clés du JSON en camelCase
        // Crée une nouvelle instance de l'entité
        $entity = "";
        if ($update==false) {
            $entity = new $entityClass();
        }else {
            $entity = $this->entityManager->getRepository($entityClass)->find($data['id']);
        }
        unset($data['id']);
        // Récupère les métadonnées de l'entité
        // Champs sensibles jamais modifiables via cette API générique
        $blacklist = ['roles', 'password', 'refreshToken', 'refreshTokenExpiresAt',
                      'refreshTokenRevokedAt', 'refreshTokenIssuedAt', 'resetToken',
                      'resetTokenExpiresAt', 'deviceToken', 'createdAt'];

        $metadata = $this->entityManager->getClassMetadata($entityClass);
        foreach ($data as $field => $value) {
            if (in_array($field, $blacklist, true)) {
                continue;
            }
            // Vérifie si le champ est mappé dans l'entité
            if ($metadata->hasField($field)) {
                // Affecte la valeur du champ
                $this->propertyAccessor->setValue($entity, $field, $value);
            }
            // Gestion des associations (relations Doctrine)
            if ($metadata->hasAssociation($field)) {
                $associationMetadata = $metadata->getAssociationMapping($field);
                // Si l'association est une relation "to-one"
                if ($associationMetadata['type'] & ClassMetadata::TO_ONE) {
                    $relatedEntity = $this->entityManager
                        ->getRepository($associationMetadata['targetEntity'])
                        ->find($value['id'] ?? $value);
                    if ($relatedEntity) {
                        $this->propertyAccessor->setValue($entity, $field, $relatedEntity);
                    }
                }
            }
        }
        // Valide l'entité
        $errors = $this->validator->validate($entity);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            return $errorMessages; // Retourne les erreurs
        }
        // Persiste l'entité
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        return ["entity" => $entity]; // Aucune erreur
    }
    
    /**
     * Normalise les clés du JSON en camelCase.
     *
     * @param array $data //Données à normaliser
     * @return array //Données normalisées
     * 
     * @author Orphée Lié <lieloumloum@gmail.com>
     */
    function normalizeKeysToCamelCase(array $data): array
    {
        $normalized = [];
        foreach ($data as $key => $value) {
            $camelCaseKey = lcfirst(str_replace('_', '', ucwords($key, '_')));
            $normalized[$camelCaseKey] = is_array($value) ? $this->normalizeKeysToCamelCase($value) : $value;
        }
        return $normalized;
    }

}