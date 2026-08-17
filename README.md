# Knowledge Learning

Knowledge Learning est une plateforme e-learning développée avec Symfony.

L’application permet aux utilisateurs de créer un compte, de l’activer par e-mail, de consulter un catalogue de formations, d’acheter des cursus complets ou des leçons individuelles, de suivre leur progression et d’obtenir des certifications.

Une interface d’administration permet également de gérer les contenus pédagogiques, les utilisateurs et les commandes.

---

## Objectifs du projet

Le projet a pour objectif de proposer une plateforme d’apprentissage en ligne permettant :

- la gestion d’un catalogue organisé par thèmes, cursus et leçons ;
- l’inscription et l’activation des comptes utilisateurs ;
- l’authentification sécurisée ;
- la réinitialisation du mot de passe ;
- l’achat de cursus complets ;
- l’achat de leçons individuelles ;
- le paiement via Stripe Checkout ;
- la gestion des droits d’accès aux contenus achetés ;
- le suivi de progression ;
- la validation des leçons ;
- l’attribution automatique de certifications ;
- l’administration des contenus, utilisateurs et commandes.

---

## Fonctionnalités principales

### Utilisateur

Un utilisateur peut :

- créer un compte ;
- recevoir un e-mail d’activation ;
- activer son compte grâce à un lien sécurisé ;
- se connecter et se déconnecter ;
- demander la réinitialisation de son mot de passe ;
- définir un nouveau mot de passe ;
- consulter le catalogue des formations ;
- consulter les cursus et leurs leçons ;
- acheter un cursus complet ;
- acheter une leçon individuelle ;
- payer avec Stripe Checkout ;
- accéder aux contenus qu’il possède ;
- consulter ses commandes en attente ;
- reprendre le paiement d’une commande en attente ;
- suivre sa progression ;
- valider une leçon terminée ;
- consulter ses certifications ;
- afficher une certification obtenue.

### Administration

Un administrateur peut :

- accéder à un tableau de bord dédié ;
- gérer les thèmes ;
- créer, modifier et supprimer des thèmes ;
- gérer les cursus ;
- créer, modifier et supprimer des cursus ;
- gérer les leçons ;
- créer, modifier et supprimer des leçons ;
- consulter les utilisateurs ;
- activer manuellement un compte utilisateur ;
- consulter les commandes d’un utilisateur ;
- annuler une commande en attente ;
- consulter l’état des paiements ;
- protéger les contenus déjà achetés ou utilisés contre certaines suppressions.

---

## Organisation pédagogique

Les contenus sont organisés selon la hiérarchie suivante :

```text
Thème
└── Cursus
    └── Leçon
```

Un thème peut contenir plusieurs cursus.

Un cursus appartient à un thème et contient plusieurs leçons.

Une leçon appartient à un cursus.

L’utilisateur peut acheter :

- un cursus complet ;
- une leçon individuelle.

L’achat d’un cursus complet donne automatiquement accès aux leçons qui lui appartiennent.

---

## Technologies utilisées

### Backend

- PHP 8.2+
- Symfony 7.4
- Doctrine ORM
- Doctrine DBAL
- Doctrine Migrations
- Symfony Security
- Symfony Form
- Symfony Validator
- Symfony Mailer
- Symfony Console

### Base de données

- MariaDB

Une version MariaDB 10.6 ou supérieure est recommandée.

### Frontend

- Twig
- HTML
- CSS
- JavaScript
- Symfony AssetMapper
- Symfony Stimulus

### Paiement

- Stripe Checkout
- Stripe PHP SDK
- Stripe Webhooks

### Tests

- PHPUnit 11
- Symfony BrowserKit
- Symfony CssSelector
- Doctrine avec base de test dédiée

### Outils

- Composer
- Git
- GitHub
- Symfony CLI
- Stripe CLI

---

## Architecture du projet

Le projet suit une architecture Symfony organisée autour des contrôleurs, services, entités, repositories et formulaires.

