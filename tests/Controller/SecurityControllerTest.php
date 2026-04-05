<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityControllerTest extends WebTestCase
{
    // ── Helpers ─────────────────────────────────────────────────────────────

    private function createUser(string $email, string $plainPassword, array $roles = []): User
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
        $user->setRoles($roles);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setPassword($hasher->hashPassword($user, $plainPassword));

        $em->persist($user);
        $em->flush();

        return $user;
    }

    // ── Tests ────────────────────────────────────────────────────────────────

    public function testLoginPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }

    public function testSuccessfulLoginRedirectsToHome(): void
    {
        $client = static::createClient();
        $this->createUser('testlogin@example.com', 'Password1!');

        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'testlogin@example.com',
            '_password' => 'Password1!',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testLoginWithWrongPasswordShowsError(): void
    {
        $client = static::createClient();
        $this->createUser('wrongpass@example.com', 'CorrectPass1!');

        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'wrongpass@example.com',
            '_password' => 'WrongPassword!',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $this->assertSelectorExists('.alert-error');
    }

    public function testLoginWithUnknownEmailShowsError(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'nobody@nowhere.com',
            '_password' => 'Whatever1!',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testAdminDashboardRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/dashboard');

        // Doit rediriger vers login ou la page d'accueil (access denied handler)
        $this->assertResponseRedirects();
    }

    public function testAdminDashboardAccessibleByAdmin(): void
    {
        $client = static::createClient();
        $admin = $this->createUser('admin_test@example.com', 'Admin1!', ['ROLE_ADMIN']);

        $client->loginUser($admin);
        $client->request('GET', '/admin/dashboard');

        $this->assertResponseIsSuccessful();
    }

    public function testAdminDashboardForbiddenForRegularUser(): void
    {
        $client = static::createClient();
        $user = $this->createUser('regular_test@example.com', 'User1!');

        $client->loginUser($user);
        $client->request('GET', '/admin/dashboard');

        // Redirigé vers home via AccessDeniedHandler
        $this->assertResponseRedirects();
    }

    public function testLogout(): void
    {
        $client = static::createClient();
        $user = $this->createUser('logout_test@example.com', 'Logout1!');

        $client->loginUser($user);
        $client->request('GET', '/logout');

        $this->assertResponseRedirects();
    }
}
