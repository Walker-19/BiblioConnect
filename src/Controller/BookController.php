<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Author;
use App\Entity\Language;
use App\Entity\Category;
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

final class BookController extends AbstractController
{
    #[Route('/book', name: 'app_book')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }

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

    #[Route('/admin/author/add', name: 'admin_author_add', methods: ['POST'])]
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

    #[Route('/admin/language/add', name: 'admin_language_add', methods: ['POST'])]
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

    #[Route('/admin/category/add', name: 'admin_category_add', methods: ['POST'])]
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
}

