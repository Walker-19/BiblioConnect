<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation')]
    public function index(): Response
    {
        return $this->render('reservation/index.html.twig', [
            'controller_name' => 'ReservationController',
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/reservation/{id}', name: 'app_reservation_create', methods: ['POST'])]
    public function createReservation(Book $book, Request $request, EntityManagerInterface $em): Response
    {
        $dateDebutStr = $request->request->get('dateDebut');
        $dateFinStr   = $request->request->get('dateFin');

        if (!$dateDebutStr || !$dateFinStr) {
            $this->addFlash('error', 'Veuillez sélectionner une période de réservation.');
            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        $dateDebut = \DateTime::createFromFormat('Y-m-d', $dateDebutStr);
        $dateFin   = \DateTime::createFromFormat('Y-m-d', $dateFinStr);

        if (!$dateDebut || !$dateFin || $dateDebut >= $dateFin) {
            $this->addFlash('error', 'Les dates saisies sont invalides.');
            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        $reservation = new Reservation();
        $reservation->setBook($book);
        $reservation->setUser($this->getUser());
        $reservation->setDateDebut($dateDebut);
        $reservation->setDateFin($dateFin);
        $reservation->setStatus(Reservation::STATUS_PENDING);
        $reservation->setCreatedAt(new \DateTimeImmutable());

        $em->persist($reservation);
        $em->flush();

        $this->addFlash('success', 'Votre réservation a bien été enregistrée et est en attente de validation.');
        return $this->redirectToRoute('app_book_index');
    }

    // ── Dashboard admin / bibliothécaire ────────────────────────────────────

    #[IsGranted('ROLE_LIBRARIAN')]
    #[Route('/admin/reservations', name: 'admin_reservations')]
    public function adminIndex(Request $request, EntityManagerInterface $em): Response
    {
        $statusFilter = $request->query->get('status', '');
        $qb = $em->getRepository(Reservation::class)
            ->createQueryBuilder('r')
            ->leftJoin('r.book', 'b')
            ->leftJoin('r.user', 'u')
            ->orderBy('u.nom', 'ASC')
            ->addOrderBy('r.createdAt', 'DESC');

        if ($statusFilter) {
            $qb->andWhere('r.status = :status')->setParameter('status', $statusFilter);
        }

        $reservations = $qb->getQuery()->getResult();

        // Grouper par utilisateur
        $grouped = [];
        foreach ($reservations as $resa) {
            $userId = $resa->getUser() ? $resa->getUser()->getId() : 0;
            $grouped[$userId]['user'] = $resa->getUser();
            $grouped[$userId]['reservations'][] = $resa;
        }

        $counts = [];
        foreach ([
            Reservation::STATUS_PENDING,
            Reservation::STATUS_APPROVED,
            Reservation::STATUS_REJECTED,
            Reservation::STATUS_CANCELED,
            Reservation::STATUS_COMPLETED,
            Reservation::STATUS_OVERDUE,
        ] as $s) {
            $counts[$s] = $em->getRepository(Reservation::class)->count(['status' => $s]);
        }

        return $this->render('dashboard/reservation/index.html.twig', [
            'grouped'       => $grouped,
            'statusFilter'  => $statusFilter,
            'counts'        => $counts,
        ]);
    }

    #[IsGranted('ROLE_LIBRARIAN')]
    #[Route('/admin/reservations/{id}/status/{status}', name: 'admin_reservation_status', methods: ['POST'])]
    public function changeStatus(Reservation $reservation, string $status, EntityManagerInterface $em): Response
    {
        $allowed = [
            Reservation::STATUS_APPROVED,
            Reservation::STATUS_REJECTED,
            Reservation::STATUS_CANCELED,
            Reservation::STATUS_COMPLETED,
            Reservation::STATUS_OVERDUE,
        ];

        if (!in_array($status, $allowed, true)) {
            $this->addFlash('error', 'Statut invalide.');
            return $this->redirectToRoute('admin_reservations');
        }

        $previous = $reservation->getStatus();
        $book     = $reservation->getBook();

        if ($book) {
            // Stock -1 uniquement quand on approuve (évite le double décrémentation)
            if ($status === Reservation::STATUS_APPROVED && $previous !== Reservation::STATUS_APPROVED) {
                $book->setStock(max(0, $book->getStock() - 1));
            }
            // Stock +1 uniquement quand le livre est retourné (terminé)
            elseif ($status === Reservation::STATUS_COMPLETED && $previous === Reservation::STATUS_APPROVED) {
                $book->setStock($book->getStock() + 1);
            }
            // Refus / annulation : pas de changement de stock
        }

        $reservation->setStatus($status);
        $em->flush();

        $labels = [
            Reservation::STATUS_APPROVED  => 'approuvée',
            Reservation::STATUS_REJECTED  => 'refusée',
            Reservation::STATUS_CANCELED  => 'annulée',
            Reservation::STATUS_COMPLETED => 'marquée terminée',
            Reservation::STATUS_OVERDUE   => 'marquée en retard',
        ];

        $this->addFlash('success', 'Réservation ' . ($labels[$status] ?? 'mise à jour') . ' avec succès.');
        return $this->redirectToRoute('admin_reservations');
    }
}
