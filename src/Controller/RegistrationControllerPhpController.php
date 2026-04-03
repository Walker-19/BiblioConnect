<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationControllerPhpController extends AbstractController
{
    #[Route('/registration/controller/php', name: 'app_registration_controller_php')]
    public function index(): Response
    {
        return $this->render('registration_controller_php/index.html.twig', [
            'controller_name' => 'RegistrationControllerPhpController',
        ]);
    }
}
