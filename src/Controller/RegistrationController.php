<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationController extends AbstractController
{
#[Route('/registration', name: 'app_registration')]
public function index(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
{
    $user = new User();
    $form = $this->createForm(RegisterType::class, $user);
    $form->handleRequest($request);

    
    if ($form->isSubmitted() && $form->isValid()) {
        $plainPassword = $form->get('password')->get('first')->getData();
        $user->setRoles(['ROLE_USER']);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                $plainPassword
            )
        );
        $plainPassword = null;
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('app_login');
    }

    return $this->render('registration/index.html.twig', [
        'form' => $form,
    ]);
}
}
