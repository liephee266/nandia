# 🎲 API Nandia

**Nandia** est une application mobile conçue pour **renforcer les liens affectifs** entre couples à travers des **échanges sincères et profonds**. L’application propose des **cartes de questions** organisées par **thèmes**, permettant de (re)découvrir l’autre, de partager des émotions, des fantasmes, des valeurs et des projets communs.

---

## 🧩 Objectifs de l’API

L’API Nandia permet de :

- Gérer les **utilisateurs** (inscription, connexion, profil).
- Créer, lire, modifier et supprimer des **thèmes** et des **cartes de questions**.
- Gérer les **parties** (sessions de jeu) et les **réponses** des utilisateurs.
- Permettre la **personnalisation** (thèmes, musique, ambiance).
- Sauvegarder un **journal de couple** (historique des réponses).
- Gérer les **extensions** et **packs de cartes** (thématiques supplémentaires).

---

## 🛠️ Technologies utilisées

- **Backend** : Symfony 6 + API Platform
- **Authentification** : JWT
- **Base de données** : PostgreSQL
- **ORM** : Doctrine
- **Frontend** : Flutter (application mobile)

---

## 🔐 Authentification

L’API utilise l’authentification **JWT**.

- **Connexion** : `POST /connexion`
  - Body : `{ "email": "...", "password": "..." }`
  - Retour : `{ "token": "..." }`

- **Accès aux ressources protégées** : `Authorization: Bearer <token>`

---

## 📚 Modèles principaux

### 1. **User**

- `id`
- `email`
- `password` (haché)
- `pseudo`
- `createdAt`
- `updatedAt`

### 2. **Theme**

- `id`
- `name` (ex. "Couple & Attentes", "Valeurs & Croyances")
- `icon` (optionnel)
- `colorCode` (ex. `#ec1380`)
- `createdAt`
- `cards` (OneToMany avec `Card`)

### 3. **Card**

- `id`
- `theme` (ManyToOne avec `Theme`)
- `questionText`
- `difficultyLevel` (1 à 3)
- `isBonus` (booléen)
- `createdAt`

### 4. **Session**

- `id`
- `user`
- `startedAt`
- `endedAt`
- `mode` (rapide, classique, compétitif)

### 5. **SessionCard**

- `id`
- `session` (ManyToOne avec `Session`)
- `card` (ManyToOne avec `Card`)
- `drawnAt`
- `skipped`
- `orderIndex`

### 6. **Response**

- `id`
- `sessionCard`
- `user`
- `answerText`
- `createdAt`

---

## 🧩 Endpoints principaux

### 🔐 Gestion des utilisateurs

- `GET /api/users` → Liste des utilisateurs
- `POST /api/users` → Créer un utilisateur
- `GET /api/users/{id}` → Détail d’un utilisateur

### 🎴 Gestion des thèmes

- `GET /api/themes` → Liste des thèmes
- `POST /api/themes` → Créer un thème
- `GET /api/themes/{id}` → Détail d’un thème

### 🧩 Gestion des cartes

- `GET /api/cards` → Liste des cartes
- `POST /api/cards` → Créer une carte
- `GET /api/cards/{id}` → Détail d’une carte

### 🎲 Gestion des parties

- `GET /api/sessions` → Liste des sessions
- `POST /api/sessions` → Créer une session
- `GET /api/sessions/{id}` → Détail d’une session

### 📝 Gestion des réponses

- `GET /api/responses` → Liste des réponses
- `POST /api/responses` → Créer une réponse
- `GET /api/responses/{id}` → Détail d’une réponse

---

## 🧪 Exemples d’utilisation

### Récupérer une carte aléatoire

```bash
GET /api/cards
Authorization: Bearer <token>
```

### Créer un utilisateur

```bash
POST /api/users
Content-Type: application/ld+json

{
  "email": "test@example.com",
  "plainPassword": "password123",
  "pseudo": "TestUser"
}
```

---

## 🚀 Installation

1. Clonez le dépôt.
2. Installez les dépendances : `composer install`
3. Configurez la base de données dans `.env`.
4. Générez les clés JWT : `php bin/console lexik:jwt:generate-keypair`
5. Créez la base de données : `php bin/console doctrine:database:create`
6. Générez les migrations : `php bin/console doctrine:migrations:migrate`
7. Chargez les fixtures : `php bin/console doctrine:fixtures:load`
8. Lancez l’application : `symfony server:start`

---

## 🧪 Tests

- Tests unitaires et d’intégration disponibles via PHPUnit.
- Lancer les tests : `./bin/phpunit`

---

## 🧩 Extensions

L’API est conçue pour être **extensible** :

- Ajouter des **thèmes personnalisés**
- Créer des **packs de cartes**
- Intégrer des **fonctionnalités additionnelles** (musique, ambiances, etc.)

---

## 📱 Application mobile

L’application Flutter associée permet de :

- Consulter les cartes
- Créer des parties
- Sauvegarder les réponses
- Visualiser un journal de couple
- Gérer les paramètres

---