```text
knowledge-learning/
│
├── assets/
│   ├── controllers/
│   ├── styles/
│   │   ├── admin.css
│   │   ├── app.css
│   │   ├── certifications.css
│   │   └── my-training.css
│   ├── app.js
│   ├── controllers.json
│   └── stimulus_bootstrap.js
│
├── bin/
│   ├── console
│   └── phpunit
│
├── config/
│   ├── packages/
│   │   ├── asset_mapper.yaml
│   │   ├── cache.yaml
│   │   ├── csrf.yaml
│   │   ├── doctrine.yaml
│   │   ├── doctrine_migrations.yaml
│   │   ├── framework.yaml
│   │   ├── mailer.yaml
│   │   ├── messenger.yaml
│   │   ├── monolog.yaml
│   │   ├── security.yaml
│   │   ├── twig.yaml
│   │   └── validator.yaml
│   │
│   ├── routes/
│   ├── bundles.php
│   ├── routes.yaml
│   └── services.yaml
│
├── migrations/
│   └── Version*.php
│
├── public/
│   └── index.php
│
├── src/
│   ├── Command/
│   │   └── CreateAdminCommand.php
│   │
│   ├── Controller/
│   │   ├── Admin/
│   │   │   ├── AdminCourseController.php
│   │   │   ├── AdminDashboardController.php
│   │   │   ├── AdminLessonController.php
│   │   │   ├── AdminThemeController.php
│   │   │   └── AdminUserController.php
│   │   │
│   │   ├── ActivationController.php
│   │   ├── CertificationController.php
│   │   ├── HomeController.php
│   │   ├── LessonProgressController.php
│   │   ├── MyTrainingController.php
│   │   ├── PasswordResetController.php
│   │   ├── PaymentController.php
│   │   ├── PurchaseController.php
│   │   ├── RegistrationController.php
│   │   ├── SecurityController.php
│   │   ├── StripeWebhookController.php
│   │   └── TrainingController.php
│   │
│   ├── DataFixtures/
│   │   ├── AppFixtures.php
│   │   ├── LearningContentFixtures.php
│   │   └── RoleFixtures.php
│   │
│   ├── Entity/
│   │   ├── Certification.php
│   │   ├── Course.php
│   │   ├── CustomerOrder.php
│   │   ├── Lesson.php
│   │   ├── LessonProgress.php
│   │   ├── OrderItem.php
│   │   ├── Role.php
│   │   ├── Theme.php
│   │   └── User.php
│   │
│   ├── Form/
│   │   ├── Admin/
│   │   │   ├── CourseType.php
│   │   │   ├── LessonType.php
│   │   │   └── ThemeType.php
│   │   │
│   │   ├── ForgotPasswordType.php
│   │   ├── RegistrationFormType.php
│   │   └── ResetPasswordType.php
│   │
│   ├── Repository/
│   │   ├── CertificationRepository.php
│   │   ├── CourseRepository.php
│   │   ├── CustomerOrderRepository.php
│   │   ├── LessonProgressRepository.php
│   │   ├── LessonRepository.php
│   │   ├── OrderItemRepository.php
│   │   ├── RoleRepository.php
│   │   ├── ThemeRepository.php
│   │   └── UserRepository.php
│   │
│   ├── Service/
│   │   ├── AccountActivationService.php
│   │   ├── CertificationService.php
│   │   ├── ContentAccessService.php
│   │   ├── LessonProgressService.php
│   │   ├── PasswordResetService.php
│   │   ├── PurchaseService.php
│   │   └── StripePaymentService.php
│   │
│   └── Kernel.php
│
├── templates/
│   ├── account/
│   ├── admin/
│   ├── bundles/
│   │   └── TwigBundle/
│   │       └── Exception/
│   │           ├── error.html.twig
│   │           ├── error403.html.twig
│   │           └── error404.html.twig
│   │
│   ├── emails/
│   ├── home/
│   ├── registration/
│   ├── security/
│   ├── training/
│   └── base.html.twig
│
├── tests/
│   ├── Controller/
│   │   ├── ActivationControllerTest.php
│   │   ├── RegistrationControllerTest.php
│   │   └── SecurityControllerTest.php
│   │
│   ├── Repository/
│   │   └── OrderItemRepositoryTest.php
│   │
│   ├── Service/
│   │   ├── AccountActivationServiceTest.php
│   │   └── PurchaseServiceTest.php
│   │
│   └── bootstrap.php
│
├── translations/
│
├── .env
├── .env.dev
├── .env.test
├── .gitignore
├── composer.json
├── composer.lock
├── importmap.php
├── phpunit.dist.xml
├── README.md
└── symfony.lock
```

