![PHPUnit Tests](https://github.com/ChapartMathieuProject/Projet_skincare_beauty/actions/workflows/phpunit.yml/badge.svg)

# Projet Skincare Beauty

Application e-commerce PHP 8.2 / MySQL 8, entièrement conteneurisée.
Aucune installation locale de PHP, MySQL ou Apache n'est nécessaire.

## Prérequis

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (avec Docker Compose v2)
- Git

## Installation

```bash
git clone https://github.com/ChapartMathieuProject/Projet_skincare_beauty.git
cd Projet_skincare_beauty
cp .env.example .env      # sous Windows PowerShell : copy .env.example .env
docker compose up -d --build
```

Le premier démarrage prend quelques minutes : construction de l'image PHP,
téléchargement de MySQL et import automatique de la base.

Vérifier que tout tourne :

```bash
docker compose ps
```

Les cinq services doivent être en état `running` (le service `db` passe
d'abord par `starting` le temps du healthcheck).

## Accès

| Service     | URL                     | Identifiants                        |
|-------------|-------------------------|-------------------------------------|
| Application | http://localhost:8080   | voir comptes de test ci-dessous     |
| phpMyAdmin  | http://localhost:8081   | `skincarebeauty_user` / `azertyui`  |
| Mailpit     | http://localhost:8025   | —                                   |

Mailpit intercepte tous les e-mails sortants (inscription, réinitialisation de
mot de passe, notifications de fidélité). Aucun message n'est réellement envoyé.

### Comptes de test

| Rôle          | E-mail                  | Mot de passe   |
|---------------|-------------------------|----------------|
| Administrateur| admin@skincare.com      | *(voir SQL)*   |
| Client        | sophie.martin@email.com | `Client1234!`  |
| Client        | lucas.dupont@email.com  | `Client1234!`  |
| Client        | emma.bernard@email.com  | `Client1234!`  |

## Ports utilisés

`8080`, `8081`, `8025`, `1025`.

⚠️ Si XAMPP, WAMP ou un MySQL local tourne sur la machine, il n'y a pas de
conflit : aucun service n'expose le 80 ni le 3306. En cas de port déjà pris,
modifier le mapping dans `docker-compose.yml` (partie gauche du `-`
uniquement, ex. `"8090:80"`).

## Services

| Conteneur                   | Rôle                                              |
|-----------------------------|---------------------------------------------------|
| `skincarebeauty_app`        | Apache + PHP 8.2, sert l'application               |
| `skincarebeauty_db`         | MySQL 8.0, base importée au premier démarrage      |
| `skincarebeauty_phpmyadmin` | Administration de la base                          |
| `skincarebeauty_mail`       | Mailpit, capture des e-mails                       |
| `skincarebeauty_cron`       | Tâche planifiée : notification des points expirants|

La tâche cron s'exécute tous les jours à 9h00 et écrit dans
`/var/log/cron_loyalty.log` du conteneur.

## Base de données

Le schéma et les données de démonstration sont dans
`sql/data/creation_database.sql`, importés automatiquement par
`sql/00-init.sh` au tout premier démarrage du conteneur MySQL.

Pour repartir d'une base vierge :

```bash
docker compose down -v      # -v supprime le volume db_data
docker compose up -d --build
```

## Tests

```bash
docker compose exec app composer install
docker compose exec app ./vendor/bin/phpunit
```

## Dépannage

```bash
docker compose logs -f            # tous les services
docker compose logs -f cron       # un service en particulier
docker compose restart app
docker compose down && docker compose up -d --build
```

**Un conteneur redémarre en boucle** — `docker compose logs <service>` donne
la cause. Pour le cron, une erreur `exec format error` signifie que les fins de
ligne du script ont été converties en CRLF : vérifier que `.gitattributes` est
bien présent et relancer `git checkout -- docker/`.

**Page blanche ou erreur de connexion à la base** — le conteneur `db` met une
trentaine de secondes à devenir sain au premier lancement. Attendre, puis
`docker compose restart app`.

## Arrêt

```bash
docker compose down        # arrête et supprime les conteneurs
docker compose down -v     # + supprime les données de la base
```
