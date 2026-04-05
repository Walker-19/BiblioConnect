<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Favorite;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class FavoriteController extends AbstractController
{
    #[Route('/favorite/toggle/{id}', name: 'app_favorite_toggle')]
    public function toggle(Book $book, FavoriteRepository $favoriteRepo, EntityManagerInterface $em): JsonResponse
    {
        $user     = $this->getUser();
        $existing = $favoriteRepo->findOneByUserAndBook($user, $book);

        if ($existing) {
            $em->remove($existing);
            $em->flush();
            return new JsonResponse(['status' => 'removed']);
        }

        $favorite = new Favorite();
        $favorite->setUser($user);
        $favorite->setBook($book);

        $em->persist($favorite);
        $em->flush();

        return new JsonResponse(['status' => 'added']);
    }
}
