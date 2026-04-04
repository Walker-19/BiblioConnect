<?php

namespace App\Controller;

use App\Entity\Language;
use App\Form\LanguageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LanguageController extends AbstractController
{
    #[Route('/admin/languages', name: 'admin_languages')]
    public function adminLanguages(Request $request, EntityManagerInterface $em): Response 
    {
        $languages = $em->getRepository(Language::class)->findAll();
        
        // Formulaire Langue
        $language = new Language();
        $form = $this->createForm(LanguageType::class, $language);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($language);
            $em->flush();
            return new JsonResponse(['success' => true]);
        }
        
        // Formulaires préremplis pour les modals
        $languageForm = $this->createForm(LanguageType::class, new Language());
        
        return $this->render('dashboard/language/index.html.twig', [
            'languages' => $languages,
            'languageForm' => $languageForm,
        ]);
    }

    #[Route('/admin/language/{id}/edit', name: 'admin_language_edit', methods: ['GET', 'POST'])]
    public function editLanguage(Language $language, Request $request, EntityManagerInterface $em): Response 
    {
        $form = $this->createForm(LanguageType::class, $language);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Langue mise à jour avec succès !');
            return $this->redirectToRoute('admin_languages');
        }
        
        return $this->render('dashboard/language/edit.html.twig', [
            'form' => $form,
            'language' => $language,
        ]);
    }

    #[Route('/admin/language/{id}/delete', name: 'admin_language_delete', methods: ['POST'])]
    public function deleteLanguage(Language $language, Request $request, EntityManagerInterface $em): Response 
    {
        if ($this->isCsrfTokenValid('delete' . $language->getId(), $request->request->get('_token'))) {
            $em->remove($language);
            $em->flush();
            $this->addFlash('success', 'Langue supprimée avec succès !');
        }
        
        return $this->redirectToRoute('admin_languages');
    }
}
