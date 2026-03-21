# API Nandia

**Nandia** est une application mobile conçue pour **renforcer les liens affectifs** entre couples à travers des **échanges sincères et profonds**. L'API expose les ressources nécessaires à l'application Flutter : cartes, thèmes, sessions de jeu solo, sessions couple, salles multijoueur, journal, badges et statistiques.

---

## Stack technique

| Composant | Détail |
|---|---|
| Framework | Symfony 7.3 + API Platform 4.2 |
| Authentification | JWT (LexikJWTAuthenticationBundle) + refresh token (rotation) |
| Base de données | PostgreSQL 17 |
| ORM | Doctrine 3.5 |
| Sérialisation | Symfony Serializer avec groupes |
| Serveur | FrankenPHP (Caddy) — HTTP→HTTPS redirect automatique |
| Temps réel | Mercure (SSE) + conteneur Docker dédié |
| Async | Symfony Messenger (transport `sync://` en dev) |
| Push | OneSignal API v1 |
| Mail | Symfony Mailer (configurable : Gmail, SendGrid, Mailgun…) |
| Monitoring | Sentry |
| Conteneurisation | Docker Compose (4 services) |

---

## Lancement rapide

```bash
# 1. Copier et remplir les variables d'environnement
cp .env.example .env.local

# 2. Démarrer les conteneurs
docker compose up -d

# 3. Appliquer les migrations
docker exec symfony_app_nandia php bin/console doctrine:migrations:migrate --no-interaction
```

Ports exposés :

| Port | Service |
|---|---|
| `8000` | HTTP (redirige vers HTTPS via 308) |
| `8443` | HTTPS |
| `5449` | PostgreSQL |
| `5050` | pgAdmin |
| `1337` | Mercure SSE |

---

## Authentification

### Inscription

```
POST /api/users
Content-Type: application/ld+json

{ "email": "...", "plainPassword": "...", "pseudo": "..." }
```

Route publique. `plainPassword` doit faire ≥ 8 caractères et contenir au moins 1 chiffre.

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

### Déconnexion

```
POST /api/logout
Authorization: Bearer <token>
```

Révoque le refresh token côté serveur.

### Réinitialisation de mot de passe

```
POST /api/password-reset/request
Content-Type: application/json

{ "email": "..." }
```

```
POST /api/password-reset/confirm
Content-Type: application/json

{ "email": "...", "token": "...", "newPassword": "..." }
```

Token valable 1 heure. Réponse générique pour éviter l'énumération d'emails.

### Négociation de format

API Platform répond en JSON-LD par défaut. Passer `Accept: application/json` pour recevoir du JSON simple :

```
Accept: application/json
```

---

## Rôles

| Rôle | Description |
|---|---|
| `ROLE_USER` | Attribué à tous les utilisateurs. Accès à toutes les routes protégées. |
| `ROLE_ADMIN` | Requis pour créer ou modifier du contenu (cartes, thèmes, etc.). |

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
| `plainPassword` | string? | Virtuel — jamais persisté |
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
| `resetToken` | string? | Token reset mot de passe (1h) |
| `resetTokenExpiresAt` | datetime? | |
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
| `theme` | Theme? | ManyToOne nullable |
| `mode` | string | `solo`, `couple_live`, `couple_relax`, `room` |
| `startedAt` | datetime | Auto |
| `endedAt` | datetime? | Renseigné à la fermeture |
| `updatedAt` | datetime? | Mis à jour via `PreUpdate` |

### SessionCard

| Champ | Type | Notes |
|---|---|---|
| `id` | int | |
| `session` | Session | ManyToOne |
| `card` | Card | ManyToOne |
| `drawnAt` | datetime | Auto |
| `orderIndex` | int? | Position dans la session |
| `skipped` | bool | `true` si joker utilisé |
| `favorited` | bool | `false` par défaut |

### Response

