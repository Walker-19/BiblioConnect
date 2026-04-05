<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Author;
use App\Entity\Comment;
use App\Entity\Language;
use App\Entity\Category;
use App\Entity\Reservation;
use App\Form\BookType;
use App\Form\AuthorType;
use App\Form\LanguageType;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class BookController extends AbstractController
{
    #[Route('/book', name: 'app_book')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/catalogue', name: 'admin_catalogue')]
    public function adminCatalogue(EntityManagerInterface $em): Response
    {
        return $this->render('dashboard/catalogue/index.html.twig', [
            'books'      => $em->getRepository(Book::class)->findAll(),
            'categories' => $em->getRepository(Category::class)->findAll(),
            'languages'  => $em->getRepository(Language::class)->findAll(),
            'authors'    => $em->getRepository(Author::class)->findAll(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/books', name: 'admin_books')]
    public function adminBooks(Request $request, EntityManagerInterface $em): Response 
    {
        $listBooks = $em->getRepository(Book::class)->findAll();
        
        // Formulaire Livre
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if($imageFile) {
                $originalFileName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFileName);
                $newFileName = $safeFileName . '-' . uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move($this->getParameter('images_directory'), $newFileName);
                    $book->setImage($newFileName);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image : ' . $e->getMessage());
                }
            }
            $em->persist($book);    
            $em->flush();
            $this->addFlash('success', 'Livre ajouté avec succès !');
            return $this->redirectToRoute('admin_books');
        }

        // Formulaires pour Auteur, Langue, Catégorie
        $author = new Author();
        $authorForm = $this->createForm(AuthorType::class, $author);
        
        $language = new Language();
        $languageForm = $this->createForm(LanguageType::class, $language);
        
        $category = new Category();
        $categoryForm = $this->createForm(CategoryType::class, $category);

        return $this->render('dashboard/book/books.html.twig', [
            'listBooks' => $listBooks,
            'form' => $form,
            'authorForm' => $authorForm,
            'languageForm' => $languageForm,
            'categoryForm' => $categoryForm,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/author/add', name: 'admin_author_add')]
    public function addAuthor(Request $request, EntityManagerInterface $em): Response
    {
        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($author);
                $em->flush();
                
                if ($request->isXmlHttpRequest()) {
                    return $this->json(['success' => true, 'message' => 'Auteur ajouté avec succès !']);
                }
                
                $this->addFlash('success', 'Auteur ajouté avec succès !');
                return $this->redirectToRoute('admin_books');
            } catch (\Exception $e) {
                $errorMessage = 'Erreur lors de l\'enregistrement : ' . $e->getMessage();
                if ($request->isXmlHttpRequest()) {
                    return $this->json(['success' => false, 'message' => $errorMessage], 400);
                }
                $this->addFlash('error', $errorMessage);
            }
        }

        $errors = [];
        if ($form->isSubmitted()) {
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json(['success' => false, 'errors' => $errors], 400);
        }

        return $this->render('dashboard/book/books.html.twig', [
            'listBooks' => $em->getRepository(Book::class)->findAll(),
            'form' => $this->createForm(BookType::class, new Book()),
            'authorForm' => $form,
            'languageForm' => $this->createForm(LanguageType::class, new Language()),
            'categoryForm' => $this->createForm(CategoryType::class, new Category()),
            'formError' => true,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/language/add', name: 'admin_language_add')]
    public function addLanguage(Request $request, EntityManagerInterface $em): Response
    {
        $language = new Language();
        $form = $this->createForm(LanguageType::class, $language);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($language);
                $em->flush();
                
                if ($request->isXmlHttpRequest()) {
                    return $this->json(['success' => true, 'message' => 'Langue ajoutée avec succès !']);
                }
                
                $this->addFlash('success', 'Langue ajoutée avec succès !');
                return $this->redirectToRoute('admin_books');
            } catch (\Exception $e) {
                $errorMessage = 'Erreur lors de l\'enregistrement : ' . $e->getMessage();
                if ($request->isXmlHttpRequest()) {
                    return $this->json(['success' => false, 'message' => $errorMessage], 400);
                }
                $this->addFlash('error', $errorMessage);
            }
        }

        $errors = [];
        if ($form->isSubmitted()) {
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json(['success' => false, 'errors' => $errors], 400);
        }

        return $this->render('dashboard/book/books.html.twig', [
            'listBooks' => $em->getRepository(Book::class)->findAll(),
            'form' => $this->createForm(BookType::class, new Book()),
            'authorForm' => $this->createForm(AuthorType::class, new Author()),
            'languageForm' => $form,
            'categoryForm' => $this->createForm(CategoryType::class, new Category()),
            'formError' => true,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/category/add', name: 'admin_category_add')]
    public function addCategory(Request $request, EntityManagerInterface $em): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($category);
                $em->flush();
                
                if ($request->isXmlHttpRequest()) {
                    return $this->json(['success' => true, 'message' => 'Catégorie ajoutée avec succès !']);
                }
                
                $this->addFlash('success', 'Catégorie ajoutée avec succès !');
                return $this->redirectToRoute('admin_books');
            } catch (\Exception $e) {
                $errorMessage = 'Erreur lors de l\'enregistrement : ' . $e->getMessage();
                if ($request->isXmlHttpRequest()) {
                    return $this->json(['success' => false, 'message' => $errorMessage], 400);
                }
                $this->addFlash('error', $errorMessage);
            }
        }

        $errors = [];
        if ($form->isSubmitted()) {
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json(['success' => false, 'errors' => $errors], 400);
        }

        return $this->render('dashboard/book/books.html.twig', [
            'listBooks' => $em->getRepository(Book::class)->findAll(),
            'form' => $this->createForm(BookType::class, new Book()),
            'authorForm' => $this->createForm(AuthorType::class, new Author()),
            'languageForm' => $this->createForm(LanguageType::class, new Language()),
            'categoryForm' => $form,
            'formError' => true,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/book/{id}/edit', name: 'admin_book_edit')]
    public function editBook(Request $request, Book $book, EntityManagerInterface $em): Response
    {
        // Sauvegarder l'image originale
        $originalImage = $book->getImage();
        
        // Créer le formulaire avec l'image remise à null pour éviter l'erreur File/string
        $book->setImage(null);
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            
            if ($imageFile) {
                // Nouvelle image uploadée - supprimer l'ancienne si elle existe
                if ($originalImage) {
                    $oldImagePath = $this->getParameter('images_directory') . '/' . $originalImage;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                
                $originalFileName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFileName);
                $newFileName = $safeFileName . '-' . uniqid() . '.' . $imageFile->guessExtension();
                
                try {
                    $imageFile->move($this->getParameter('images_directory'), $newFileName);
                    $book->setImage($newFileName);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image : ' . $e->getMessage());
                    // Restaurer l'ancienne image
                    $book->setImage($originalImage);
                }
            } else {
                // Aucune nouvelle image - conserver l'originale
                $book->setImage($originalImage);
            }
            
            $em->persist($book);
            $em->flush();
            $this->addFlash('success', 'Livre modifié avec succès !');
            return $this->redirectToRoute('admin_books');
        }

        // Restaurer l'image pour l'affichage dans le template
        $book->setImage($originalImage);

        return $this->render('dashboard/book/edit_book.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/book/{id}', name: 'admin_book_show')]
    public function adminBookShow(Book $book, EntityManagerInterface $em): Response
    {
        $averageRating = $em->getRepository(Comment::class)->getAverageRattingByBook($book) ?? 0.0;
        $book->setAverageRating($averageRating);

        $comments = $em->getRepository(Comment::class)->findBy(
            ['book' => $book],
            ['createdAt' => 'DESC']
        );

        $reservations = $em->getRepository(Reservation::class)->findBy(
            ['book' => $book],
            ['createdAt' => 'DESC']
        );

        return $this->render('dashboard/book/show.html.twig', [
            'book'         => $book,
            'comments'     => $comments,
            'reservations' => $reservations,
            'avgRating'    => $averageRating,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/book/{id}/delete', name: 'admin_book_delete')]
    public function deleteBook(Book $book, EntityManagerInterface $em, Request $request): Response
    {
        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('delete' . $book->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('admin_books');
        }

        // Supprimer l'image si elle existe
        if ($book->getImage()) {
            $imagePath = $this->getParameter('images_directory') . '/' . $book->getImage();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $em->remove($book);
        $em->flush();

        $this->addFlash('success', 'Livre supprimé avec succès !');
        return $this->redirectToRoute('admin_books');
    }
    #[Route('/show_book/{id}', name: 'app_book_show')]
    public function bookShow(Book $book, EntityManagerInterface $em): Response
    {
        $reservations = $em->getRepository(Reservation::class)->findBy(['book' => $book, 'status' => Reservation::STATUS_APPROVED]);
       $averageRating = $em->getRepository(Comment::class)->getAverageRattingByBook($book) ?? 0.0;
        $book->setAverageRating($averageRating);
        $reservationList = [];
        foreach ($reservations as $reservation) {
            $reservationList[] = [
                'start' => $reservation->getDateDebut(),
                'end'   => $reservation->getDateFin()
            ];
        }

        $isFavorite  = false;
        $canComment  = false;
        $userComment = null;

        if ($this->getUser()) {
            $isFavorite = $em->getRepository(\App\Entity\Favorite::class)
                ->findOneBy(['user' => $this->getUser(), 'book' => $book]) !== null;

            // User can comment only if they have at least one completed reservation for this book
            $completedReservation = $em->getRepository(Reservation::class)->findOneBy([
                'book'   => $book,
                'user'   => $this->getUser(),
                'status' => Reservation::STATUS_COMPLETED,
            ]);
            $canComment = $completedReservation !== null;

            // Check if the user has already submitted a comment
            $userComment = $em->getRepository(Comment::class)->findOneBy([
                'book' => $book,
                'user' => $this->getUser(),
            ]);
        }

        // Fetch all published comments for this book
        $comments = $em->getRepository(Comment::class)->findBy(
            ['book' => $book, 'status' => 'published'],
            ['createdAt' => 'DESC']
        );

        return $this->render('home/book_show.html.twig', [
            'book'            => $book,
            'listReservation' => $reservationList,
            'isFavorite'      => $isFavorite,
            'canComment'      => $canComment,
            'userComment'     => $userComment,
            'comments'        => $comments,
            'averageRating'   => $averageRating,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/book/{id}/comment', name: 'app_book_comment')]
    public function addComment(Book $book, Request $request, EntityManagerInterface $em): Response
    {
        // Verify the user has a completed reservation for this book
        $completedReservation = $em->getRepository(Reservation::class)->findOneBy([
            'book'   => $book,
            'user'   => $this->getUser(),
            'status' => Reservation::STATUS_COMPLETED,
        ]);

        if (!$completedReservation) {
            $this->addFlash('error', 'Vous devez avoir terminé une réservation pour laisser un commentaire.');
            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        $note     = (int) $request->request->get('note', 0);
        $contents = trim($request->request->get('contents', ''));

        if ($note < 1 || $note > 5 || $contents === '') {
            $this->addFlash('error', 'La note (1-5) et le commentaire sont obligatoires.');
            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        // Upsert: update existing comment or create a new one
        $comment = $em->getRepository(Comment::class)->findOneBy([
            'book' => $book,
            'user' => $this->getUser(),
        ]);

        if (!$comment) {
            $comment = new Comment();
            $comment->setBook($book);
            $comment->setUser($this->getUser());
            $comment->setCreatedAt(new \DateTimeImmutable());
        }

        $comment->setNote($note);
        $comment->setContents($contents);
        $comment->setStatus('published');

        $em->persist($comment);
        $em->flush();

        $this->addFlash('success', 'Votre commentaire a été publié !');
        return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
    }
}

