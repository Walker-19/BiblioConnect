<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class CategoryController extends AbstractController
{
    #[Route('/admin/categories', name: 'admin_categories')]
    public function adminCategories(Request $request, EntityManagerInterface $em): Response 
    {
        $categories = $em->getRepository(Category::class)->findAll();
        
        // Formulaire Catégorie
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($category);
            $em->flush();
            return new JsonResponse(['success' => true]);
        }
        
        // Formulaires préremplis pour les modals
        $categoryForm = $this->createForm(CategoryType::class, new Category());
        
        return $this->render('dashboard/category/index.html.twig', [
            'categories' => $categories,
            'categoryForm' => $categoryForm,
        ]);
    }

    #[Route('/admin/category/{id}/edit', name: 'admin_category_edit')]
    public function editCategory(Category $category, Request $request, EntityManagerInterface $em): Response 
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Catégorie mise à jour avec succès !');
            return $this->redirectToRoute('admin_categories');
        }
        
        return $this->render('dashboard/category/edit.html.twig', [
            'form' => $form,
            'category' => $category,
        ]);
    }

    #[Route('/admin/category/{id}/delete', name: 'admin_category_delete')]
    public function deleteCategory(Category $category, Request $request, EntityManagerInterface $em): Response 
    {
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            $em->remove($category);
            $em->flush();
            $this->addFlash('success', 'Catégorie supprimée avec succès !');
        }
        
        return $this->redirectToRoute('admin_categories');
    }
}
