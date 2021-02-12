# Blog

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/33a24272cf9f4e90bf63cb5989587b8a)](https://app.codacy.com/gh/asainama/blog?utm_source=github.com&utm_medium=referral&utm_content=asainama/blog&utm_campaign=Badge_Grade)

Ce site est un blog dans le cadre de la formation OC.

Le projet est donc de développer votre blog professionnel. Ce site web se décompose en deux grands groupes de pages :

-   les pages utiles à tous les visiteurs ;
-   les pages permettant d’administrer votre blog.

Voici la liste des pages qui devront être accessibles depuis votre site web :

-   la page d'accueil
-   la page listant l’ensemble des blog posts
-   la page affichant un blog post
-   la page permettant d’ajouter un blog post
-   la page permettant de modifier un blog post -
-   les pages permettant de modifier/supprimer un blog post
-   les pages de connexion/enregistrement des utilisateurs.

## Prérequis

-   PHP >= 7.1
-   Mysql 8.0.19V
-   Composer 2.0V
-   SwiftMailer 6.2V
-   phpdotenv 5.3V
-   twig 3.2V
  
## Installation

Pour installer le projet:

-   Configurer le projet à partir la variable .env
-   Se référer au fichier .env.test
-   Utiliser le fichier bd.sql pour créer la base de données
-   Installer les packages avec la commande:  

```bash
    composer install
```

### Configuration du .env

Pour configurer le projet:

Il faut créer un fichier /.env à la racine du projet

Le fichier devra être construit comme ceci:

```bash
    APP_ENV=dev
    # Database
    DB_HOST=127.0.0.1/ip_database
    DB_NAME=blog
    DB_USER=root
    DB_PASSWORD=password
    DB_PORT=3306/port_database

    # Swift Mailer
    MAILER_TRANSPORT=smtp.example.com
    MAILER_PORT=''
    MAILER_PROTOCOLE=''
    MAILER_USER=''
    MAILER_PASS=''
```

### Charger la base données

Pour charger la base données avec un jeu de tests:

```bash
    php ./commands/fill.php
```

### Lancer le projet

Pour lancer le projet, il est possible d'utiliser (wamp\xampp...) ou:

```bash
    php -S localhost:8000 -t ./public
```

### Pour l'administration

Compte admin:

-   email : s@s.fr
-   mdp : admin