| Champ | Type | Notes |
|---|---|---|
| `id` | int | |
| `sessionCard` | SessionCard | ManyToOne |
| `user` | Users | ManyToOne |
| `answerText` | text? | |
| `createdAt` | datetime | |

### Couple

| Champ | Type | Notes |
|---|---|---|
| `id` | int | |
| `user1` | Users | ManyToOne |
| `user2` | Users? | ManyToOne nullable |
| `inviteCode` | string | 6 caractères sans ambiguïté |
| `status` | string | `pending`, `active` |
| `inviteExpiresAt` | datetime? | |
| `createdAt` | datetime | |

### Room

| Champ | Type | Notes |
|---|---|---|
| `id` | int | |
| `code` | string | Code d'accès 6 caractères |
| `host` | Users | ManyToOne |
| `theme` | Theme? | ManyToOne nullable |
| `status` | string | `waiting`, `playing`, `ended` |
| `currentCard` | Card? | |
| `phase` | string? | `answering`, `voting` |
| `createdAt` | datetime | |
| `startedAt` | datetime? | |
| `endedAt` | datetime? | |
| `updatedAt` | datetime? | Mis à jour via `PreUpdate` |

### RoomParticipant

| Champ | Type | Notes |
|---|---|---|
| `id` | int | |
| `room` | Room | ManyToOne |
| `user` | Users | ManyToOne |
| `couple` | Couple? | ManyToOne nullable |
| `score` | int | `0` par défaut |
| `joinedAt` | datetime | |

### Badge

| Champ | Type | Notes |
|---|---|---|
| `id` | int | |
| `user` | Users | ManyToOne |
| `type` | string | Identifiant du badge |
| `label` | string | Nom affiché |
| `description` | text? | |
| `earnedAt` | datetime | |

### Pack / Ritual

Contenu éditorial. Accès en lecture via API Platform.

---

## Endpoints

### Utilisateurs

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `POST` | `/api/users` | Public | Inscription |
| `GET` | `/api/users/{id}` | `ROLE_USER` | Profil |
| `PATCH` | `/api/users/{id}` | `ROLE_USER` | Mise à jour (`application/merge-patch+json`) |
| `DELETE` | `/api/users/{id}` | `ROLE_USER` | Suppression de compte |
| `POST` | `/api/users/{id}/image` | `ROLE_USER` | Upload photo de profil (`multipart/form-data`) |

### Auth

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `POST` | `/api/v1/connexion` | Public | Login → JWT + refresh token |
| `POST` | `/api/token/refresh` | Public | Renouvellement JWT |
| `POST` | `/api/logout` | `ROLE_USER` | Déconnexion + révocation refresh token |
| `POST` | `/api/password-reset/request` | Public | Envoi email de reset |
| `POST` | `/api/password-reset/confirm` | Public | Confirmation reset |

### Thèmes & Cartes

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `GET` | `/api/themes` | `ROLE_USER` | Liste |
| `GET` | `/api/themes/{id}` | `ROLE_USER` | Détail |
| `GET` | `/api/cards/random` | `ROLE_USER` | Carte aléatoire (`?themeId=&sessionId=&difficulty=`) |

### Sessions solo

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `POST` | `/api/sessions` | `ROLE_USER` | Créer une session |
| `GET` | `/api/sessions/{id}` | `ROLE_USER` | Détail |
| `PATCH` | `/api/sessions/{id}` | `ROLE_USER` | Fermer (`endedAt`) |
| `POST` | `/api/session_cards` | `ROLE_USER` | Enregistrer une carte piochée |
| `PATCH` | `/api/session_cards/{id}` | `ROLE_USER` | Joker (`skipped: true`) |
| `POST` | `/api/session-cards/{id}/favorite` | `ROLE_USER` | Toggle favori |
| `POST` | `/api/responses` | `ROLE_USER` | Enregistrer une réponse |

