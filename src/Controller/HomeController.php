<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Category;
use App\Entity\Rating;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('home/profile.html.twig');
    }

    #[Route('/favorites', name: 'app_favorites')]
    public function favorites(): Response
    {
        return $this->render('home/favorites.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/app_book_index', name: 'app_book_index')]
    public function bookIndex(Request $request, EntityManagerInterface $em): Response
    {
        $q        = $request->query->get('q', '');
        $catId    = $request->query->get('category');
        $sort     = $request->query->get('sort', 'newest');

        $categories = $em->getRepository(Category::class)->findAll();

        $qb = $em->getRepository(Book::class)->createQueryBuilder('b')
            ->leftJoin('b.author', 'a')
            ->leftJoin('b.categories', 'c');

        if ($q) {
            $qb->andWhere('b.title LIKE :q OR a.nom LIKE :q OR a.prenom LIKE :q')
               ->setParameter('q', '%' . $q . '%');
        }

        if ($catId) {
            $qb->andWhere('c.id = :cat')->setParameter('cat', $catId);
        }

        match ($sort) {
            'title'  => $qb->orderBy('b.title', 'ASC'),
            'oldest' => $qb->orderBy('b.createdAt', 'ASC'),
            default  => $qb->orderBy('b.createdAt', 'DESC'),
        };

        $books = $qb->getQuery()->getResult();

        $ratingRepo = $em->getRepository(Rating::class);
        foreach ($books as $book) {
            $book->setAverageRating($ratingRepo->getAverageRating($book));
        }

        return $this->render('home/book_index.html.twig', [
            'books'      => $books,
            'categories' => $categories,
            'q'          => $q,
            'catId'      => $catId,
            'sort'       => $sort,
        ]);
    }


}
