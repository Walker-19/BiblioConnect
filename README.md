# BiblioConnect

Application web de gestion de bibliothèque développée avec Symfony 8.

## Fonctionnalités

- **Catalogue public** : navigation des livres avec recherche, filtres par catégorie/langue/auteur, notes moyennes
- **Espace utilisateur** : réservations de livres, favoris (toggle AJAX), commentaires avec notation
- **Espace bibliothécaire** : gestion et validation des réservations (approbation / annulation)
- **Espace administrateur** :
  - CRUD complet livres, auteurs, catégories, langues
  - Modération des commentaires (en attente / publié / rejeté)
  - Gestion des utilisateurs et de leurs rôles
  - Tableau de bord avec statistiques

## Stack technique

| Couche | Technologie |
|---|---|
| Langage | PHP >= 8.4 |
| Framework | Symfony 8.0 |
| ORM | Doctrine ORM 3 + Migrations |
| Templates | Twig 3 |
| Frontend | AssetMapper + Stimulus + UX Turbo |
| Messagerie | Symfony Messenger |
| Tests | PHPUnit |

## Prérequis

- PHP >= 8.4
- Composer
- Docker (pour PostgreSQL)
- [Symfony CLI](https://symfony.com/download) (recommandé)

## Installation

```bash
# 1. Cloner le dépôt et installer les dépendances PHP
composer install

# 2. Démarrer PostgreSQL via Docker
docker compose up -d

# 3. Configurer l'environnement
cp .env .env.local
# Modifier DATABASE_URL dans .env.local si besoin
```

## Base de données

```bash
# Créer la base et appliquer les migrations
php bin/console doctrine:migrations:migrate

# Charger les données de démonstration (optionnel)
php bin/console doctrine:fixtures:load
```

## Lancer l'application

```bash
symfony server:start
```


## Rôles et hiérarchie

| Rôle | Accès |
|---|---|
| `ROLE_USER` | Réservations, favoris, commentaires |
| `ROLE_LIBRARIAN` | + Gestion des réservations |
| `ROLE_ADMIN` | + CRUD catalogue, utilisateurs, tableau de bord |

## Tests

```bash
php bin/phpunit
```

## Structure du projet

```
src/
├── Controller/     # Contrôleurs (Book, Reservation, Comment, User…)
├── Entity/         # Entités Doctrine
├── Form/           # Formulaires Symfony
├── Repository/     # Requêtes personnalisées
├── Security/       # Handlers login/access denied
└── DataFixtures/   # Données de démonstration
templates/          # Vues Twig
migrations/         # Migrations Doctrine
assets/             # JS (Stimulus) + CSS
```