### Couple

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `POST` | `/api/couple/create` | `ROLE_USER` | Créer un couple (génère un code d'invitation) |
| `POST` | `/api/couple/join` | `ROLE_USER` | Rejoindre via code |
| `GET` | `/api/couple/me` | `ROLE_USER` | Récupérer son couple |
| `POST` | `/api/couple/regenerate` | `ROLE_USER` | Régénérer le code d'invitation |
| `DELETE` | `/api/couple/leave` | `ROLE_USER` | Quitter le couple |

### Session couple

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `POST` | `/api/couple-session/create` | `ROLE_USER` | Créer une session couple |
| `GET` | `/api/couple-session/{id}/state` | `ROLE_USER` | État en temps réel |
| `POST` | `/api/couple-session/{id}/respond` | `ROLE_USER` | Soumettre une réponse |
| `POST` | `/api/couple-session/{id}/next-card` | `ROLE_USER` | Carte suivante |
| `POST` | `/api/couple-session/{id}/close` | `ROLE_USER` | Terminer la session |
| `GET` | `/api/couple-session/{id}/stream` | `ROLE_USER` | SSE stream (Mercure) |

### Salle multijoueur (Room)

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `POST` | `/api/room/create` | `ROLE_USER` | Créer une salle |
| `POST` | `/api/room/join` | `ROLE_USER` | Rejoindre via code |
| `GET` | `/api/room/{id}/state` | `ROLE_USER` | État en temps réel |
| `POST` | `/api/room/{id}/start` | `ROLE_USER` | Démarrer (hôte uniquement) |
| `POST` | `/api/room/{id}/answer` | `ROLE_USER` | Soumettre une réponse |
| `POST` | `/api/room/{id}/next-card` | `ROLE_USER` | Carte suivante (hôte) |
| `DELETE` | `/api/room/{id}/leave` | `ROLE_USER` | Quitter la salle |
| `GET` | `/api/room/{id}/stream` | `ROLE_USER` | SSE stream (Mercure) |
| `POST` | `/api/vote` | `ROLE_USER` | Voter pour une réponse (+2 pts) |

### Journal & Stats

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `GET` | `/api/journal/me` | `ROLE_USER` | Historique des réponses (avec filtres) |
| `GET` | `/api/stats/{userId}` | `ROLE_USER` | Statistiques (cache 5 min) |
| `GET` | `/api/badges/me` | `ROLE_USER` | Badges obtenus |

### Contenu éditorial

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `GET` | `/api/rituals` | `ROLE_USER` | Liste des rituels |
| `GET` | `/api/packs` | `ROLE_USER` | Liste des packs |

---

## Rate limiting

| Endpoint | Limite | Fenêtre |
|---|---|---|
| `POST /api/v1/connexion` | 5 tentatives | 15 minutes (par IP) |
| `POST /api/users` | 3 inscriptions | 1 heure (par IP) |
| `POST /api/token/refresh` | 10 requêtes | 1 minute (par IP) |
| `POST /api/password-reset/*` | 5 requêtes | 15 minutes (par IP) |

---

## Sécurité

- Routes publiques : `POST /api/users`, `POST /api/v1/connexion`, `POST /api/token/refresh`, `POST /api/password-reset/*`
- Toutes les autres routes requièrent un JWT valide (`IS_AUTHENTICATED_FULLY`)
- Refresh token : rotation à chaque utilisation, expiration 30 jours
- Reset token : comparaison via `hash_equals()` (protection timing attack), valide 1 heure
- Upload image : vérification MIME via `finfo`, limite de taille et dimensions
- Ne pas committer `config/jwt/private.pem` et `config/jwt/public.pem`
- Copier `.env.example` vers `.env.local` — ne jamais committer `.env.local`

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
| `Version20260320000002` | Entités Couple, Room, RoomParticipant, Badge, CardVote |
| `Version20260325000001` | Reset token sur users |
| `Version20260325000002` | `updatedAt` sur Room et Session + `favorited` sur SessionCard |
