
## Projet : Gestion d'un Championnat de Football

Ce projet est une bases de données de **gestion d'un championnat de football**, incluant la gestion des joueurs, équipes, matchs, arbitres, saisons, et statistiques.

Nous avons décidé de choisir ce projet car, ayant chacun propoer un domaine d'application pour ce projet, nous avons estimé que ce domaine d'application pour plusieurs raisons :

* **Sujet compréhensible par un grand nombre de personne.**
* **Le niveau de difficulté (nous l'avons estimé à 3, 3 étant la plus grande).**
* **Les possibilités de fonctions et de triggers.**

Voici les deux autres domaines que nous avions en hypothèse :

* **Gestion d'une ferme de manière automatique.**
* **Gestion des films (a l'image de IMDB).**

### L'équipe est composé de 4 personnes

* **Zhang Claude**
* **Mbaye Amynata (Chef de projet)**
* **Trb (à changer) Walid**
* **Rianodji Dicard**

### L'objectif

L'objectif principal de ce projet est de créer une bases de données sur un domaine choisi (dans notre cas un championnat de football) et d'y inclure **au minimum 5 fonctions et 5 triggers**.

##### Fonctionnalités du modèle de données (étape de brainstorming)

* Le modèle offre un **suivi détaillé des statistiques individuelles de chaque joueur pour chaque match**. Il inclut des données telles que les duels gagnés, les passes réussies, les tirs, les fautes commises, et les cartons reçus.
* Le modèle **calcule automatiquement les classements des meilleurs buteurs et passeurs** en s’appuyant sur les événements enregistrés pendant les matchs (buts et passes décisives).
* Le modèle **enregistre et suit les sanctions disciplinaires pour chaque joueur**, notamment les cartons jaunes et rouges. Cela inclut l’impact de ces sanctions sur l’équipe, comme les suspensions liées à un carton rouge ou à une accumulation de cartons jaunes.
* En combinant les performances individuelles des joueurs avec les événements de match, le modèle **génère des statistiques détaillées par équipe**. Cela inclut des indicateurs comme le nombre de buts marqués, les fautes commises, et les tirs cadrés.
* Le modèle propose une méthode pour **identifier l’"Homme du match" en se basant sur les performances individuelles des joueurs**. Ce titre peut être attribué automatiquement ou manuellement selon des critères définis, comme les passes décisives, les buts, les duels gagnés, ou d’autres statistiques clés.

### Technologies utilisées

- **Symfony** : Framework PHP utilisé seulement pour faciliter la création/insértion/update des tables afin que nous ayons des tables **ISO**.
- **PostgreSQL** : Système de Gestion de Base de Données relationnel utilisé pour stocker et gérer les données du projet.
- **Git** : Système de contrôle de version pour le suivi des modifications du code source.

### Utilisation avec Symfony

Symfony **nécéssite certaines dépendances**, je vais ici seulement les lister, je vous laisserais par la suite les installer :

* **PHP**
* **Composer**
* **Symfony CLI**
* **PostgreSQL**

Au sein du fichier **`.env`** se trouvant a la racine, au sein de la variable **`DATABASE_URL`**, il faudra modifier les champs **le nom d'utilisateur et le mot de passe**, le nom de la base de données est football_league par défaut mais vous pouvez également la modifier.

Le **port par défaut pour PostgreSQL est 5432**, si vous l'avez changé veuillez le remplacer par le votre.

`DATABASE_URL="postgresql://username:password@127.0.0.1:5432/football_league"`


##### Commandes a effectué pour cloner la bdd

* **composer install** : installe les dépendances du projet.
* **php bin/console doctrine:database:create** : crée la bases de données.
* **php bin/console doctrine:schema:update --force** : crée automatiquement les tables en se basant sur les entités.
* **symfony console doctrine:fixtures:load** : Génère les insertions que nous avons défini.

### Utilisation avec PostgreSQL (en ligne de commande ou l'interface graphique)

Les différents scripts SQL sont au sein du **dossier SQL**, voici les étapes d'éxécutions :

* **createDatabase.sql**
* **functionsAndTrigger.sql**
