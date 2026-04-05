<?php

namespace App\Controller;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class CommentController extends AbstractController
{
    #[Route('/admin/comments', name: 'admin_comments')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $statusFilter = $request->query->get('status', '');

        $qb = $em->getRepository(Comment::class)->createQueryBuilder('c')
            ->leftJoin('c.book', 'b')->addSelect('b')
            ->leftJoin('c.user', 'u')->addSelect('u')
            ->orderBy('c.createdAt', 'DESC');

        if ($statusFilter !== '') {
            $qb->andWhere('c.status = :status')->setParameter('status', $statusFilter);
        }

        $comments = $qb->getQuery()->getResult();

        // Count per status
        $counts = [];
        foreach (['published', 'pending', 'rejected'] as $s) {
            $counts[$s] = (int) $em->getRepository(Comment::class)
                ->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->where('c.status = :s')->setParameter('s', $s)
                ->getQuery()->getSingleScalarResult();
        }
        $counts['all'] = array_sum($counts);

        return $this->render('dashboard/comment/index.html.twig', [
            'comments'     => $comments,
            'statusFilter' => $statusFilter,
            'counts'       => $counts,
        ]);
    }

    #[Route('/admin/comments/{id}/status/{status}', name: 'admin_comment_status', methods: ['POST'])]
    public function changeStatus(Comment $comment, string $status, EntityManagerInterface $em): Response
    {
        $allowed = ['published', 'pending', 'rejected'];
        if (!in_array($status, $allowed, true)) {
            $this->addFlash('error', 'Statut invalide.');
            return $this->redirectToRoute('admin_comments');
        }

        $comment->setStatus($status);
        $em->flush();

        $labels = ['published' => 'publié', 'pending' => 'mis en attente', 'rejected' => 'rejeté'];
        $this->addFlash('success', 'Commentaire ' . $labels[$status] . ' avec succès.');
        return $this->redirectToRoute('admin_comments');
    }

    #[Route('/admin/comments/{id}/delete', name: 'admin_comment_delete', methods: ['POST'])]
    public function delete(Comment $comment, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_comment_' . $comment->getId(), $request->request->get('_token'))) {
            $em->remove($comment);
            $em->flush();
            $this->addFlash('success', 'Commentaire supprimé.');
        }

        return $this->redirectToRoute('admin_comments');
    }
}
