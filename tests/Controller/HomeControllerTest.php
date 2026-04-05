<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class HomeControllerTest extends WebTestCase
{
    // ── Helper ───────────────────────────────────────────────────────────────

    private function getOrCreateUser(string $email, string $password, array $roles = []): User
    {
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $existing = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existing) {
            return $existing;
        }

        $hasher = $container->get(UserPasswordHasherInterface::class);
        $user = new User();
        $user->setEmail($email);
        $user->setNom('Test');
        $user->setPrenom('User');
        $user->setRoles($roles ?: ['ROLE_USER']);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setPassword($hasher->hashPassword($user, $password));

        $em->persist($user);
        $em->flush();

        return $user;
    }

    // ── Tests page d'accueil ─────────────────────────────────────────────────

    public function testHomePageLoadsForGuest(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
    }

    public function testHomePageContainsLoginLinkForGuest(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('a[href*="login"], a[href*="registration"]');
    }

    public function testHomePageForLoggedInUser(): void
    {
        $client = static::createClient();
        $user = $this->getOrCreateUser('home_user@example.com', 'Test1234!');

        $client->loginUser($user);
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('a[href="/login"]');
    }

    public function testHomePageShowsBooks(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $bookCount = $em->getRepository(\App\Entity\Book::class)->count([]);

        if ($bookCount === 0) {
            $this->markTestSkipped('Aucun livre en base — les fixtures n\'ont pas été chargées.');
        }

        $this->assertSelectorExists('a[href*="show_book"], .book-card, .card');
    }

    // ── Tests catalogue livres ───────────────────────────────────────────────

    public function testBookIndexPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/app_book_index');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
    }

    public function testBookIndexShowsBooks(): void
    {
        $client = static::createClient();
        $client->request('GET', '/app_book_index');

        $this->assertResponseIsSuccessful();

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $bookCount = $em->getRepository(\App\Entity\Book::class)->count([]);

        if ($bookCount === 0) {
            $this->markTestSkipped('Aucun livre en base.');
        }

        $this->assertSelectorExists('a[href*="show_book"], .book-card, article, .card');
    }

    public function testBookIndexSearchReturnsResults(): void
    {
        $client = static::createClient();
        $client->request('GET', '/app_book_index', ['q' => '1984']);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('1984', $client->getResponse()->getContent());
    }

    public function testBookIndexSearchWithNoResultsReturnsPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/app_book_index', ['q' => 'zzzz_titre_inexistant_zzzz']);

        $this->assertResponseIsSuccessful();
    }

    public function testBookIndexSortByTitle(): void
    {
        $client = static::createClient();
        $client->request('GET', '/app_book_index', ['sort' => 'title']);

        $this->assertResponseIsSuccessful();
    }

    public function testBookIndexSortByOldest(): void
    {
        $client = static::createClient();
        $client->request('GET', '/app_book_index', ['sort' => 'oldest']);

        $this->assertResponseIsSuccessful();
    }

    public function testBookIndexFilterByCategory(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $cat = $em->getRepository(\App\Entity\Category::class)->findOneBy([]);
        if (!$cat) {
            $this->markTestSkipped('Aucune catégorie en base.');
        }

        $client->request('GET', '/app_book_index', ['category' => $cat->getId()]);

        $this->assertResponseIsSuccessful();
    }

    public function testBookDetailPageLoads(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $book = $em->getRepository(\App\Entity\Book::class)->findOneBy([]);
        if (!$book) {
            $this->markTestSkipped('Aucun livre en base.');
        }

        $client->request('GET', '/show_book/' . $book->getId());

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString($book->getTitle(), $client->getResponse()->getContent());
    }

    public function testBookDetailPageShowsAuthor(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $book = $em->getRepository(\App\Entity\Book::class)->createQueryBuilder('b')
            ->join('b.author', 'a')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        if (!$book) {
            $this->markTestSkipped('Aucun livre avec auteur en base.');
        }

        $client->request('GET', '/show_book/' . $book->getId());

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString($book->getAuthor()->getNom(), $client->getResponse()->getContent());
    }

    public function testBookDetailPageNotFoundReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/show_book/999999');

        $this->assertResponseStatusCodeSame(404);
    }
}