---

## Architecture applicative

Les responsabilités sont séparées afin de conserver un code maintenable.

### Controllers

Les contrôleurs traitent les requêtes HTTP et coordonnent les actions de l’application.

Ils évitent autant que possible de contenir directement les règles métier complexes.

### Services

Les services regroupent la logique métier.

Principaux services :

- `PurchaseService` : création et réutilisation des commandes ;
- `ContentAccessService` : gestion des droits d’accès aux contenus ;
- `LessonProgressService` : gestion de la progression ;
- `CertificationService` : attribution des certifications ;
- `AccountActivationService` : envoi des e-mails d’activation ;
- `PasswordResetService` : réinitialisation sécurisée du mot de passe ;
- `StripePaymentService` : création des sessions Stripe Checkout.

### Repositories

Les repositories regroupent les requêtes spécifiques d’accès aux données.

Ils permettent notamment de déterminer :

- si un utilisateur possède un cursus ;
- si un utilisateur possède une leçon ;
- si une commande est déjà en attente ;
- si toutes les leçons d’un cursus sont terminées ;
- si une certification existe déjà.

### Entities

Les principales entités Doctrine sont :

```text
User
Role
Theme
Course
Lesson
CustomerOrder
OrderItem
LessonProgress
Certification
```

---

## Prérequis

Avant d’installer le projet, vérifier que les éléments suivants sont disponibles :

- PHP 8.2 ou supérieur ;
- Composer ;
- MariaDB 10.6 ou supérieur recommandé ;
- Git.

Pour tester les paiements Stripe en local :

- Stripe CLI.

---

## Installation locale

Cloner le dépôt :

```bash
git clone https://github.com/adjolokoueyabana/Site_e-Learning_PHP-Symfony.git
cd Site_e-Learning_PHP-Symfony
```

Installer les dépendances PHP :

```bash
composer install
```

---

## Configuration locale

Créer un fichier :

```text
.env.local
```

à la racine du projet.

Exemple :

```dotenv
DATABASE_URL="mysql://USER:PASSWORD@127.0.0.1:3306/knowledge_learning?serverVersion=10.11-MariaDB&charset=utf8mb4"

MAILER_DSN="smtp://USER:PASSWORD@HOST:PORT"
MAIL_FROM_ADDRESS="no-reply@example.com"

STRIPE_SECRET_KEY="sk_test_..."
STRIPE_WEBHOOK_SECRET="whsec_..."
```

Les vraies valeurs sensibles ne doivent jamais être ajoutées dans les fichiers versionnés.

Les fichiers suivants sont ignorés par Git :

```text
.env.local
.env.*.local
```

---

## Base de données

Créer la base :

```bash
php bin/console doctrine:database:create
```

Appliquer les migrations :

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

Vérifier le schéma :

```bash
php bin/console doctrine:schema:validate
```

---

## Fixtures

Des fixtures permettent de créer les données nécessaires au fonctionnement du projet.

Elles peuvent notamment créer :

- les rôles ;
- les thèmes ;
- les cursus ;
- les leçons.

Pour les charger :

```bash
php bin/console doctrine:fixtures:load
```

Attention : cette commande peut supprimer les données existantes avant de recréer les fixtures.

