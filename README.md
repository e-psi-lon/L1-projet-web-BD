# Projet L1 Web-BD - Corpus Digitale

Ce dépôt contient le code source du projet de web et base de données de première année de licence. Il s'agit d'une application web permettant d'explorer une bibliothèque de textes classiques de l'Antiquité latine.

## Lancement

Ce projet utilise la fonctionnalité de PHP permettant de définir un routeur.
Pour lancer le projet, il faut donc démarrer le serveur en précisant d'utiliser le fichier router.php comme routeur. Ci-dessous, vous trouverez comment lancer le serveur correctement selon diverses méthodes :

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
6. Accédez au site via l'URL: `http://localhost`
  - Il est nécessaire pour le bon fonctionnement des routes de mettre le projet à la racine web: [`http://localhost/`](http://localhost/)

## Structure

Le projet est divisé en plusieurs dossiers pour une meilleure organisation :

- **[account](/account/)** : Gestion des comptes utilisateurs
  - Inscription, connexion, déconnexion
- **[admin](/admin/)** : Interface d'administration
- **[api](/api/)** : API REST pour accéder et modifier certaines données (interactivité avec le frontend)
  -  **[`suggestions`](/api/suggestions/)** : Endpoints API spécifiques aux suggestions
- **[assets](/assets/)** : Contient les ressources statiques
  - **[`/css`](/assets/css/)** : Feuilles de style CSS
  - **[`/js`](/assets/js/)** : Scripts JavaScript
- **[content](/content/)** : Contient les pages de contenu
  - **[`suggestions`](/content/suggestions/)** : Pages relatives aux suggestions de contenu
- **[database](/database/)** : Contient le schéma et les données (sous forme de fichiers SQL)
- **[errors](/errors/)** : Pages d'erreur personnalisées
- **[includes](/includes/)** : Composants réutilisables (header, footer, utilitaires)
- **[research](/research/)** : Pages de recherche parmi les auteurs, œuvres et les textes 

## Base de données

Le projet utilise MySQL comme système de gestion de base de données. Le schéma est défini dans `schema.sql` et comprend les tables suivantes :

### Tables

| Table                     | Description                            | Champs principaux                                                                          |
|---------------------------|----------------------------------------|--------------------------------------------------------------------------------------------|
| **`authors`**             | Informations sur les auteurs           | `author_id`, `name`, `url_name`, `birth_year`, `death_year`, `biography`                   |
| **`books`**               | Ouvrages des auteurs                   | `book_id`, `author_id`, `title`, `url_title`, `publication_year`, `description`            |
| **`chapters`**            | Chapitres des livres                   | `chapter_id`, `book_id`, `title`, `chapter_number`, `content`                              |
| **`users`**               | Gestion des utilisateurs               | `user_id`, `username`, `email`, `password`, `is_admin`                                     |
| **`suggestions`**         | Suggestions de contenu (infos de base) | `suggestion_id`, `user_id`, `suggestion_type`, `status`, `admin_notes`, `reviewed_by`      |
| **`author_suggestions`**  | Suggestions d'auteurs                  | `suggestion_id`, `author_name`, `author_url_name`, `birth_year`, `death_year`, `biography` |
| **`book_suggestions`**    | Suggestions de livres                  | `suggestion_id`, `author_id`, `title`, `url_title`, `publication_year`, `description`      |
| **`chapter_suggestions`** | Suggestions de chapitres               | `suggestion_id`, `book_id`, `title`, `chapter_number`, `content`                           |

### Relations

Le schéma de données s'articule autour des relations suivantes :

- Un **auteur** (`authors`) peut avoir écrit plusieurs **livres** (`books`) *(relation 1,1 - 0,N)*
- Un **livre** (`books`) contient plusieurs **chapitres** (`chapters`) *(relation 1,1 - 0,N)*
- Un **utilisateur** (`users`) peut soumettre plusieurs **suggestions** (`suggestions`) *(relation 1,1 - 0,N)*
- Une **suggestion** (`suggestions`) est liée à un type spécifique de suggestion (`author_suggestions`, `book_suggestions`, ou `chapter_suggestions`) *(relation 1,1 - 0,1)*
- Les suggestions de livres (`book_suggestions`) sont liées à des **auteurs** existants
- Les suggestions de chapitres (`chapter_suggestions`) sont liées à des **livres** existants

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
Une API REST est disponible pour accéder aux données de la base de données. Cela permet d'interagir avec les données autrement que via le PHP interne aux pages
permettant une meilleure dynamique et interactivité sur les pages. Voici quelques exemples de routes disponibles :
- Routes pour les auteurs et livres
- Routes pour les suggestions
  - Ajout, modification, approbation, refus et examen des suggestions
- Route dédiée à la recherche (interactivité sans recharger la page)

### Schéma de navigation

```
Accueil
├── Recherche
├── Auteurs (recherche par nom)
│   └── Page auteur
│       └── Page livre
│           └── Page chapitre
├── Connexion/Inscription
├── [Si connecté]
│   ├── Mon profil
│   ├── Suggestions
│   │   ├── Nouvelle suggestion
│   │   ├── Mes suggestions
│   │   └── Détails d'une suggestion
│   │       └── Modification d'une suggestion
│   └── [Si administrateur]
│       ├── Tableau de bord admin
│       ├── Gestion des suggestions
│       └── Gestion des utilisateurs
└── Mode sombre/clair (disponible sur toutes les pages)

API
├── GET /api/authors - Obtenir ou rechercher des auteurs
├── GET /api/books - Obtenir ou rechercher des livres 
├── GET /api/search - Recherche globale
├── GET /api/suggestion-details - Détails d'une suggestion
└── /api/suggestions - Routes spécifiques aux suggestions
    ├── POST /api/suggestions/suggest - Soumettre une suggestion
    ├── POST /api/suggestions/edit - Modifier une suggestion
    ├── POST /api/suggestions/approve - Approuver une suggestion (admin)
    ├── POST /api/suggestions/reject - Rejeter une suggestion (admin)
    └── POST /api/suggestions/update-notes - Mettre à jour les notes concernant une suggestion (admin)
```
## Technologies utilisées

- **Backend** : [PHP](https://www.php.net/)
- **Base de données** : [MySQL](https://www.mysql.com/) ([SQLite](https://www.sqlite.org/) pour le développement)
- **Frontend** : [HTML](https://developer.mozilla.org/fr/docs/Web/HTML), [CSS](https://developer.mozilla.org/fr/docs/Web/CSS), [JavaScript](https://developer.mozilla.org/fr/docs/Web/JavaScript)
- **Dépendances externes** : [Lucide Icons](https://lucide-icons.web.app/)

## Crédits

MAULNY Lilian, L1 Informatique, [Université de Tours](https://univ-tours.fr), antenne de Blois. Année 2024-2025.
- [Lucide Icons](https://lucide-icons.web.app/) pour les magnifiques icônes utilisées dans le projet.
## Licence

Ce projet est un projet universitaire et ne doit pas être utilisé à des fins commerciales.
