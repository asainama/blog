# Blog

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/33a24272cf9f4e90bf63cb5989587b8a)](https://app.codacy.com/gh/asainama/blog?utm_source=github.com&utm_medium=referral&utm_content=asainama/blog&utm_campaign=Badge_Grade)

    Ce site est un blog dans le cadre de la formation OC.
##  Prérequis

- PHP >= 7.1
- Mysql
- Composer
  
## Installation

Pour installer le projet:

    - Configurer le projet à partir la variable .env
    - Se référer au fichier .env.test
    - Utiliser le fichier bd.sql pour créer la base de données
    - Installer les packages avec la commande:
     
```
    composer install
```

### Charger la base données

Pour charger la base données avec un jeu de tests:

```
    php .\commands\fill.php
```

### Lancer le projet

Le lancer le site utiliser (wamp\xampp...):

```
    php -S localhost:8000 -t ./public
```

### Pour l'administration

Compte admin:

    - email : s@s.fr
    - mdp : admin





