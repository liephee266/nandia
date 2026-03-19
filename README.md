# 🎲 API Nandia

**Nandia** est une application mobile conçue pour **renforcer les liens affectifs** entre couples à travers des **échanges sincères et profonds**. L'API expose les ressources nécessaires à l'application Flutter : cartes, thèmes, sessions de jeu, réponses et journal de couple.

---

## 🛠️ Stack technique

- **Framework** : Symfony 6 + API Platform 4
- **Authentification** : JWT (LexikJWTAuthenticationBundle)
- **Base de données** : PostgreSQL
- **ORM** : Doctrine
- **Sérialisation** : Symfony Serializer avec groupes
- **Frontend** : Flutter (app mobile)

---

## 🔐 Authentification

### Connexion

```
POST /api/v1/connexion
Content-Type: application/json

{ "email": "...", "password": "..." }
```

Retourne `{ "token": "..." }`. À passer ensuite dans tous les appels protégés :

```
Authorization: Bearer <token>
```

### Inscription

```
POST /api/users
Content-Type: application/ld+json

{ "email": "...", "plainPassword": "...", "pseudo": "..." }
```

Route publique gérée par `UserCreateController` (hachage du mot de passe intégré).

### Négociation de format

L'API Platform répond en JSON-LD par défaut. Pour obtenir du JSON simple avec la clé `member` sur les collections, passer **toujours** :

```
Accept: application/json
```

---

## 👤 Rôles

| Rôle | Description |
|---|---|
| `ROLE_USER` | Attribué à tous les utilisateurs. Lecture seule sur cartes/thèmes/rituels/packs. |
| `ROLE_ADMIN` | Requis pour créer ou modifier du contenu (cartes, thèmes, packs, rituels). |

Pour promouvoir un utilisateur en admin :

```sql
UPDATE users SET roles = '["ROLE_ADMIN"]' WHERE id = <id>;
```

---

## 📚 Modèles

### Users

| Champ | Type | Notes |
|---|---|---|
| `id` | int | Auto |
| `email` | string | Unique, identifiant JWT |
| `password` | string | Haché (bcrypt) |
| `roles` | json | `[]` par défaut → `ROLE_USER` garanti ; `["ROLE_ADMIN"]` pour les admins |
| `pseudo` | string? | Nom d'affichage |
| `prenom`, `nom` | string? | |
| `dateNaissance` | date? | |
| `telephone` | string? | |
| `sexe` | string? | |
| `situationAmoureuse` | string? | |
| `biographie` | text? | |
| `profileImage` | string? | URL ou chemin |
| `createdAt` | datetime | |
| `updatedAt` | datetime? | |

### Theme

| Champ | Type | Notes |
|---|---|---|
| `id` | int | |
| `name` | string | |
| `description` | text? | |
| `size` | string? | `1x1` (défaut), `2x1`, `1x2` — utilisé pour le layout de la grille dans l'app |
| `colorCode` | string? | Hex `#RRGGBB` |
| `icon` | string? | Nom d'icône |
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
| `theme` | Theme? | ManyToOne nullable — `null` = mode aléatoire toutes thèmes |
| `mode` | string? | `'random'` ou `'theme'` |
| `startedAt` | datetime | Auto |
| `endedAt` | datetime? | Renseigné à la fermeture de session |

### SessionCard

| Champ | Type | Notes |
|---|---|---|
| `id` | int | |
| `session` | Session | ManyToOne |
| `card` | Card | ManyToOne |
| `drawnAt` | datetime | Auto |
| `orderIndex` | int? | Position dans la session |
| `skipped` | bool | `false` par défaut ; `true` si joker utilisé — patchable via `PATCH /api/session_cards/{id}` |

### Response

| Champ | Type | Notes |
|---|---|---|
| `id` | int | |
| `sessionCard` | SessionCard | ManyToOne |
| `user` | Users | ManyToOne |
| `answerText` | text? | Réponse libre |
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
| `price` | decimal? | Prix en euros |

---

## 🧩 Endpoints

### Utilisateurs

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `POST` | `/api/users` | Public | Inscription |
| `GET` | `/api/users` | `ROLE_USER` | Liste |
| `GET` | `/api/users/{id}` | `ROLE_USER` | Profil |
| `PATCH` | `/api/users/{id}` | `ROLE_USER` | Mise à jour profil (`application/merge-patch+json`) |

