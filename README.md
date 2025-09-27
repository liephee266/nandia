Le projet Mix &amp; Match a pour objectif de développer une plateforme web interactive dédiée à la gestion et à la présentation des composants de parfumerie (flacons, pompes, capots) dans le cadre de l’activité Private Label du Groupe ARTHES. 


# 📌 **Spécification API Mix & Match**

---

## 🔑 Authentification

* **Type** : JWT (LexikJWTAuthenticationBundle)
* **Endpoints :**

  * `POST /auth/login`

    * **Payload** : `{ "email": "user@mail.com", "password": "secret" }`
    * **Response 200** : `{ "token": "jwt-token" }`
  * Les endpoints internes (`/users`, `/composants`, `/vues_clients`) nécessitent un **Authorization: Bearer <token>`.
  * Les endpoints publics (`/views/{id}`) sont **sans auth**.

---

## 👤 Utilisateurs (Interne – Back-office)

### `POST /users`

➡️ Créer un utilisateur (réservé super_admin).

```json
{
  "email": "admin@mail.com",
  "password": "password123",
  "role": "admin"
}
```

**Response 201** : `{ "id": "uuid", "email": "admin@mail.com", "role": "admin" }`

---

### `GET /users`

➡️ Liste des utilisateurs internes.
**Response 200** :

```json
[
  { "id": "uuid1", "email": "super@mail.com", "role": "super_admin" },
  { "id": "uuid2", "email": "admin@mail.com", "role": "admin" }
]
```

---

### `DELETE /users/{id}`

➡️ Supprimer un utilisateur.
**Response 204** : No Content.

---

## 📦 Composants

### `GET /composants`

➡️ Lister et filtrer les composants.
**Exemple requête :**
`/composants?type=flacon&origine.id=1&gamme.id=3&moq[gte]=500`

**Response 200 :**

```json
[
  {
    "id": "uuid",
    "nom": "Flacon Cylindrique 50ml",
    "type": "flacon",
    "contenance": 50,
    "moq": 1000,
    "disponibilite": "en_stock",
    "reference_interne": "FLA-123",
    "image_url": "/uploads/composants/flacon_50ml.png",
    "origine": "/origines/1",
    "fournisseur": "/fournisseurs/2",
    "forme": "/formes/3",
    "benchmark": "/benchmarks/1",
    "gamme": "/gammes/4"
  }
]
```

---

### `POST /composants`

➡️ Créer un composant.

```json
{
  "nom": "Capot Carré Luxe",
  "type": "capot",
  "contenance": null,
  "moq": 500,
  "disponibilite": "sur_demande",
  "reference_interne": "CAP-456",
  "image_url": "/uploads/composants/capot_carre.png",
  "origine": "/origines/2",
  "fournisseur": "/fournisseurs/5",
  "forme": "/formes/2",
  "benchmark": "/benchmarks/2",
  "gamme": "/gammes/3"
}
```

**Response 201** : `{ "id": "uuid", ... }`

---

### `PUT /composants/{id}`

➡️ Modifier un composant existant.

---

### `DELETE /composants/{id}`

➡️ Supprimer un composant.
**Response 204** : No Content.

---

## 🌍 Référentiels (Origine, Fournisseur, Forme, Benchmark, Gamme)

Chaque entité est exposée avec CRUD complet :

* `GET /origines` – Liste
* `POST /origines` – Ajouter
* `PUT /origines/{id}` – Modifier
* `DELETE /origines/{id}` – Supprimer

Idem pour `/fournisseurs`, `/formes`, `/benchmarks`, `/gammes`.

**Exemple : GET /gammes**

```json
[
  { "id": "1", "nom": "mass market" },
  { "id": "2", "nom": "prestige" }
]
```

---

## 👓 Vues clients (interne)

### `GET /vues_clients`

➡️ Liste des vues créées.

**Response 200** :

```json
[
  {
    "id": "uuid",
    "nom": "Vue Dior Asie",
    "lien_unique": "a1b2c3d4",
    "date_creation": "2025-09-26T10:00:00Z"
  }
]
```

---

### `POST /vues_clients`

➡️ Créer une vue et y associer des composants.

```json
{
  "nom": "Vue Client Luxe",
  "composants": [
    { "id": "uuid-flacon", "ordre": 1 },
    { "id": "uuid-capot", "ordre": 2 }
  ]
}
```

**Response 201** :

```json
{
  "id": "uuid",
  "nom": "Vue Client Luxe",
  "lien_unique": "xyz123",
  "date_creation": "2025-09-26T10:00:00Z",
  "composants": [...]
}
```

---

### `GET /vues_clients/{id}`

➡️ Détails d’une vue + composants liés.

---

### `DELETE /vues_clients/{id}`

➡️ Supprimer/désactiver une vue.

---

### `POST /vues_clients/{id}/duplicate`

➡️ Dupliquer une vue avec ses composants.

---

## 🔗 Accès client (public)

### `GET /views/{lien_unique}`

➡️ Accès public client à sa vue filtrée.
**Response 200** :

```json
{
  "nom": "Vue Dior Asie",
  "composants": [
    {
      "id": "uuid-flacon",
      "nom": "Flacon Cylindrique 50ml",
      "type": "flacon",
      "image_url": "/uploads/composants/flacon.png"
    },
    {
      "id": "uuid-capot",
      "nom": "Capot Carré Luxe",
      "type": "capot",
      "image_url": "/uploads/composants/capot.png"
    }
  ]
}
```

---

# 📑 Notes techniques

* Tous les endpoints internes → protégés par **JWT**.
* Les liens clients (`/views/{lien_unique}`) → lecture seule, pas d’auth.
* **Swagger** exposera la doc auto-générée à :

  * `/docs` (interface Swagger UI)
  * `/docs.json` (OpenAPI spec)

---