---

## Comptes et rôles

Deux rôles principaux existent :

```text
ROLE_CLIENT
ROLE_ADMIN
```

### ROLE_CLIENT

Permet notamment :

- d’accéder à l’espace personnel ;
- d’acheter des contenus ;
- de consulter les formations achetées ;
- de valider les leçons ;
- de consulter les certifications.

### ROLE_ADMIN

Permet notamment :

- d’accéder à `/admin` ;
- de gérer les contenus pédagogiques ;
- de consulter les utilisateurs ;
- de consulter et gérer certaines commandes.

---

## Création d’un administrateur

Une commande Symfony permet de créer un administrateur vérifié :

```bash
php bin/console app:create-admin admin@example.com Prenom Nom
```

Le mot de passe est demandé de manière interactive.

Il n’apparaît donc pas dans la commande et n’est pas enregistré directement dans l’historique du terminal.

Le mot de passe doit contenir au minimum :

- 8 caractères ;
- une lettre majuscule ;
- une lettre minuscule ;
- un chiffre ;
- un caractère spécial.

---

## Lancement du projet en local

Avec Symfony CLI :

```bash
symfony server:start
```

L’application est ensuite généralement disponible sur :

```text
http://127.0.0.1:8000
```

Pour arrêter le serveur :

```bash
symfony server:stop
```

---

## Assets

Le projet utilise Symfony AssetMapper.

Les fichiers sources sont principalement situés dans :

```text
assets/
```

Pour compiler les assets en production :

```bash
php bin/console asset-map:compile --env=prod
```

Les fichiers générés sont placés dans :

```text
public/assets/
```

Le dossier compilé n’est pas versionné.

---

## Stripe

Les paiements sont réalisés avec Stripe Checkout.

### Variables nécessaires

```dotenv
STRIPE_SECRET_KEY="sk_test_..."
STRIPE_WEBHOOK_SECRET="whsec_..."
```

### Paiement

Un utilisateur peut acheter :

- un cursus ;
- une leçon.

Une commande est d’abord créée avec le statut :

```text
pending
```

Après confirmation du paiement Stripe, elle passe au statut :

```text
paid
```

Une commande peut également être :

```text
cancelled
```

### Stripe CLI en local

Pour écouter les webhooks :

```bash
stripe listen --forward-to http://127.0.0.1:8000/stripe/webhook
```

Stripe CLI retourne alors un secret :

```text
whsec_...
```

qui doit être configuré dans `.env.local`.

### Sécurisation du webhook

Le webhook vérifie notamment :

- la signature Stripe ;
- l’identifiant de la commande ;
- l’identifiant de la session Checkout ;
- le statut du paiement ;
- la devise ;
- le montant ;
- l’idempotence du traitement.

Le paiement n’est validé dans l’application qu’après confirmation par Stripe.

---

## Sécurité des achats

Les achats utilisent des requêtes HTTP `POST` et sont protégés avec des tokens CSRF.

La route de paiement n’accepte pas directement une requête GET.

Un utilisateur doit également :

- être connecté ;
- avoir un compte activé ;
- avoir le rôle approprié.

L’application empêche également un utilisateur de racheter un contenu qu’il possède déjà.

Une commande en attente existante est réutilisée afin d’éviter les doublons.

---

## E-mails

Symfony Mailer est utilisé pour envoyer :

- les e-mails d’activation de compte ;
- les e-mails de réinitialisation du mot de passe.

Configuration :

```dotenv
MAILER_DSN="smtp://..."
MAIL_FROM_ADDRESS="no-reply@example.com"
```

En environnement de test, l’envoi réel peut être désactivé :

```dotenv
MAILER_DSN=null://null
```

---

## Activation du compte

Après inscription :

1. l’utilisateur est créé avec un compte non vérifié ;
2. un token d’activation sécurisé est généré ;
3. un e-mail contenant un lien d’activation est envoyé ;
4. l’utilisateur clique sur le lien ;
5. son compte passe à l’état vérifié ;
6. le token d’activation est supprimé.

