# Conventions du projet

Document de référence pour le code, le nommage et le workflow Git du projet.

## Préambule

- **Outil de traduction** : [WordReference.com](https://www.wordreference.com)
- **Tout le code en anglais** (identifiants, tables, colonnes).
- **Entité** → au pluriel.
- **Colonne** (par ordre de priorité) → singulier + nom entier + précision.
  Exemple : `products_cream_antiwrinkle`.
- **Booléen** → préfixe `is_` (ex : `is_active`).

---

## 1. Règles générales

- **Langue du code** : les identifiants (variables, fonctions, tables, colonnes, branches) sont en anglais. Les commentaires, les messages de commit et la documentation sont en français.
- **Encodage** : UTF-8 partout (fichiers, base de données en `utf8mb4`).
- **Indentation** : espaces, jamais de tabulations.
  - PHP : 4 espaces.
  - HTML / CSS / JS / JSON / SQL : 2 espaces.
- **Fin de ligne** : LF (`\n`), pas CRLF. À régler dans l'éditeur.
- **Longueur de ligne** : on vise ~120 caractères maximum.
- **Un seul sujet par fichier** : si un fichier devient trop gros (> 300 lignes environ), on envisage de le découper.
- **Pas de code mort** : on supprime le code commenté inutile au lieu de le laisser « au cas où » (Git garde l'historique).

---

## 2. Nommage

### 2.1 Base de données (MySQL)

On conserve les conventions déjà en place dans le projet.

| Élément          | Convention                                | Exemple                        |
| ---------------- | ----------------------------------------- | ------------------------------ |
| Table            | `snake_case`, au pluriel, en anglais      | `products`, `delivery_types`   |
| Colonne          | `snake_case`, préfixée par l'entité       | `product_name`, `user_mail`    |
| Clé primaire     | `<entité>_id`                             | `product_id`, `user_id`        |
| Clé étrangère    | même nom que la clé primaire référencée   | `brand_id` dans `products`     |
| Table d'association | nom explicite, pas auto-généré         | `order_lines` (pas `Asso_16`)  |

- Mots-clés SQL en **MAJUSCULES** (`SELECT`, `CREATE TABLE`, `NOT NULL`).
- Une colonne par ligne dans les `CREATE TABLE`.
- Toujours `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4`.

### 2.2 PHP

On suit la norme **PSR-12**.

| Élément            | Convention                            | Exemple                          |
| ------------------ | ------------------------------------- | -------------------------------- |
| Variable           | `camelCase`                           | `$productName`, `$totalPrice`    |
| Fonction / méthode | `camelCase`, verbe d'action           | `getProductById()`, `addToCart()`|
| Classe             | `PascalCase`                          | `ProductRepository`, `CartManager`|
| Constante          | `UPPER_SNAKE_CASE`                    | `MAX_LOGIN_ATTEMPTS`             |
| Fichier de classe  | `PascalCase.php` (1 classe = 1 fichier)| `ProductRepository.php`         |

### 2.3 JavaScript

| Élément             | Convention          | Exemple                      |
| ------------------- | ------------------- | ---------------------------- |
| Variable / fonction | `camelCase`         | `cartTotal`, `updateQuantity()` |
| Classe              | `PascalCase`        | `ProductSlider`              |
| Constante           | `UPPER_SNAKE_CASE`  | `API_BASE_URL`               |
| Fichier             | `kebab-case.js`     | `cart-manager.js`            |

On déclare avec `const` par défaut, `let` si réassignation, **jamais** `var`.

### 2.4 HTML / CSS

- Classes et identifiants CSS en `kebab-case` : `.product-card`, `#main-header`.
- On réutilise les classes utilitaires de Bootstrap quand elles existent avant d'écrire du CSS maison.
- Le CSS personnalisé va dans des fichiers dédiés, pas en `style=""` inline.
- Fichiers d'assets en `kebab-case` : `product-card.css`, `hero-banner.jpg`.

---

## 3. Commentaires

- On commente le « **pourquoi** », pas le « **quoi** » : le code dit déjà ce qu'il fait.
- Tous les commentaires sont en français.

```php
// ❌ Inutile : répète le code
$total = $total + $price; // ajoute le prix au total

// ✅ Utile : explique une décision
// On applique la TVA après la remise, conformément aux CGV.
$total = ($subtotal - $discount) * 1.20;
```

- PHP : on documente les fonctions et classes avec un bloc PHPDoc.

```php
/**
 * Récupère un produit à partir de son slug.
 *
 * @param string $slug Slug unique du produit
 * @return array|null Le produit, ou null s'il n'existe pas
 */
function getProductBySlug(string $slug): ?array { ... }
```

- **TODO / FIXME** : format normalisé, avec les initiales de l'auteur.

```php
// TODO (ML) : gérer le cas du stock épuisé
// FIXME (PD) : le calcul des frais de port est faux pour les points relais
```

---

## 4. Mise en forme du code

- Une seule instruction par ligne.
- Espace autour des opérateurs : `$total = $a + $b;` (pas `$total=$a+$b;`).
- Accolade ouvrante sur la même ligne (PHP/JS) :

```php
if ($stock > 0) {
    // ...
} else {
    // ...
}
```

- **Guillemets** : simples par défaut en PHP et JS ; doubles uniquement si interpolation ou apostrophe dans la chaîne.
- On compare avec `===` / `!==` en PHP et JS (comparaison stricte).

---

## 5. Git

### 5.1 Organisation des branches

- `main` : branche toujours fonctionnelle (le code doit tourner). On ne pousse **jamais** directement dessus.
- Une branche par fonctionnalité ou correction, créée à partir de `main`.

| Préfixe     | Usage                    | Exemple                  |
| ----------- | ------------------------ | ------------------------ |
| `feature/`  | nouvelle fonctionnalité  | `feature/cart-management`|
| `fix/`      | correction de bug        | `fix/cart-total`         |
| `db/`       | modification de la base  | `db/add-slug-columns`    |
| `docs/`     | documentation            | `docs/update-readme`     |

Nom de branche en `kebab-case`, court et explicite.

### 5.2 Messages de commit

Format inspiré des **Conventional Commits** : `type(portée): description courte à l'impératif`

| Type       | Quand l'utiliser                                       |
| ---------- | ----------------------------------------------------- |
| `feat`     | nouvelle fonctionnalité                               |
| `fix`      | correction de bug                                     |
| `db`       | changement de schéma / données                        |
| `style`    | mise en forme, CSS, indentation (sans changer la logique) |
| `refactor` | réécriture sans changement de comportement            |
| `docs`     | documentation                                         |
| `chore`    | tâches diverses (config, dépendances)                 |

**Règles :**

- Description en français, à l'impératif présent, sans point final, en minuscules.
- Sujet ≤ 72 caractères. Détails éventuels dans le corps du message, après une ligne vide.
- Commits atomiques : un commit = une idée. On évite le commit fourre-tout.

```
✅ feat(catalogue): ajoute le filtre par marque
✅ fix(panier): corrige le calcul du total avec code promo
✅ db(produits): ajoute la colonne slug

❌ modifs
❌ correction de plein de trucs
❌ WIP
```

### 5.3 Avant chaque push — checklist

> ⚠️ *À faire vérifier par Guillaume*

- Le code tourne (aucune erreur, la page s'affiche).
- `git pull` (idéalement `git pull --rebase`) pour récupérer le travail de l'autre avant de pousser.
- On résout les éventuels conflits en se parlant, jamais en écrasant le travail de l'autre au hasard.
- On ne pousse aucun secret ni fichier généré (voir `.gitignore` ci-dessous).
- Commits propres et bien nommés.

### 5.4 Travail à deux (revue)

> ⚠️ *À faire vérifier par Guillaume*

- On développe sur sa branche, on pousse, puis on ouvre une merge request / pull request vers `main`.
- L'autre personne relit avant de fusionner. C'est l'occasion de repérer les erreurs et de rester au courant du code de l'autre.
- Après fusion, on supprime la branche.
- On communique sur qui travaille sur quoi pour éviter de modifier les mêmes fichiers en parallèle.

### 5.5 Fichier `.gitignore`

À placer à la racine. On ne versionne **jamais** :

```gitignore
# Dépendances
/vendor/
/node_modules/

# Configuration et secrets
.env
.env.local
config/secrets.php

# Fichiers générés / système
*.log
.DS_Store
Thumbs.db

# Éditeurs
.idea/
.vscode/

# Médias uploadés (selon le projet)
/public/uploads/
```

---

## 6. Structure de dossiers

```
/
├── public/            # point d'entrée web (index.php, assets accessibles)
│   ├── css/
│   ├── js/
│   └── images/
├── config/            # configuration (hors secrets)
├── sql/               # scripts de création et de données de la BDD
├── .gitignore
├── CONVENTIONS.md     # ce fichier
└── README.md
```
