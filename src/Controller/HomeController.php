<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Category;
use App\Entity\Rating;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $em): Response
    {
        $categories = $em->getRepository(Category::class)->findAll();
        $books = $em->getRepository(Book::class)->findBy([], ['createdAt' => 'DESC'], 6);
        $ratingRepo = $em->getRepository(Rating::class);
        foreach ($books as $book)
            {
                $average = $ratingRepo->getAverageRating($book);
                $book->setAverageRating($average);
            }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'categories' => $categories,
            'books' => $books,
        ]);
        
    }

    #[Route('/profil', name: 'app_profile')]
    public function profile(): Response
    {
        return $this->render('home/profile.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/favorites', name: 'app_favorites')]
    public function favorites(): Response
    {
        return $this->render('home/favorites.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/app_book_index', name: 'app_book_index')]
    public function bookIndex(): Response
    {
        return $this->render('home/book_index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/show_book/{id}', name: 'app_book_show')]
    public function bookShow(Book $book): Response
    {
        return $this->render('home/book_show.html.twig', [
            'controller_name' => 'HomeController',
            'book' => $book,
        ]); 

    }
}
