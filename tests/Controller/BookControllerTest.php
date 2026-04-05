<?php

namespace App\Tests\Controller;

use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Language;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class BookControllerTest extends WebTestCase
{
    // ── Helpers ─────────────────────────────────────────────────────────────

    private function createAdmin(): User
    {
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $existing = $em->getRepository(User::class)->findOneBy(['email' => 'book_test_admin@example.com']);
        if ($existing) {
            return $existing;
        }

        $hasher = $container->get(UserPasswordHasherInterface::class);

        $admin = new User();
        $admin->setEmail('book_test_admin@example.com');
        $admin->setNom('Admin');
        $admin->setPrenom('Test');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setCreatedAt(new \DateTimeImmutable());
        $admin->setPassword($hasher->hashPassword($admin, 'Admin1234!'));

        $em->persist($admin);
        $em->flush();

        return $admin;
    }

    private function createBookFixtures(): array
    {
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $author = $em->getRepository(Author::class)->findOneBy(['nom' => 'Test', 'prenom' => 'Auteur'])
            ?? (function () use ($em) {
                $a = new Author();
                $a->setNom('Test');
                $a->setPrenom('Auteur');
                $a->setBibliographie('Auteur de test pour les tests unitaires.');
                $em->persist($a);
                return $a;
            })();

        $category = $em->getRepository(Category::class)->findOneBy(['label' => 'Test Category'])
            ?? (function () use ($em) {
                $c = new Category();
                $c->setLabel('Test Category');
                $em->persist($c);
                return $c;
            })();

        $language = $em->getRepository(Language::class)->findOneBy(['nom' => 'Test Langue'])
            ?? (function () use ($em) {
                $l = new Language();
                $l->setNom('Test Langue');
                $em->persist($l);
                return $l;
            })();

        $em->flush();

        return ['author' => $author, 'category' => $category, 'language' => $language];
    }

    // ── Tests ────────────────────────────────────────────────────────────────

    public function testAdminBooksPageRequiresLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/books');

        $this->assertResponseRedirects();
    }

    public function testAdminBooksPageLoadsForAdmin(): void
    {
        $client = static::createClient();
        $admin = $this->createAdmin();
        $this->createBookFixtures();

        $client->loginUser($admin);
        $client->request('GET', '/admin/books');

        $this->assertResponseIsSuccessful();
    }

    public function testCreateBookAsAdmin(): void
    {
        $client = static::createClient();
        $admin = $this->createAdmin();
        $fixtures = $this->createBookFixtures();

        $client->loginUser($admin);

        // Récupérer le token CSRF depuis la page
        $crawler = $client->request('GET', '/admin/books');
        $this->assertResponseIsSuccessful();

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $authorId   = $fixtures['author']->getId();
        $categoryId = $fixtures['category']->getId();
        $languageId = $fixtures['language']->getId();

        // Récupérer le token CSRF du formulaire (champ caché dans le DOM)
        $csrfToken = $crawler->filter('input[name="book[_token]"]')->attr('value');

        // Soumettre directement en POST (le formulaire est dans une modale)
        $client->request('POST', '/admin/books', [
            'book' => [
                'title'           => 'Le Test des Tests',
                'description'     => 'Un livre créé par un test automatisé.',
                'yearPublication' => 2024,
                'stock'           => 5,
                'quantity'        => 10,
                'isbn'            => '9780000000002',
                'author'          => $authorId,
                'categories'      => [$categoryId],
                'language'        => $languageId,
                '_token'          => $csrfToken,
            ],
        ]);

        // Le livre doit être créé et on doit être redirigé
        $this->assertResponseRedirects('/admin/books');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Vérifier que le livre existe bien en base
        $em->clear();
        $bookRepo = $em->getRepository(\App\Entity\Book::class);
        $book = $bookRepo->findOneBy(['title' => 'Le Test des Tests']);

        $this->assertNotNull($book, 'Le livre doit exister en base de données.');
        $this->assertSame('Le Test des Tests', $book->getTitle());
        $this->assertSame(5, $book->getStock());
        $this->assertSame(10, $book->getQuantity());
    }

    public function testCreateBookWithMissingTitleShowsError(): void
    {
        $client = static::createClient();
        $admin = $this->createAdmin();
        $fixtures = $this->createBookFixtures();

        $client->loginUser($admin);
        $crawler = $client->request('GET', '/admin/books');

        $authorId   = $fixtures['author']->getId();
        $categoryId = $fixtures['category']->getId();
        $languageId = $fixtures['language']->getId();
        $csrfToken  = $crawler->filter('input[name="book[_token]"]')->attr('value');

        $client->request('POST', '/admin/books', [
            'book' => [
                'title'           => '',  // Titre vide
                'description'     => 'Description sans titre.',
                'yearPublication' => 2024,
                'stock'           => 3,
                'quantity'        => 5,
                'author'          => $authorId,
                'categories'      => [$categoryId],
                'language'        => $languageId,
                '_token'          => $csrfToken,
            ],
        ]);

        // Le formulaire invalide doit rester sur la page (pas de redirection vers admin_books)
        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateBookForbiddenForRegularUser(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $user = $em->getRepository(User::class)->findOneBy(['email' => 'regularbook@example.com']);
        if (!$user) {
            $hasher = $container->get(UserPasswordHasherInterface::class);
            $user = new User();
            $user->setEmail('regularbook@example.com');
            $user->setNom('Regular');
            $user->setPrenom('User');
            $user->setRoles([]);
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setPassword($hasher->hashPassword($user, 'User1234!'));
            $em->persist($user);
            $em->flush();
        }

        $client->loginUser($user);
        $client->request('GET', '/admin/books');

        // Redirigé vers home via AccessDeniedHandler
        $this->assertResponseRedirects();
    }

    public function testBookCataloguePageIsPublic(): void
    {
        $client = static::createClient();

        // La page publique des livres doit être accessible sans connexion
        $client->request('GET', '/app_book_index');

        $this->assertResponseIsSuccessful();
    }
}
