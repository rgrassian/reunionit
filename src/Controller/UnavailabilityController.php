<?php

namespace App\Controller;

use App\Entity\Unavailability;
use App\Form\UnavailabilityAdminType;
use App\Form\UnavailabilityType;
use App\Repository\UnavailabilityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class UnavailabilityController extends AbstractController
{
    /**
     * Affiche l'historique de l'occupation des salles.
     * @Route("/admin/historique.html", name="unavailability_index", methods={"GET"})
     * @param UnavailabilityRepository $unavailabilityRepository
     * @return Response
     */
    public function index(UnavailabilityRepository $unavailabilityRepository): Response
    {
        return $this->render('unavailability/index.html.twig', ['unavailabilities' => $unavailabilityRepository->findAll()]);
    }

    /**
     * Permet de créer une nouvelle réservation.
     * @Route("/nouvelle-reservation.html", name="unavailability_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $unavailability = new Unavailability();

        if ($this->getUser()->hasRole('ROLE_ADMIN')) {
            $form = $this->createForm(UnavailabilityAdminType::class, $unavailability);
            $unavailability->setOrganiser($this->getUser());
            $form->get('organiser')->setData($this->getUser());
        } else {
            $form = $this->createForm(UnavailabilityType::class, $unavailability);
            $unavailability->setOrganiser($this->getUser());
            $unavailability->setType(Unavailability::REUNION);
        }

        // Si la réservation vient du calendrier, on intègre les dates de début et de fin choisies par l'utilisateur
        if (!null == $request->query->get('startDate') && !null == $request->query->get('endDate')) {

            $startDate = \DateTime::createFromFormat('d/m/Y H:i', $request->query->get('startDate'));
            $unavailability->setStartDate($startDate);
            $form->get('startDate')->setData($startDate);

            $endDate = \DateTime::createFromFormat('d/m/Y H:i', $request->query->get('endDate'));
            $unavailability->setEndDate($endDate);
            $form->get('endDate')->setData($endDate);

        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($unavailability);
            $entityManager->flush();

            return $this->redirectToRoute('room_show', ['id' => $unavailability->getRoom()->getId()]);
        }

        return $this->render('unavailability/new.html.twig', [
            'unavailability' => $unavailability,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Affiche les infos sur une réservation.
     * @Route("/reservation-{id}.html", name="unavailability_show", methods={"GET"})
     * @param Unavailability $unavailability
     * @return Response
     */
    public function show(Unavailability $unavailability): Response
    {
        return $this->render('unavailability/show.html.twig', ['unavailability' => $unavailability]);
    }

    /**
     * Permet de modifier une réservation.
     * @Route("/modifier/reservation-{id}.html", name="unavailability_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Unavailability $unavailability
     * @return Response
     */
    public function edit(Request $request, Unavailability $unavailability): Response
    {
        if ($this->getUser()->hasRole('ROLE_ADMIN')) {
            $form = $this->createForm(UnavailabilityAdminType::class, $unavailability);
        } else {
            $form = $this->createForm(UnavailabilityType::class, $unavailability);
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('unavailability_index', ['id' => $unavailability->getId()]);
        }

        return $this->render('unavailability/edit.html.twig', [
            'unavailability' => $unavailability,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Permet de supprimer une réservation.
     * @Route("/supprimer/reservation-{id}.html", name="unavailability_delete", methods={"DELETE"})
     * @param Request $request
     * @param Unavailability $unavailability
     * @return Response
     */
    public function delete(Request $request, Unavailability $unavailability): Response
    {
        if ($this->isCsrfTokenValid('delete'.$unavailability->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($unavailability);
            $entityManager->flush();
        }

        return $this->redirectToRoute('unavailability_index');
    }


    /**
     * @Route("/calendrier", name="unavailability_calendar")
     */
    public function calendar()
    {
        return $this->render('unavailability/calendar.html.twig');
    }
}
