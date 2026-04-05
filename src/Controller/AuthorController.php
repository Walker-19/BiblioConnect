<?php

namespace App\Controller;

use App\Entity\Author;
use App\Form\AuthorType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AuthorController extends AbstractController
{
    #[Route('/author', name: 'admin_author')]
    public function index(EntityManagerInterface $em): Response
    {   
        $authors = $em->getRepository(Author::class)->findAll();
        $form = $this->createForm(AuthorType::class);
        return $this->render('dashboard/author/index.html.twig', [
            'controller_name' => 'AuthorController',
            'authors' => $authors,
            'authorForm' => $form,
        ]);
    }

    #[Route('/author/{id}/edit', name: 'admin_author_edit')]
    public function edit(Author $author, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Auteur mis à jour avec succès.');
            return $this->redirectToRoute('admin_author');
        }

        return $this->render('dashboard/author/edit.html.twig', [
            'author' => $author,
            'form'   => $form,
        ]);
    }

    #[Route('/author/delete/{id}', name: 'admin_author_delete')]
    public function delete(Author $author, EntityManagerInterface $em): Response
    {
        $em->remove($author);
        $em->flush();

        return $this->redirectToRoute('admin_author');
    }
}
