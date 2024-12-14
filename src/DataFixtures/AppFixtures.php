<?php

namespace App\DataFixtures;

use App\Entity\Arbitre;
use App\Entity\Equipe;
use App\Entity\Joueur;
use App\Entity\League; // Assurez-vous que cette entité existe
use App\Entity\Officiel;
use App\Entity\Saison;
use App\Enum\JoueurPost;
use App\Enum\OfficielRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('en_US');
        // Création d'une ligue
        $league = new League();
        $league->setDisplayName('Premier League'); // Nom de la ligue
        $league->setCountry('Angleterre'); // Pays de la ligue
        $league->setDateCreation(new \DateTime('1992-02-20')); // Date de création

        // Sauvegarde dans la base de données
        $manager->persist($league);


        // Création de la saison 2024-2025
        $saison = new Saison();
        $saison->setDebut(new \DateTime('2024-08-01')); // Date de début
        $saison->setFin(new \DateTime('2025-05-31'));  // Date de fin
        $saison->setNbEquipe(20); // Exemple : 20 équipes
        $saison->setNbArbitre(25); // Exemple : 25 arbitres
        $saison->setNbRemplacement(5);
        $saison->setLeague($league); // Association avec la ligue

        // Sauvegarde de la saison
        $manager->persist($saison);


        // Création de 18 équipes
        for ($i = 0; $i < 18; $i++) {
            $equipe = new Equipe();
            $equipe->setNom($faker->company); // Nom aléatoire de l'équipe
            $equipe->setVille($faker->city); // Ville aléatoire
            $equipe->setDateCreation($faker->dateTimeBetween('-50 years', 'now')); // Date de création aléatoire

            // Persister l'équipe
            $manager->persist($equipe);
        }


        // Création de 500 joueurs
        for ($i = 0; $i < 500; $i++) {
            $joueur = new Joueur();
            $joueur->setNom($faker->lastName);         // Nom aléatoire
            $joueur->setPrenom($faker->firstName);     // Prénom aléatoire
            $joueur->setDateNaissance($faker->dateTimeBetween('-30 years', '-18 years')); // Date de naissance entre 18 et 30 ans

            // Poste aléatoire entre DEFENSSEUR, MILIEU, ATTAQUANT, GOAL
            $randomPost = $faker->randomElement([JoueurPost::DEFENSSEUR, JoueurPost::MILIEU, JoueurPost::ATTAQUANT, JoueurPost::GOAL]);
            $joueur->setPost($randomPost);

            // Persister l'entité Joueur
            $manager->persist($joueur);
        }


        // Création de 100 arbitres
        for ($i = 0; $i < 100; $i++) {
            $arbitre = new Arbitre();
            $arbitre->setNom($faker->name); // Nom aléatoire pour chaque arbitre

            // Persister l'arbitre
            $manager->persist($arbitre);
        }


        // Création de 250 officiels
        for ($i = 0; $i < 250; $i++) {
            $officiel = new Officiel();
            $officiel->setNom($faker->lastName);       // Nom aléatoire
            $officiel->setPrenom($faker->firstName);   // Prénom aléatoire

            // Rôle aléatoire entre COACH et STAFF
            $randomRole = $faker->randomElement([OfficielRole::COACH, OfficielRole::STAFF]);
            $officiel->setRole($randomRole);

            // Persister l'officiel
            $manager->persist($officiel);
        }

        // Appliquer les changements
        $manager->flush();
    }
}
