<?php

namespace App\Controller;

use App\Entity\Unavailability;
use App\Form\UnavailabilityAdminType;
use App\Form\UnavailabilityType;
use App\Repository\RoomRepository;
use App\Repository\UnavailabilityRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class UnavailabilityController extends AbstractController
{
    /**
     * Affiche les réunions organisées par l'utilisateur ou
     * toutes les réunions si l'utilisateur est admin.
     * @Route("/historique.html", name="unavailability_index", methods={"GET"})
     * @IsGranted("ROLE_EMPLOYEE")
     * @param UnavailabilityRepository $unavailabilityRepository
     * @return Response
     */
    public function index(UnavailabilityRepository $unavailabilityRepository): Response
    {
        if ($this->getUser()->hasRole('ROLE_ADMIN')) {
            $unavailabilities = $unavailabilityRepository->findAll();
        } else {
            $unavailabilities = $unavailabilityRepository->findByOrganiser($this->getUser());
        }
        return $this->render('unavailability/index.html.twig', [
            'unavailabilities' => $unavailabilities
        ]);
    }

    /**
     * Permet de créer une nouvelle réservation.
     * @Route("/nouvelle-reservation.html", name="unavailability_new", methods={"GET","POST"})
     * @IsGranted("ROLE_EMPLOYEE")
     * @param Request $request
     * @param RoomRepository $roomRepository
     * @return Response
     */
    public function new(Request $request, RoomRepository $roomRepository): Response
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

        // Si la réservation vient du calendrier, on intègre la salle
        // et les dates sélectionnées par l'utilisateur.
        if (!null == $request->query->get('startDate')
            && !null == $request->query->get('endDate')
            && !null == $request->query->get('roomId')) {

            $startDate = \DateTime::createFromFormat('d/m/Y H:i', $request->query->get('startDate'));
            $endDate = \DateTime::createFromFormat('d/m/Y H:i', $request->query->get('endDate'));
            $room = $roomRepository->findOneById($request->query->get('roomId'));

            $unavailability->setStartDate($startDate)
                ->setEndDate($endDate)
                ->setRoom($room);

            $form->get('startDate')->setData($startDate);
            $form->get('endDate')->setData($endDate);
            $form->get('room')->setData($room);

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
     * @IsGranted("ROLE_EMPLOYEE")
     * @param Unavailability $unavailability
     * @return Response
     */
    public function show(Unavailability $unavailability): Response
    {
        return $this->render('unavailability/show.html.twig', ['unavailability' => $unavailability]);
    }

    /**
     * Permet à l'admin ou à l'organisateur de modifier une réservation.
     * @Route("/modifier/reservation-{id}.html", name="unavailability_edit", methods={"GET","POST"})
     * @Security("unavailability.isOrganiser(user) or has_role('ROLE_ADMIN')")
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
     * Permet à l'admin ou à l'organisateur de supprimer une réservation.
     * @Route("/supprimer/reservation-{id}.html", name="unavailability_delete", methods={"DELETE"})
     * @IsGranted("ROLE_EMPLOYEE")
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param Unavailability $unavailability
     * @return Response
     */
    public function delete(Request $request, ObjectManager $entityManager, Unavailability $unavailability): Response
    {
        if ($this->isCsrfTokenValid('delete'.$unavailability->getId(), $request->request->get('_token'))) {
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