### Thèmes

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `GET` | `/api/themes` | `ROLE_USER` | Liste des thèmes |
| `GET` | `/api/themes/{id}` | `ROLE_USER` | Détail |
| `POST` | `/api/themes` | `ROLE_ADMIN` | Créer un thème |

### Cartes

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `GET` | `/api/cards` | `ROLE_USER` | Liste des cartes |
| `GET` | `/api/cards/{id}` | `ROLE_USER` | Détail |
| `GET` | `/api/cards/random` | `ROLE_USER` | Carte aléatoire (param optionnel `?themeId=`) |
| `POST` | `/api/cards` | `ROLE_ADMIN` | Créer une carte |

### Sessions

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `POST` | `/api/sessions` | `ROLE_USER` | Créer une session |
| `GET` | `/api/sessions/{id}` | `ROLE_USER` | Détail |
| `PATCH` | `/api/sessions/{id}` | `ROLE_USER` | Fermer une session (`endedAt`) |

**Exemple — créer une session avec thème :**

```json
POST /api/sessions
Content-Type: application/ld+json
Accept: application/json

{
  "user": "/api/users/1",
  "mode": "theme",
  "theme": "/api/themes/3"
}
```

**Exemple — créer une session aléatoire :**

```json
{
  "user": "/api/users/1",
  "mode": "random"
}
```

### SessionCards

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `POST` | `/api/session_cards` | `ROLE_USER` | Enregistrer une carte piochée |
| `GET` | `/api/session_cards/{id}` | `ROLE_USER` | Détail |
| `PATCH` | `/api/session_cards/{id}` | `ROLE_USER` | Marquer comme skippée (joker) |

**Exemple — joker :**

```json
PATCH /api/session_cards/42
Content-Type: application/merge-patch+json
Accept: application/json

{ "skipped": true }
```

### Réponses

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `POST` | `/api/responses` | `ROLE_USER` | Enregistrer une réponse |
| `GET` | `/api/responses/{id}` | `ROLE_USER` | Détail |

**Exemple :**

```json
POST /api/responses
Content-Type: application/ld+json
Accept: application/json

{
  "sessionCard": "/api/session_cards/42",
  "user": "/api/users/1",
  "answerText": "Ma réponse..."
}
```

### Journal

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `GET` | `/api/journal/{userId}` | `ROLE_USER` | Historique des réponses de l'utilisateur |

Retourne un tableau JSON :

```json
[
  {
    "id": 1,
    "questionText": "...",
    "answerText": "...",
    "themeName": "Couple",
    "themeColor": "#EC1380",
    "createdAt": "2026-03-19T10:00:00+00:00"
  }
]
```

### Rituels & Packs

| Méthode | Route | Auth | Description |
|---|---|---|---|
| `GET` | `/api/rituals` | `ROLE_USER` | Liste |
| `POST` | `/api/rituals` | `ROLE_ADMIN` | Créer |
| `GET` | `/api/packs` | `ROLE_USER` | Liste |
| `POST` | `/api/packs` | `ROLE_ADMIN` | Créer |

---

## 🚀 Installation

```bash
# 1. Dépendances
composer install

# 2. Variables d'environnement
cp .env .env.local
# Éditer DATABASE_URL, JWT_SECRET_KEY, etc.

# 3. Clés JWT
php bin/console lexik:jwt:generate-keypair

# 4. Base de données + migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# 5. Fixtures (données de démonstration)
php bin/console doctrine:fixtures:load

# 6. Serveur
symfony server:start
# ou via Docker :
docker compose up -d
```

---

## 🗄️ Migrations

| Version | Description |
|---|---|
| `Version20250927024409` | Schéma initial |
| `Version20250927031751` | Ajout colonnes de base |
| `Version20260318000001` | Structure de session et cartes |
| `Version20260318000002` | Profil utilisateur complet (prenom, nom, date_naissance…) |
| `Version20260319000001` | **Colonne `roles` (JSON) sur `users` + FK `theme_id` sur `session`** |

---

## 🔒 Sécurité — points clés

- Les routes publiques sont uniquement `/api/v1/connexion` et `POST /api/users`.
- Toutes les autres routes requièrent un JWT valide (`IS_AUTHENTICATED_FULLY`).
- La création de contenu (cartes, thèmes, packs, rituels) est réservée à `ROLE_ADMIN`.
- Le PATCH sur les utilisateurs n'est pas restreint à l'utilisateur lui-même — à sécuriser si nécessaire en prod.
