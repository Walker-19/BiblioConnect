<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/user', name: 'admin_user_')]
#[IsGranted('ROLE_ADMIN')]
final class UserController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();
        $users = $currentUser ? $em->getRepository(User::class)->findAll() : [];
        $users = array_filter($users, fn($u) => $u->getId() !== $currentUser->getId());
        
        return $this->render('/dashboard/user/index.html.twig', [
            'controller_name' => 'UserController',
            'users' => $users,
        ]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(User $user): Response
    {
        $currentUser = $this->getUser();
        
        // Prevent viewing own profile from here
        if ($user->getId() === $currentUser->getId()) {
            $this->addFlash('warning', 'Vous ne pouvez pas consulter votre propre profil ici.');
            return $this->redirectToRoute('admin_user_index');
        }

        // Get reservations ordered by most recent first
        $reservations = $user->getReservations()->toArray();
        usort($reservations, fn($a, $b) => $b->getCreatedAt() <=> $a->getCreatedAt());

        return $this->render('/dashboard/user/show.html.twig', [
            'user' => $user,
            'reservations' => $reservations,
        ]);
    }

    #[Route('/{id}/roles', name: 'update_roles')]
    public function updateRoles(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();

        // Prevent updating own roles
        if ($user->getId() === $currentUser->getId()) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier vos propres rôles.');
            return $this->redirectToRoute('admin_user_show', ['id' => $user->getId()]);
        }

        $selectedRole = $request->request->get('role');
        
        // Set only one role (ROLE_USER is always added automatically)
        if ($selectedRole && in_array($selectedRole, ['ROLE_ADMIN', 'ROLE_LIBRARIAN'])) {
            $user->setRoles([$selectedRole]);
        } else {
            $user->setRoles([]);
        }
        
        $em->flush();

        $this->addFlash('success', 'Le rôle de l\'utilisateur a été mis à jour.');
        return $this->redirectToRoute('admin_user_show', ['id' => $user->getId()]);
    }
    
}
