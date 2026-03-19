# API Nandia

**Nandia** est une application mobile conçue pour **renforcer les liens affectifs** entre couples à travers des **échanges sincères et profonds**. L'API expose les ressources nécessaires à l'application Flutter : cartes, thèmes, sessions de jeu, réponses et journal de couple.

---

## Stack technique

- **Framework** : Symfony 7.3 + API Platform 4.2
- **Authentification** : JWT (LexikJWTAuthenticationBundle) + refresh token
- **Base de données** : PostgreSQL 17
- **ORM** : Doctrine 3.5
- **Sérialisation** : Symfony Serializer avec groupes
- **Serveur** : FrankenPHP (Caddy)
- **Containerisation** : Docker Compose

---

## Lancement rapide

```bash
docker compose up -d
docker exec symfony_app_nandia php bin/console doctrine:migrations:migrate --no-interaction
```

Ports exposés :

- `8000` → HTTP (redirige vers HTTPS)
- `8443` → HTTPS
- `5449` → PostgreSQL
- `5050` → pgAdmin

---

## Authentification

### Inscription

```
POST /api/users
Content-Type: application/ld+json

{ "email": "...", "plainPassword": "...", "pseudo": "..." }
```

Route publique, pas de token requis.

### Connexion

```
POST /api/v1/connexion
Content-Type: application/json

{ "email": "...", "password": "..." }
```

Réponse :

```json
{
  "token": "<jwt>",
  "refresh_token": "<opaque_64hex>",
  "user": { "id": 1, "email": "...", "pseudo": "..." }
}
```

### Requêtes protégées

```
Authorization: Bearer <token>
```

### Renouvellement de token

```
POST /api/token/refresh
Content-Type: application/json

{ "refresh_token": "<opaque_64hex>" }
```

Réponse : `{ "token": "<nouveau_jwt>", "refresh_token": "<nouveau_refresh>" }` — rotation à chaque utilisation, valide 30 jours.

### Négociation de format

API Platform répond en JSON-LD par défaut. Passer `Accept: application/json` pour recevoir du JSON simple :

```
Accept: application/json
```

---

## Rôles

| Rôle | Description |
|---|---|
| `ROLE_USER` | Attribué à tous les utilisateurs. Lecture sur cartes/thèmes/rituels/packs. |
| `ROLE_ADMIN` | Requis pour créer ou modifier du contenu. |

Pour promouvoir un utilisateur en admin :

```sql
UPDATE users SET roles = '["ROLE_ADMIN"]' WHERE id = <id>;
```

---

## Modèles

### Users

| Champ | Type | Notes |
|---|---|---|
| `id` | int | Auto |
| `email` | string | Unique, identifiant JWT |
| `password` | string | Haché (bcrypt) |
| `roles` | json | `[]` par défaut → `ROLE_USER` garanti |
| `pseudo` | string? | Nom d'affichage |
| `prenom`, `nom` | string? | |
| `dateNaissance` | date? | |
| `telephone` | string? | |
| `sexe` | string? | |
| `situationAmoureuse` | string? | |
| `biographie` | text? | |
| `profileImage` | string? | URL |
| `refreshToken` | string? | Token opaque 64 hex — rotation à chaque refresh |
| `refreshTokenExpiresAt` | datetime? | Expiration dans 30 jours |
| `createdAt` | datetime | |
| `updatedAt` | datetime? | |

### Theme

| Champ | Type | Notes |
|---|---|---|
| `id` | int | |
| `name` | string | |
| `description` | text? | |
| `size` | string? | `1x1` (défaut), `2x1`, `1x2` |
| `colorCode` | string? | Hex `#RRGGBB` |
| `icon` | string? | |
| `backgroundImage` | text? | |

### Card

| Champ | Type | Notes |
|---|---|---|
| `id` | int | |
| `theme` | Theme | ManyToOne |
| `questionText` | text | |
| `difficultyLevel` | smallint? | 1–3 |
| `isBonus` | bool | `false` par défaut |
| `createdAt` | datetime | |

### Session

| Champ | Type | Notes |
|---|---|---|
| `id` | int | |
| `user` | Users | ManyToOne |
| `theme` | Theme? | ManyToOne nullable — `null` = mode aléatoire |
| `mode` | string? | `'random'` ou `'theme'` |
| `startedAt` | datetime | Auto |
| `endedAt` | datetime? | Renseigné à la fermeture |

### SessionCard