Un lien déjà utilisé ne peut pas être réutilisé.

---

## Réinitialisation du mot de passe

L’utilisateur peut demander la réinitialisation de son mot de passe.

Le système :

1. génère un token sécurisé ;
2. stocke uniquement son empreinte ;
3. définit une date d’expiration ;
4. envoie un lien par e-mail ;
5. permet de définir un nouveau mot de passe ;
6. supprime le token après utilisation.

---

## Progression

La progression d’un utilisateur est enregistrée pour chaque leçon.

Une leçon peut être marquée comme terminée.

Pour un cursus, la progression peut être calculée avec :

```text
nombre de leçons terminées
/
nombre total de leçons
```

---

## Certifications

Une certification peut être attribuée lorsqu’un utilisateur termine l’ensemble des contenus requis pour un thème.

Chaque certification possède notamment :

- un utilisateur ;
- un thème ;
- un numéro de certificat unique ;
- des informations d’audit.

Les certifications sont consultables depuis :

```text
/mes-certifications
```

---

## Administration

L’espace administration est disponible sous :

```text
/admin
```

Il est réservé aux utilisateurs possédant :

```text
ROLE_ADMIN
```

Les principaux écrans permettent de gérer :

```text
/admin/themes
/admin/cursus
/admin/lecons
/admin/utilisateurs
```

Les suppressions sont protégées afin d’éviter de supprimer des contenus déjà référencés par des achats ou des progressions.

---

## Pages d’erreur personnalisées

L’application possède des pages personnalisées pour :

```text
403
404
500 et autres erreurs
```

Les templates sont situés dans :

```text
templates/bundles/TwigBundle/Exception/
```

avec notamment :

```text
error403.html.twig
error404.html.twig
error.html.twig
```

---

## Base de données de test

Les tests utilisent une base séparée de la base de développement.

Exemple :

```text
knowledge_learning_test
```

Le fichier local peut être configuré dans :

```text
.env.test.local
```

Exemple :

```dotenv
DATABASE_URL="mysql://USER:PASSWORD@127.0.0.1:3306/knowledge_learning?serverVersion=10.11-MariaDB&charset=utf8mb4"

MAILER_DSN=null://null
MAIL_FROM_ADDRESS=test@knowledge-learning.local

STRIPE_SECRET_KEY=sk_test_dummy
STRIPE_WEBHOOK_SECRET=whsec_test_dummy
```

Doctrine ajoute automatiquement le suffixe `_test` en environnement de test.

---

## Tests automatisés

Pour lancer toute la suite :

```bash
php bin/phpunit
```

État actuel :

```text
21 tests
122 assertions
0 échec
```

Les tests couvrent notamment :

- l’accès à la page d’inscription ;
- l’inscription d’un utilisateur ;
- l’attribution du rôle client ;
- la génération du token d’activation ;
- le stockage sécurisé du mot de passe ;
- l’accès à la page de connexion ;
- la connexion avec des identifiants valides ;
- le refus d’un mot de passe incorrect ;
- l’activation du compte ;
- les tokens d’activation invalides ;
- l’impossibilité de réutiliser un lien d’activation ;
- la génération de l’e-mail d’activation ;
- l’interdiction d’achat pour un compte non activé ;
- la création d’une commande de cursus ;
- la création d’une commande de leçon ;
- la réutilisation des commandes en attente ;
- l’interdiction de racheter un contenu déjà possédé ;
- les accès aux cursus achetés ;
- les accès aux leçons achetées ;
- les accès aux leçons incluses dans un cursus ;
- les requêtes Doctrine de certains repositories.

---

## Sécurité

Le projet met notamment en place :

