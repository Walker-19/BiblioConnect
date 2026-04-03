<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Language;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $categories = [
            'Roman', 'Science-Fiction', 'Fantasy', 'Policier',
            'Thriller', 'Biographie', 'Histoire', 'Philosophie',
            'Sciences', 'Développement personnel', 'Jeunesse',
            'Bande dessinée', 'Poésie', 'Classique', 'Aventure',
        ];

        $categoriesEntities = [];
        foreach ($categories as $nom) {
            $categorie = new Category();
            $categorie->setLabel($nom);
            $manager->persist($categorie);
            $categoriesEntities[] = $categorie;
        }

        // ── LANGUES ──────────────────────────────────
        $langues = ['Français', 'Anglais', 'Espagnol', 'Allemand', 'Italien'];

        foreach ($langues as $nom) {
            $langue = new Language();
            $langue->setNom($nom);
            $manager->persist($langue);
        }

        // ── AUTEURS ──────────────────────────────────
        $auteurs = [
            ['nom' => 'Hugo',       'prenom' => 'Victor',      'bio' => 'Écrivain français du XIXe siècle, auteur des Misérables et Notre-Dame de Paris.'],
            ['nom' => 'Zola',       'prenom' => 'Émile',       'bio' => 'Chef de file du naturalisme français, auteur de la saga des Rougon-Macquart.'],
            ['nom' => 'Camus',      'prenom' => 'Albert',      'bio' => 'Philosophe et écrivain français, prix Nobel de littérature en 1957.'],
            ['nom' => 'Orwell',     'prenom' => 'George',      'bio' => 'Écrivain britannique, auteur de 1984 et La Ferme des animaux.'],
            ['nom' => 'Tolkien',    'prenom' => 'J.R.R.',      'bio' => 'Auteur britannique, créateur de la Terre du Milieu et du Seigneur des Anneaux.'],
            ['nom' => 'Herbert',    'prenom' => 'Frank',       'bio' => 'Auteur américain de science-fiction, célèbre pour le cycle Dune.'],
            ['nom' => 'Asimov',     'prenom' => 'Isaac',       'bio' => 'Auteur américain de science-fiction, père du cycle Fondation.'],
            ['nom' => 'Dumas',      'prenom' => 'Alexandre',   'bio' => 'Écrivain français du XIXe siècle, auteur des Trois Mousquetaires.'],
            ['nom' => 'Flaubert',   'prenom' => 'Gustave',     'bio' => 'Écrivain français, auteur de Madame Bovary.'],
            ['nom' => 'Voltaire',   'prenom' => 'François',    'bio' => 'Philosophe et écrivain français des Lumières, auteur de Candide.'],
            ['nom' => 'Balzac',     'prenom' => 'Honoré de',   'bio' => 'Écrivain français, auteur de La Comédie humaine.'],
            ['nom' => 'Stendhal',   'prenom' => 'Henri',       'bio' => 'Écrivain français, auteur du Rouge et le Noir.'],
            ['nom' => 'Christie',   'prenom' => 'Agatha',      'bio' => 'Reine du roman policier, créatrice d\'Hercule Poirot et Miss Marple.'],
            ['nom' => 'King',       'prenom' => 'Stephen',     'bio' => 'Maître du roman d\'horreur américain, auteur de Shining et It.'],
            ['nom' => 'Saint-Exupéry', 'prenom' => 'Antoine de', 'bio' => 'Écrivain et aviateur français, auteur du Petit Prince.'],
        ];

        foreach ($auteurs as $data) {
            $auteur = new Author();
            $auteur->setNom($data['nom']);
            $auteur->setPrenom($data['prenom']);
            $auteur->setBibliographie($data['bio']);
            $manager->persist($auteur);
        }


        $manager->flush();
    }
}
