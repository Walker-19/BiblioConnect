<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Favorite;
use App\Entity\Language;
use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        // ── CATÉGORIES ──────────────────────────────
        $categoriesData = [
            'Roman', 'Science-Fiction', 'Fantasy', 'Policier',
            'Thriller', 'Biographie', 'Histoire', 'Philosophie',
            'Sciences', 'Développement personnel', 'Jeunesse',
            'Bande dessinée', 'Poésie', 'Classique', 'Aventure',
        ];

        $categoriesEntities = [];
        foreach ($categoriesData as $nom) {
            $cat = new Category();
            $cat->setLabel($nom);
            $manager->persist($cat);
            $categoriesEntities[$nom] = $cat;
        }

        // ── LANGUES ──────────────────────────────────
        $languesData = ['Français', 'Anglais', 'Espagnol', 'Allemand', 'Italien'];
        $languesEntities = [];
        foreach ($languesData as $nom) {
            $langue = new Language();
            $langue->setNom($nom);
            $manager->persist($langue);
            $languesEntities[$nom] = $langue;
        }

        // ── AUTEURS ──────────────────────────────────
        $auteursData = [
            ['nom' => 'Hugo',          'prenom' => 'Victor',       'bio' => 'Écrivain français du XIXe siècle, auteur des Misérables et Notre-Dame de Paris.'],
            ['nom' => 'Zola',          'prenom' => 'Émile',        'bio' => 'Chef de file du naturalisme français, auteur de la saga des Rougon-Macquart.'],
            ['nom' => 'Camus',         'prenom' => 'Albert',       'bio' => 'Philosophe et écrivain français, prix Nobel de littérature en 1957.'],
            ['nom' => 'Orwell',        'prenom' => 'George',       'bio' => 'Écrivain britannique, auteur de 1984 et La Ferme des animaux.'],
            ['nom' => 'Tolkien',       'prenom' => 'J.R.R.',       'bio' => 'Auteur britannique, créateur de la Terre du Milieu et du Seigneur des Anneaux.'],
            ['nom' => 'Herbert',       'prenom' => 'Frank',        'bio' => 'Auteur américain de science-fiction, célèbre pour le cycle Dune.'],
            ['nom' => 'Asimov',        'prenom' => 'Isaac',        'bio' => 'Auteur américain de science-fiction, père du cycle Fondation.'],
            ['nom' => 'Dumas',         'prenom' => 'Alexandre',    'bio' => 'Écrivain français du XIXe siècle, auteur des Trois Mousquetaires.'],
            ['nom' => 'Flaubert',      'prenom' => 'Gustave',      'bio' => 'Écrivain français, auteur de Madame Bovary.'],
            ['nom' => 'Voltaire',      'prenom' => 'François',     'bio' => 'Philosophe et écrivain français des Lumières, auteur de Candide.'],
            ['nom' => 'Balzac',        'prenom' => 'Honoré de',    'bio' => 'Écrivain français, auteur de La Comédie humaine.'],
            ['nom' => 'Stendhal',      'prenom' => 'Henri',        'bio' => 'Écrivain français, auteur du Rouge et le Noir.'],
            ['nom' => 'Christie',      'prenom' => 'Agatha',       'bio' => 'Reine du roman policier, créatrice d\'Hercule Poirot et Miss Marple.'],
            ['nom' => 'King',          'prenom' => 'Stephen',      'bio' => 'Maître du roman d\'horreur américain, auteur de Shining et It.'],
            ['nom' => 'Saint-Exupéry', 'prenom' => 'Antoine de',  'bio' => 'Écrivain et aviateur français, auteur du Petit Prince.'],
        ];

        $auteursEntities = [];
        foreach ($auteursData as $data) {
            $auteur = new Author();
            $auteur->setNom($data['nom']);
            $auteur->setPrenom($data['prenom']);
            $auteur->setBibliographie($data['bio']);
            $manager->persist($auteur);
            $auteursEntities[$data['nom']] = $auteur;
        }

        // ── UTILISATEURS ────────────────────────────
        $admin = new User();
        $admin->setEmail('admin@biblioconnect.fr');
        $admin->setNom('Admin');
        $admin->setPrenom('Super');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'Admin1234!'));
        $admin->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($admin);

        $librarian = new User();
        $librarian->setEmail('librarian@biblioconnect.fr');
        $librarian->setNom('Bibliothécaire');
        $librarian->setPrenom('Marie');
        $librarian->setRoles(['ROLE_LIBRARIAN']);
        $librarian->setPassword($this->hasher->hashPassword($librarian, 'Librarian1234!'));
        $librarian->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($librarian);

        $regularUsers = [];
        $usersData = [
            ['email' => 'alice@example.com',   'nom' => 'Dupont',   'prenom' => 'Alice'],
            ['email' => 'bob@example.com',     'nom' => 'Martin',   'prenom' => 'Bob'],
            ['email' => 'charlie@example.com', 'nom' => 'Durand',   'prenom' => 'Charlie'],
            ['email' => 'diana@example.com',   'nom' => 'Leroy',    'prenom' => 'Diana'],
            ['email' => 'etienne@example.com', 'nom' => 'Moreau',   'prenom' => 'Étienne'],
        ];
        foreach ($usersData as $ud) {
            $u = new User();
            $u->setEmail($ud['email']);
            $u->setNom($ud['nom']);
            $u->setPrenom($ud['prenom']);
            $u->setRoles(['ROLE_USER']);
            $u->setPassword($this->hasher->hashPassword($u, 'User1234!'));
            $u->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 365) . ' days'));
            $manager->persist($u);
            $regularUsers[] = $u;
        }

        // ── LIVRES ──────────────────────────────────
        $booksData = [
            ['title' => 'Les Misérables',             'author' => 'Hugo',          'lang' => 'Français', 'year' => 1862, 'cats' => ['Roman', 'Classique'],      'stock' => 5,  'qty' => 7,  'isbn' => '9782070409228', 'desc' => 'L\'histoire de Jean Valjean, un ancien forçat cherchant à se racheter dans la France du XIXe siècle.'],
            ['title' => 'Notre-Dame de Paris',         'author' => 'Hugo',          'lang' => 'Français', 'year' => 1831, 'cats' => ['Roman', 'Classique'],      'stock' => 3,  'qty' => 5,  'isbn' => '9782070360024', 'desc' => 'Le destin tragique de Quasimodo, sonneur de cloches de Notre-Dame de Paris.'],
            ['title' => 'Germinal',                    'author' => 'Zola',          'lang' => 'Français', 'year' => 1885, 'cats' => ['Roman', 'Classique'],      'stock' => 4,  'qty' => 6,  'isbn' => '9782070413119', 'desc' => 'La vie des mineurs du Nord de la France au XIXe siècle.'],
            ['title' => 'L\'Étranger',                 'author' => 'Camus',         'lang' => 'Français', 'year' => 1942, 'cats' => ['Roman', 'Philosophie'],    'stock' => 6,  'qty' => 8,  'isbn' => '9782070360024', 'desc' => 'Meursault, un homme indifférent à tout, commet un meurtre absurde sous le soleil algérien.'],
            ['title' => '1984',                        'author' => 'Orwell',        'lang' => 'Anglais',  'year' => 1949, 'cats' => ['Roman', 'Science-Fiction'], 'stock' => 7,  'qty' => 10, 'isbn' => '9780451524935', 'desc' => 'Dans un État totalitaire omniscient, Winston Smith tente de résister au Parti.'],
            ['title' => 'La Ferme des animaux',        'author' => 'Orwell',        'lang' => 'Anglais',  'year' => 1945, 'cats' => ['Roman', 'Classique'],      'stock' => 5,  'qty' => 7,  'isbn' => '9780451526342', 'desc' => 'Allégorie politique sur une ferme où les animaux se révoltent contre leur propriétaire.'],
            ['title' => 'Le Seigneur des Anneaux',     'author' => 'Tolkien',       'lang' => 'Anglais',  'year' => 1954, 'cats' => ['Fantasy', 'Aventure'],     'stock' => 4,  'qty' => 6,  'isbn' => '9780261103252', 'desc' => 'La quête de Frodon pour détruire l\'Anneau Unique dans les feux du Mont Doom.'],
            ['title' => 'Dune',                        'author' => 'Herbert',       'lang' => 'Anglais',  'year' => 1965, 'cats' => ['Science-Fiction'],          'stock' => 5,  'qty' => 8,  'isbn' => '9780441013593', 'desc' => 'Sur la planète désertique Arrakis, Paul Atréides devient le messie d\'un peuple opprimé.'],
            ['title' => 'Fondation',                   'author' => 'Asimov',        'lang' => 'Anglais',  'year' => 1951, 'cats' => ['Science-Fiction'],          'stock' => 3,  'qty' => 5,  'isbn' => '9780553293357', 'desc' => 'Hari Seldon développe la psychohistoire pour préserver la civilisation galactique.'],
            ['title' => 'Les Trois Mousquetaires',     'author' => 'Dumas',         'lang' => 'Français', 'year' => 1844, 'cats' => ['Aventure', 'Classique'],   'stock' => 6,  'qty' => 9,  'isbn' => '9782070360710', 'desc' => 'D\'Artagnan et ses compagnons Athos, Porthos et Aramis défendent l\'honneur de la reine.'],
            ['title' => 'Madame Bovary',               'author' => 'Flaubert',      'lang' => 'Français', 'year' => 1857, 'cats' => ['Roman', 'Classique'],      'stock' => 4,  'qty' => 6,  'isbn' => '9782070360055', 'desc' => 'Emma Bovary, épouse d\'un médecin de province, rêve d\'une vie romantique et s\'engage dans des liaisons dangereuses.'],
            ['title' => 'Candide',                     'author' => 'Voltaire',      'lang' => 'Français', 'year' => 1759, 'cats' => ['Philosophie', 'Classique'], 'stock' => 5,  'qty' => 7,  'isbn' => '9782070360628', 'desc' => 'Candide parcourt le monde avec son maître Pangloss, découvrant que tout ne va pas pour le mieux.'],
            ['title' => 'Le Père Goriot',              'author' => 'Balzac',        'lang' => 'Français', 'year' => 1835, 'cats' => ['Roman', 'Classique'],      'stock' => 3,  'qty' => 5,  'isbn' => '9782253004226', 'desc' => 'La déchéance du père Goriot, sacrifié par ses filles ambitieuses dans le Paris du XIXe siècle.'],
            ['title' => 'Le Rouge et le Noir',         'author' => 'Stendhal',      'lang' => 'Français', 'year' => 1830, 'cats' => ['Roman', 'Classique'],      'stock' => 4,  'qty' => 6,  'isbn' => '9782070360192', 'desc' => 'Julien Sorel, fils de charpentier ambitieux, gravit les échelons de la société par la séduction.'],
            ['title' => 'Le Crime de l\'Orient-Express','author' => 'Christie',    'lang' => 'Anglais',  'year' => 1934, 'cats' => ['Policier', 'Thriller'],     'stock' => 6,  'qty' => 9,  'isbn' => '9780007119318', 'desc' => 'Hercule Poirot enquête sur un meurtre dans le célèbre train bloqué par la neige.'],
            ['title' => 'Shining',                     'author' => 'King',          'lang' => 'Anglais',  'year' => 1977, 'cats' => ['Thriller'],                 'stock' => 5,  'qty' => 7,  'isbn' => '9780385121675', 'desc' => 'Jack Torrance sombre dans la folie en gardant un hôtel isolé avec sa famille.'],
            ['title' => 'Le Petit Prince',             'author' => 'Saint-Exupéry', 'lang' => 'Français', 'year' => 1943, 'cats' => ['Jeunesse', 'Classique'],   'stock' => 8,  'qty' => 12, 'isbn' => '9782070612758', 'desc' => 'Un aviateur rencontre un petit prince venu d\'une autre planète dans le désert du Sahara.'],
            ['title' => 'L\'Hobbit',                   'author' => 'Tolkien',       'lang' => 'Anglais',  'year' => 1937, 'cats' => ['Fantasy', 'Aventure'],     'stock' => 5,  'qty' => 8,  'isbn' => '9780261102217', 'desc' => 'Bilbo Sacquet part en aventure avec des nains et un magicien pour reconquérir un trésor gardé par un dragon.'],
            ['title' => 'La Peste',                    'author' => 'Camus',         'lang' => 'Français', 'year' => 1947, 'cats' => ['Roman', 'Philosophie'],    'stock' => 4,  'qty' => 6,  'isbn' => '9782070360253', 'desc' => 'Une ville d\'Algérie est dévastée par une épidémie. Chronique d\'une solidarité face à l\'absurde.'],
            ['title' => 'Vingt Mille Lieues sous les mers', 'author' => 'Dumas',    'lang' => 'Français', 'year' => 1870, 'cats' => ['Aventure', 'Sciences'],    'stock' => 3,  'qty' => 5,  'isbn' => '9782070413331', 'desc' => 'Le professeur Aronnax explore les fonds marins à bord du Nautilus du mystérieux capitaine Nemo.'],
        ];

        $books = [];
        foreach ($booksData as $bd) {
            $book = new Book();
            $book->setTitle($bd['title']);
            $book->setDescription($bd['desc']);
            $book->setYearPublication($bd['year']);
            $book->setStock($bd['stock']);
            $book->setQuantity($bd['qty']);
            $book->setIsbn($bd['isbn']);
            $book->setCreatedAt(new \DateTimeImmutable('-' . rand(30, 730) . ' days'));
            $book->setAuthor($auteursEntities[$bd['author']]);
            $book->setLanguage($languesEntities[$bd['lang']]);
            foreach ($bd['cats'] as $catName) {
                $book->addCategory($categoriesEntities[$catName]);
            }
            $manager->persist($book);
            $books[] = $book;
        }

        // ── COMMENTAIRES ────────────────────────────
        $commentsData = [
            ['book' => 0,  'user' => 0, 'content' => 'Un chef-d\'œuvre intemporel. L\'histoire de Jean Valjean m\'a profondément touché.',     'rating' => 5, 'status' => 'published'],
            ['book' => 0,  'user' => 1, 'content' => 'Un roman fleuve mais absolument magnifique. La description de Paris est époustouflante.', 'rating' => 5, 'status' => 'published'],
            ['book' => 3,  'user' => 0, 'content' => 'Court mais percutant. Camus nous force à questionner le sens de nos actes.',               'rating' => 4, 'status' => 'published'],
            ['book' => 4,  'user' => 2, 'content' => '1984 est terriblement d\'actualité. Un livre à lire absolument.',                          'rating' => 5, 'status' => 'published'],
            ['book' => 4,  'user' => 3, 'content' => 'Glaçant et prophétique. Big Brother est partout.',                                        'rating' => 5, 'status' => 'published'],
            ['book' => 6,  'user' => 1, 'content' => 'Une odyssée fantastique. Tolkien a créé un monde d\'une richesse inégalée.',               'rating' => 5, 'status' => 'published'],
            ['book' => 16, 'user' => 2, 'content' => 'Mon livre préféré de tous les temps. Bilbo est inoubliable.',                             'rating' => 5, 'status' => 'published'],
            ['book' => 17, 'user' => 4, 'content' => 'Une métaphore puissante sur les épidémies et la solidarité humaine.',                     'rating' => 4, 'status' => 'published'],
            ['book' => 14, 'user' => 3, 'content' => 'Poirot est brillant ! Un suspense parfaitement construit.',                               'rating' => 4, 'status' => 'published'],
            ['book' => 9,  'user' => 4, 'content' => 'Classique de l\'aventure. D\'Artagnan est un héros attachant.',                           'rating' => 4, 'status' => 'published'],
            ['book' => 7,  'user' => 0, 'content' => 'Dune est une épopée extraordinaire. L\'écologie de la planète Arrakis est fascinante.',   'rating' => 5, 'status' => 'published'],
            ['book' => 8,  'user' => 1, 'content' => 'La psychohistoire est un concept génial. Asimov était visionnaire.',                      'rating' => 4, 'status' => 'published'],
        ];

        foreach ($commentsData as $cd) {
            $comment = new Comment();
            $comment->setContents($cd['content']);
            $comment->setNote($cd['rating']);
            $comment->setStatus($cd['status']);
            $comment->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 200) . ' days'));
            $comment->setBook($books[$cd['book']]);
            $comment->setUser($regularUsers[$cd['user']]);
            $manager->persist($comment);
        }

        // ── FAVORIS ─────────────────────────────────
        $favoritesData = [
            ['user' => 0, 'book' => 0],
            ['user' => 0, 'book' => 3],
            ['user' => 0, 'book' => 7],
            ['user' => 1, 'book' => 4],
            ['user' => 1, 'book' => 6],
            ['user' => 1, 'book' => 16],
            ['user' => 2, 'book' => 4],
            ['user' => 2, 'book' => 8],
            ['user' => 3, 'book' => 14],
            ['user' => 3, 'book' => 9],
            ['user' => 4, 'book' => 17],
            ['user' => 4, 'book' => 7],
        ];

        foreach ($favoritesData as $fd) {
            $fav = new Favorite();
            $fav->setUser($regularUsers[$fd['user']]);
            $fav->setBook($books[$fd['book']]);
            $manager->persist($fav);
        }

        // ── RÉSERVATIONS ────────────────────────────
        $reservationsData = [
            ['user' => 0, 'book' => 0,  'status' => Reservation::STATUS_APPROVED,  'start' => '-5 days',   'end' => '+9 days'],
            ['user' => 0, 'book' => 4,  'status' => Reservation::STATUS_COMPLETED,  'start' => '-30 days',  'end' => '-16 days'],
            ['user' => 1, 'book' => 6,  'status' => Reservation::STATUS_PENDING,    'start' => '+1 day',    'end' => '+15 days'],
            ['user' => 1, 'book' => 14, 'status' => Reservation::STATUS_APPROVED,   'start' => '-2 days',   'end' => '+12 days'],
            ['user' => 2, 'book' => 7,  'status' => Reservation::STATUS_REJECTED,   'start' => '-10 days',  'end' => '-1 day'],
            ['user' => 2, 'book' => 16, 'status' => Reservation::STATUS_PENDING,    'start' => '+2 days',   'end' => '+16 days'],
            ['user' => 3, 'book' => 9,  'status' => Reservation::STATUS_COMPLETED,  'start' => '-20 days',  'end' => '-6 days'],
            ['user' => 3, 'book' => 17, 'status' => Reservation::STATUS_OVERDUE,    'start' => '-15 days',  'end' => '-1 day'],
            ['user' => 4, 'book' => 3,  'status' => Reservation::STATUS_APPROVED,   'start' => '-3 days',   'end' => '+11 days'],
            ['user' => 4, 'book' => 8,  'status' => Reservation::STATUS_PENDING,    'start' => '+3 days',   'end' => '+17 days'],
        ];

        foreach ($reservationsData as $rd) {
            $reservation = new Reservation();
            $reservation->setUser($regularUsers[$rd['user']]);
            $reservation->setBook($books[$rd['book']]);
            $reservation->setStatus($rd['status']);
            $reservation->setDateDebut(new \DateTime($rd['start']));
            $reservation->setDateFin(new \DateTime($rd['end']));
            $reservation->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 10) . ' days'));
            $manager->persist($reservation);
        }

        $manager->flush();
    }
}