| Champ | Type | Notes |
|---|---|---|
| `id` | int | |
| `session` | Session | ManyToOne |
| `card` | Card | ManyToOne |
| `drawnAt` | datetime | Auto |
| `orderIndex` | int? | Position dans la session |
| `skipped` | bool | `true` si joker utilisé |

### Response

| Champ | Type | Notes |
|---|---|---|
| `id` | int | |
| `sessionCard` | SessionCard | ManyToOne |
| `user` | Users | ManyToOne |
| `answerText` | text? | |
| `createdAt` | datetime | |

### Ritual

| Champ | Type | Notes |
|---|---|---|
| `id` | int | |
| `title` | string | |
| `description` | text? | |
| `type` | string? | `'rituel'`, `'défi'`, `'pause'`, `'joker'` |
| `theme` | Theme? | ManyToOne |

### Pack

| Champ | Type | Notes |
|---|---|---|
| `id` | int | |
| `name` | string | |
| `description` | text? | |
| `price` | decimal? | |

---

## Endpoints

### Utilisateurs

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `POST` | `/api/users` | Public | Inscription |
| `GET` | `/api/users/{id}` | `ROLE_USER` | Profil |
| `PATCH` | `/api/users/{id}` | `ROLE_USER` | Mise à jour (`application/merge-patch+json`) |
| `DELETE` | `/api/users/{id}` | `ROLE_USER` (soi-même) | Suppression de compte |
| `POST` | `/api/users/{id}/image` | `ROLE_USER` | Upload photo de profil (`multipart/form-data`) |

### Auth

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `POST` | `/api/v1/connexion` | Public | Login → JWT + refresh token |
| `POST` | `/api/token/refresh` | Public | Renouvellement JWT |
| `POST` | `/api/password-reset` | Public | Réinitialisation mot de passe |

### Thèmes

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `GET` | `/api/themes` | `ROLE_USER` | Liste (paginée) |
| `GET` | `/api/themes/{id}` | `ROLE_USER` | Détail |
| `POST` | `/api/themes` | `ROLE_ADMIN` | Créer |

### Cartes

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `GET` | `/api/cards` | `ROLE_USER` | Liste |
| `GET` | `/api/cards/{id}` | `ROLE_USER` | Détail |
| `GET` | `/api/cards/random` | `ROLE_USER` | Carte aléatoire (`?themeId=`) |
| `POST` | `/api/cards` | `ROLE_ADMIN` | Créer |

### Sessions

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `POST` | `/api/sessions` | `ROLE_USER` | Créer une session |
| `GET` | `/api/sessions/{id}` | `ROLE_USER` | Détail |
| `PATCH` | `/api/sessions/{id}` | `ROLE_USER` | Fermer (`endedAt`) |

### SessionCards

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `POST` | `/api/session_cards` | `ROLE_USER` | Enregistrer une carte piochée |
| `PATCH` | `/api/session_cards/{id}` | `ROLE_USER` | Joker (`skipped: true`) |

### Réponses & Journal

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `POST` | `/api/responses` | `ROLE_USER` | Enregistrer une réponse |
| `GET` | `/api/journal/{userId}` | `ROLE_USER` | Historique des réponses |

### Rituels & Packs

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `GET` | `/api/rituals` | `ROLE_USER` | Liste |
| `GET` | `/api/packs` | `ROLE_USER` | Liste |

### Statistiques

| Méthode | Route                 | Auth        | Description                               |
|---------|-----------------------|-------------|-------------------------------------------|
| `GET`   | `/api/stats/{userId}` | `ROLE_USER` | sessionsCount, cardsCount, responsesCount |

---

## Migrations

| Version | Description |
|---|---|
| `Version20250927024409` | Schéma initial |
| `Version20250927031751` | Ajout colonnes de base |
| `Version20260318000001` | Structure session et cartes |
| `Version20260318000002` | Profil utilisateur complet (prenom, nom, date_naissance…) |
| `Version20260319000001` | `roles` JSON sur users + FK `theme_id` sur session |
| `Version20260320000001` | Refresh token sur users |

---

## Sécurité

- Routes publiques : `POST /api/users`, `POST /api/v1/connexion`, `POST /api/token/refresh`
- Toutes les autres routes requièrent un JWT valide (`IS_AUTHENTICATED_FULLY`)
- Création de contenu réservée à `ROLE_ADMIN`
- Refresh token : rotation à chaque utilisation, expiration 30 jours
- Clés JWT : ne pas committer `config/jwt/private.pem` et `config/jwt/public.pem`
