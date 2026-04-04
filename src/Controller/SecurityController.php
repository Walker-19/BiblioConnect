<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Comment;
use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }


    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function adminDashboard(EntityManagerInterface $em): Response
    {
        $stats = [
            'totalBooks' => $em->getRepository(Book::class)->count([]),
            'totalUsers' => $em->getRepository(User::class)->count([]),
            'totalComments' => $em->getRepository(Comment::class)->count([]),
        ];

        $lastReservations = $em->getRepository(Reservation::class)->findBy([], ['createdAt' => 'DESC'], 5);

        $lastComments = $em->getRepository(Comment::class)->findBy(['status' => 'en attente'], ['createdAt' => 'DESC'], 5);

        return $this->render('dashboard/admin/index.html.twig', [
            'stats' => $stats,
            'lastReservations' => $lastReservations,
            'lastComments' => $lastComments,
        ]);
    }

    #[Route(path: '/librarian/dashboard', name: 'librarian_dashboard')]
    public function librarianDashboard(EntityManagerInterface $em): Response
    {
          $stats = [
            'totalBooks' => $em->getRepository(Book::class)->count([]),
            'totalUsers' => $em->getRepository(User::class)->count([]),
            'totalComments' => $em->getRepository(Comment::class)->count([]),
        ];

        $lastReservations = $em->getRepository(Reservation::class)->findBy([], ['createdAt' => 'DESC'], 5);

        $lastComments = $em->getRepository(Comment::class)->findBy(['status' => 'en attente'], ['createdAt' => 'DESC'], 5);

        return $this->render('dashboard/librarian/index.html.twig', [
            'stats' => $stats,
            'lastReservations' => $lastReservations,
            'lastComments' => $lastComments,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