- mots de passe hashés avec Symfony PasswordHasher ;
- protection CSRF ;
- contrôle des rôles ;
- restrictions des routes d’administration ;
- vérification du statut du compte avant achat ;
- sessions Stripe créées uniquement depuis des requêtes POST sécurisées ;
- validation des webhooks Stripe ;
- validation du montant Stripe ;
- validation de la devise Stripe ;
- protection contre le traitement multiple d’un webhook ;
- tokens d’activation ;
- tokens de réinitialisation temporaires ;
- variables sensibles non versionnées ;
- compte MariaDB dédié au projet ;
- base PHPUnit séparée ;
- saisie masquée du mot de passe lors de la création d’un administrateur.

---

## Audit et traçabilité

Les principales entités possèdent des champs de suivi tels que :

```text
createdAt
updatedAt
createdBy
updatedBy
```

Ils permettent de conserver des informations de traçabilité sur les créations et modifications des données.

---

## Vérifications avant déploiement

### Composer

```bash
composer validate --strict
composer audit
```

État actuel :

```text
composer.json valide
aucune vulnérabilité connue détectée
```

### Tests

```bash
php bin/phpunit
```

Résultat actuel :

```text
21 tests
122 assertions
0 échec
```

### Conteneur Symfony

```bash
php bin/console lint:container --env=prod
```

### Templates Twig

```bash
php bin/console lint:twig templates --env=prod
```

### Doctrine

```bash
php bin/console doctrine:schema:validate --env=prod
```

### Migrations

```bash
php bin/console doctrine:migrations:status --env=prod
```

### Cache production

```bash
php bin/console cache:clear --env=prod --no-debug
php bin/console cache:warmup --env=prod --no-debug
```

### Assets

```bash
php bin/console asset-map:compile --env=prod
```

---

## Déploiement

Le projet est prévu pour être déployé sur alwaysdata.

L’environnement de production doit disposer notamment de :

- PHP 8.2 ou supérieur ;
- MariaDB 10.6 ou supérieur ;
- Composer ;
- accès aux variables d’environnement ;
- HTTPS ;
- configuration d’un webhook Stripe accessible publiquement.

### Étapes principales

1. récupérer le dépôt Git ;
2. installer les dépendances Composer ;
3. configurer les variables d’environnement de production ;
4. configurer la base MariaDB ;
5. exécuter les migrations Doctrine ;
6. compiler les assets ;
7. vider et réchauffer le cache Symfony ;
8. faire pointer le site vers le dossier `public/` ;
9. configurer le service d’e-mail ;
10. configurer Stripe ;
11. configurer le webhook Stripe de production ;
12. vérifier les parcours utilisateur et administrateur.

### URL de production

L’application est déployée sur alwaysdata et accessible à l’adresse suivante :

```text
https://superdevop.alwaysdata.net
```

---

## Dépôt GitHub

Le code source est disponible sur GitHub :

```text
https://github.com/adjolokoueyabana/Site_e-Learning_PHP-Symfony
```

---

## État actuel du projet

Les principaux contrôles réalisés avant le déploiement sont :

```text
Symfony 7.4
PHP 8.2
Doctrine valide
Migrations à jour
Schéma Doctrine synchronisé
Templates Twig valides
Conteneur Symfony valide
Assets de production compilables
Composer valide
Aucune vulnérabilité Composer connue
21 tests PHPUnit
122 assertions
0 échec
Git propre
```

---

## Améliorations possibles

Le projet peut encore évoluer avec notamment :

- davantage de tests automatisés ;
- une couverture de test plus importante sur Stripe ;
- un tableau de bord utilisateur plus avancé ;
- des statistiques de progression ;
- des notifications supplémentaires ;
- une gestion plus avancée des certifications ;
- une interface d’administration enrichie ;
- une pagination des listes importantes ;
- une recherche avancée dans le catalogue ;
- une gestion de profil utilisateur ;
- une amélioration continue de l’accessibilité.

---

## Auteur

**ADJOLOKOU Eyabana**

Projet réalisé dans le cadre de la formation **Développeur Web Full Stack**.