# Documentation Technique — Skin Care Beauty

**Auteurs :** Camil Bernardeau & Mathieu Chapart
**Dernière mise à jour :** 04/08/2026

---

## Historique des évolutions

| Version | Date       | Nature de l'évolution         |
| ------- | ---------- | ----------------------------- |
| 1.0.0   | 05/06/2026 | Création de la BDD SQL        |
| 1.0.1   | 09/06/2026 | Correction du script initial  |
| 1.1.0   | 04/08/2026 | Ajout du module SAV (tickets de retour) |

---

## Sommaire

- [1. Description](#1-description)
- [2. Base de Données](#2-base-de-données)
  - [2.1. Modèle conceptuel de données (MCD)](#21-modèle-conceptuel-de-données-mcd)
  - [2.2. Définition et utilisation des tables](#22-définition-et-utilisation-des-tables)
- [3. Triggers et fonction Slug](#3-triggers-et-fonction-slug)
  - [3.1. Fonction generate_slug](#31-fonction-generate_slug)
  - [3.2. Triggers](#32-triggers)
- [4. Module SAV — Tickets de retour](#4-module-sav--tickets-de-retour)
  - [4.1. Tables du module](#41-tables-du-module)
  - [4.2. Justification des choix de conception](#42-justification-des-choix-de-conception)
  - [4.3. Workflow et traçabilité](#43-workflow-et-traçabilité)

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
| `return_types`   | Recense les types de retour SAV (NPAI, adresse incomplète, colis non réclamé) |
| `ticket_status`  | Recense les statuts d'un ticket SAV (Ouvert, En cours, Clôturé)       |
| `tickets`        | Enregistre les tickets de retour SAV liés aux commandes               |
| `ticket_history` | Enregistre l'historique horodaté des actions sur les tickets SAV      |

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

---

## 4. Module SAV — Tickets de retour

Le module SAV gère les retours de colis (workflow en deux étapes : autorisation du retour, puis confirmation de la réception) avec une traçabilité complète des actions.

### 4.1. Tables du module

| Table            | Colonnes principales                                                                                     | Rôle                                            |
| ---------------- | -------------------------------------------------------------------------------------------------------- | ----------------------------------------------- |
| `return_types`   | `return_type_id`, `return_type_name` (UNIQUE)                                                             | Référentiel des types de retour : NPAI, Adresse incomplète, Colis non réclamé |
| `ticket_status`  | `ticket_status_id`, `ticket_status_name` (UNIQUE)                                                         | Référentiel des statuts : Ouvert, En cours, Clôturé |
| `tickets`        | `ticket_id`, `ticket_return_number` (UNIQUE), `ticket_comment`, `ticket_created_at`, FK vers `orders`, `return_types`, `ticket_status`, `users` | Ticket de retour, lié à une commande et à l'agent SAV créateur |
| `ticket_history` | `ticket_history_id`, `ticket_history_action`, `ticket_history_created_at`, FK vers `tickets` et `users`   | Journal horodaté : « qui fait quoi et quand »   |

**Cardinalités :**

- `orders` (0,n) — (1,1) `tickets` : une commande peut faire l'objet de plusieurs retours ; un ticket concerne exactement une commande.
- `tickets` (1,n) — (1,1) `ticket_history` : un ticket possède au minimum une ligne d'historique (sa création) ; chaque ligne appartient à un seul ticket.
- `users` (0,n) — (1,1) `tickets` / `ticket_history` : l'agent SAV (utilisateur de type Administrateur) est référencé sur chaque ticket et chaque action.

### 4.2. Justification des choix de conception

**Tables de référence plutôt qu'ENUM.** `return_types` et `ticket_status` suivent le modèle des référentiels existants (`order_status`, `delivery_types`) : ajouter un type de retour se fait par un simple `INSERT` sans `ALTER TABLE`, l'intégrité est garantie par les clés étrangères, et les libellés sont centralisés pour l'affichage.

**Historique dans une table dédiée.** L'horodatage obligatoire (« Retour créé par [Agent] », « Modification effectuée sur l'expédition par [Agent] ») est une liste d'événements de taille variable : c'est une entité à part entière, et non un attribut du ticket. Le libellé complet de l'action est stocké tel quel (`ticket_history_action`) afin de **figer le nom de l'agent au moment de l'action**, même si son compte est modifié ou supprimé par la suite.

**Numéro de retour généré en PHP (et non par trigger).** Contrairement aux numéros `CMD`/`EXP`/`FAC`, le numéro `RET-AAAA-XXXX` est produit par `TicketDAO::generateReturnNumber()` : la logique est ainsi couverte par des tests unitaires PHPUnit (format valide, séquence invalide), et le script SQL reste exempt de blocs `DELIMITER`/`DEFINER` supplémentaires, sources de problèmes à l'import sur CloudPanel. La contrainte `UNIQUE` sur `ticket_return_number` protège en dernier recours contre tout doublon.

**Sécurité.** Toutes les écritures passent par des requêtes préparées (PDO). La création d'un ticket est encapsulée dans une **transaction** (insertion du ticket, passage au statut « En cours », lignes d'historique) : soit tout est enregistré, soit rien. L'e-mail PHPMailer est envoyé **après le commit**, pour ne jamais communiquer au client un numéro de retour non enregistré ; un échec d'envoi est tracé dans l'historique sans annuler le ticket.

### 4.3. Workflow et traçabilité

| Étape | Action de l'agent SAV | Effets techniques |
| ----- | --------------------- | ----------------- |
| 1. Autorisation | Ouvre un ticket : type de retour, commande liée (statut « Expédié » uniquement), commentaire | Ticket créé au statut **Ouvert** → génération du numéro `RET-AAAA-XXXX` → passage automatique **En cours** → e-mail au client (numéro + instructions de renvoi) via PHPMailer. Historique : « Retour créé par [Agent] », « Numéro généré », « E-mail envoyé » |
| 2. Réexpédition | Contrôle le colis puis clique sur « Confirmer la réception » | Statut **Clôturé**. Historique : « Réception confirmée — modification effectuée sur l'expédition par [Agent] » |

Seules les commandes au statut **Expédié** sont liables à un ticket : un retour NPAI, adresse incomplète ou colis non réclamé suppose nécessairement qu'une expédition a eu lieu.
