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
use Symfony\Component\Security\Http\Attribute\IsGranted;
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


    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function adminDashboard(EntityManagerInterface $em): Response
    {
        $reservationRepo = $em->getRepository(Reservation::class);
        $bookRepo        = $em->getRepository(Book::class);
        $userRepo        = $em->getRepository(User::class);
        $commentRepo     = $em->getRepository(Comment::class);

        $stats = [
            'totalBooks'    => $bookRepo->count([]),
            'totalUsers'    => $userRepo->count([]),
            'totalComments' => $commentRepo->count([]),
            'totalReservations' => $reservationRepo->count([]),
            'pendingReservations'  => $reservationRepo->count(['status' => Reservation::STATUS_PENDING]),
            'approvedReservations' => $reservationRepo->count(['status' => Reservation::STATUS_APPROVED]),
            'overdueReservations'  => $reservationRepo->count(['status' => Reservation::STATUS_OVERDUE]),
            'pendingComments'      => $commentRepo->count(['status' => 'en attente']),
            'availableBooks'       => $bookRepo->createQueryBuilder('b')
                ->select('COUNT(b.id)')->where('b.stock > 0')
                ->getQuery()->getSingleScalarResult(),
        ];

        $lastReservations = $reservationRepo->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')->leftJoin('r.book', 'b')
            ->orderBy('r.createdAt', 'DESC')->setMaxResults(6)
            ->getQuery()->getResult();

        $lastComments = $commentRepo->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')->leftJoin('c.book', 'b')
            ->where("c.status = 'en attente'")->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(5)->getQuery()->getResult();

        $lastUsers = $userRepo->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC')->setMaxResults(5)
            ->getQuery()->getResult();

        $lowStockBooks = $bookRepo->createQueryBuilder('b')
            ->where('b.stock >= 0')->andWhere('b.stock <= 2')
            ->orderBy('b.stock', 'ASC')->setMaxResults(5)
            ->getQuery()->getResult();

        return $this->render('dashboard/admin/index.html.twig', [
            'stats'            => $stats,
            'lastReservations' => $lastReservations,
            'lastComments'     => $lastComments,
            'lastUsers'        => $lastUsers,
            'lowStockBooks'    => $lowStockBooks,
        ]);
    }

    #[IsGranted('ROLE_LIBRARIAN')]
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
