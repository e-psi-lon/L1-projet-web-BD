# Projet L1 Web-BD - Corpus Digitale

Ce dépôt contient le code source du projet de web et base de données de première année de licence. Il s'agit d'une application web permettant d'explorer une bibliothèque de textes classiques de l'Antiquité latine.

## Lancement

Ce projet utilise la fonctionnalité de PHP permettant de définir un routeur.
Pour lancer le projet, il faut donc démarrer le serveur en précisant d'utiliser le fichier router.php comme routeur. Par exemple, en utilisant le serveur de développement de PHP :

### Ligne de commande PHP

```bash
php -S localhost:8000 router.php
```

### UWamp

1. Ouvrir le panneau de configuration d'UWamp
2. Assurez-vous que le projet est placé dans le répertoire www d'UWamp
3. Vérifiez que le module mod_rewrite est activé dans Apache
4. Le fichier `.htaccess` fourni dans le projet est déjà configuré pour rediriger les requêtes vers le routeur
5. Démarrez les services Apache et MySQL depuis le panneau de configuration d'UWamp
6. Accédez au site via l'URL: `http://localhost/[nom-du-dossier-projet]`
  - Par exemple, si votre dossier s'appelle "corpus-digitale": [`http://localhost/corpus-digitale`](http://localhost/corpus-digitale)

## Structure

Le projet est divisé en plusieurs parties :

- **[assets](/assets/)** : Contient les ressources statiques
  - **[`/css`](/assets/css/)** : Feuilles de style CSS
  - **[`/js`](/assets/js/)** : Scripts JavaScript
- **[account](/account)** : Gestion de l'authentification
  - Login, inscription et déconnexion
- **[config](/config/)** : Configuration et utilitaires
- **[database](/database/)** : Schéma de la base de données
- **[includes](/includes/)** : Composants réutilisables (header, footer)

## Base de données
## Base de données

Le projet utilise SQLite comme système de gestion de base de données. Le schéma est défini dans `schema.sql` et comprend les tables suivantes :

### Tables

| Table             | Description                  | Champs principaux                                                                                |
|-------------------|------------------------------|--------------------------------------------------------------------------------------------------|
| **`authors`**     | Informations sur les auteurs | `author_id`, `name`, `birth_year`, `death_year`, `biography`                                     |
| **`books`**       | Ouvrages des auteurs         | `book_id`, `author_id`, `title`, `publication_year`, `description`                               |
| **`chapters`**    | Chapitres des livres         | `chapter_id`, `book_id`, `title`, `chapter_number`, `content`                                    |
| **`users`**       | Gestion des utilisateurs     | `user_id`, `username`, `email`, `password`, `is_admin`                                           |
| **`suggestions`** | Suggestions de contenu       | `suggestion_id`, `user_id`, `suggestion_type`, `content`, `status`, `admin_notes`, `reviewed_by` |

### Relations

Le schéma de données s'articule autour des relations suivantes :

- Un **auteur** (`authors`) peut avoir écrit plusieurs **livres** (`books`) *(relation 1,1 - 0,N)*
- Un **livre** (`books`) contient plusieurs **chapitres** (`chapters`) *(relation 1,1 - 0,N)*
- Un **utilisateur** (`users`) peut soumettre plusieurs **suggestions** (`suggestions`) *(relation 1,1 - 0,N)*


## Fonctionnalités

### Navigation
- Page d'accueil présentant le projet
- Liste des auteurs avec recherche par nom
- Pages détaillées pour chaque auteur avec leurs œuvres
- Pages de livres avec description et accès aux chapitres
- Affichage des chapitres avec recherche de contenu
- Suggestions de contenu par les utilisateurs

### Authentification
- Inscription des utilisateurs
- Connexion/déconnexion
- Profils utilisateurs
 - Profils utilisateurs pouvant fournir des suggestions de contenu
 - Profils administrateurs pour gérer les suggestions et s'assurer du bon fonctionnement du site

### Thème visuel
- Interface moderne et responsive
- Support du mode sombre/clair avec persistance des préférences

### Recherche
- Recherche d'auteurs
- Recherche de contenus dans les textes

### API
Une API REST est disponible pour accéder aux données de la base de données. Cela permet d'interagir avec les données sans passer par l'interface web. Sont fournis :
- Points d'accès pour auteurs et livres
- Points d'accès pour suggestions


## Technologies utilisées

- **Backend** : [PHP](https://www.php.net/)
- **Base de données** : [MySQL](https://www.mysql.com/) ([SQLite](https://www.sqlite.org/) pour le développement)
- **Frontend** : [HTML](https://developer.mozilla.org/fr/docs/Web/HTML), [CSS](https://developer.mozilla.org/fr/docs/Web/CSS), [JavaScript](https://developer.mozilla.org/fr/docs/Web/JavaScript)
- **Dépendances externes** : [Lucide Icons](https://lucide-icons.web.app/)

## Crédits

MAULNY Lilian, L1 Informatique, [Université de Tours](https://univ-tours.fr), antenne de Blois. Année 2024-2025.

## Licence

Ce projet est un projet universitaire et ne doit pas être utilisé à des fins commerciales.