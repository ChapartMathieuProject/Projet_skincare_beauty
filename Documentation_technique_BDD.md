# Documentation Technique — Skin Care Beauty

**Auteurs :** Camil Bernardeau & Mathieu Chapart
**Dernière mise à jour :** 10/06/2026

---

## Historique des évolutions

| Version | Date       | Nature de l'évolution         |
| ------- | ---------- | ----------------------------- |
| 1.0.0   | 05/06/2026 | Création de la BDD SQL        |
| 1.0.1   | 09/06/2026 | Correction du script initial  |

---

## Sommaire

- [1. Description](#1-description)
- [2. Base de Données](#2-base-de-données)
  - [2.1. Modèle conceptuel de données (MCD)](#21-modèle-conceptuel-de-données-mcd)
  - [2.2. Définition et utilisation des tables](#22-définition-et-utilisation-des-tables)
- [3. Triggers et fonction Slug](#3-triggers-et-fonction-slug)
  - [3.1. Fonction generate_slug](#31-fonction-generate_slug)
  - [3.2. Triggers](#32-triggers)

---

## 1. Description

L'enjeu de ce document est de détailler l'entièreté des éléments (tables, procédures, solutions, …) du projet **Skin Care Beauty**.

Ce projet s'inscrit dans le cadre d'un travail à réaliser en équipe durant la formation **Développeur Web et Web Mobile** dispensée par l'Afpa.

---

## 2. Base de Données

### 2.1. Modèle conceptuel de données (MCD)

![Modèle conceptuel de données du projet Skin Care Beauty](images/mcd-skin-care-beauty.png)

### 2.2. Définition et utilisation des tables

| Table            | Rôle                                                                  |
| ---------------- | --------------------------------------------------------------------- |
| `producers`      | Recense les fabricants                                                |
| `brands`         | Recense les marques                                                   |
| `product_types`  | Recense les différentes catégories de produits                        |
| `products`       | Recense les produits : composition, description, prix d'achat         |
| `pictures`       | Recense les images des produits                                       |
| `order_status`   | Recense les différents statuts de commande                            |
| `orders`         | Enregistre les commandes des clients                                  |
| `payement_types` | Recense les différents types de paiement                              |
| `delivery_types` | Recense les différents types de livraison                             |
| `users`          | Recense les différents types d'utilisateurs et leurs mots de passe    |
| `companies`      | Recense les différentes compagnies                                    |
| `customers`      | Enregistre les clients du site                                        |
| `genders`        | Recense les différents genres                                         |
| `deliveries`     | Enregistre les livraisons                                             |
| `bills`          | Enregistre les factures                                               |
| `addresses`      | Enregistre les adresses des clients                                   |

---

## 3. Triggers et fonction Slug

### 3.1. Fonction generate_slug

Fonction déterministe qui transforme une chaîne de caractères en *slug* exploitable dans une URL.

- **Paramètre :** `input_text` (`VARCHAR(250)`) — le texte source à convertir.
- **Retour :** `VARCHAR(250)` — le slug généré.

**Traitement appliqué (dans l'ordre) :**

1. Remplacement des caractères accentués par leur équivalent non accentué (`é → e`, `à → a`, `ç → c`, etc.).
2. Conversion en minuscules.
3. Remplacement des espaces par des tirets.
4. Suppression de tous les caractères restants qui ne sont ni alphanumériques ni des tirets (via `REGEXP_REPLACE`).

**Exemple :**

```text
"Création d'un Personnage"  →  creation-dun-personnage
```

### 3.2. Triggers

| Entité          | Trigger                          | Moment        | Rôle                                                                                                              |
| --------------- | -------------------------------- | ------------- | ---------------------------------------------------------------------------------------------------------------- |
| `product_types` | `before_insert_product_types`    | BEFORE INSERT | Génère le slug à partir de `product_type_name` via `generate_slug()`, puis garantit son unicité par suffixe incrémental (`-1`, `-2`, …). |
| `product_types` | `before_update_product_types`    | BEFORE UPDATE | Régénère le slug uniquement si `product_type_name` a été modifié, avec la même logique d'unicité (en excluant la ligne courante via `product_type_id`). |
| `products`      | `before_insert_products`         | BEFORE INSERT | Génère le slug à partir de `product_name` et assure son unicité par suffixe incrémental.                          |
| `products`      | `before_update_products`         | BEFORE UPDATE | Régénère le slug si `product_name` change, avec contrôle d'unicité hors ligne courante.                           |
| `orders`        | `before_num_orders`              | BEFORE INSERT | Génère un numéro de commande au format `CMD` + 7 chiffres (ex. `CMD0000001`), basé sur le nombre de commandes existantes. |
| `orders`        | `before_insert_bills`            | AFTER INSERT  | Crée automatiquement une facture dans `bills`, rattachée à la commande qui vient d'être insérée (`order_id`).     |
| `deliveries`    | `before_generate_num_deliveries` | BEFORE INSERT | Génère un numéro de livraison au format `EXP` + 7 chiffres (ex. `EXP0000001`).                                    |
| `bills`         | `before_generate_num_bills`      | BEFORE INSERT | Génère un numéro de facture au format `FAC` + 7 chiffres (ex. `FAC0000001`).                                      |
