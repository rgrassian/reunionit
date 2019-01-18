<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\Unavailability;
use App\Entity\User;
use App\Form\UnavailabilityAdminType;
use App\Form\UnavailabilityType;
use App\Repository\RoomRepository;
use App\Repository\UnavailabilityRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
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
     * @Route("/admin/historique.html", name="unavailability_index", methods={"GET"})
     * @param UnavailabilityRepository $unavailabilityRepository
     * @return Response
     */
    public function index(UnavailabilityRepository $unavailabilityRepository): Response
    {
        $unavailabilities = $unavailabilityRepository->findAllAndOrder();

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
     * @param UnavailabilityRepository $unavailabilityRepository
     * @return Response
     */
    public function new(Request $request,
                        RoomRepository $roomRepository,
                        UnavailabilityRepository $unavailabilityRepository): Response
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

        // Pré-remplissage des dates et de la salle sélectionnées
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

        $unavailabilities = $unavailabilityRepository->findUnavailabilitiesByRoom($room->getId());

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($unavailability);
            $entityManager->flush();

            return $this->redirectToRoute('room_show', [
                'id' => $unavailability->getRoom()->getId()
            ]);
        }

        return $this->render('unavailability/new.html.twig', [
            'unavailability' => $unavailability,
            'form' => $form->createView(),
            'unavailabilities' => $unavailabilities,
        ]);
    }

    /**
     * Affiche les infos sur une réservation.
     * @Route("/reservation-{id}.html", name="unavailability_show", methods={"GET"})
     * @Security("unavailability != null", statusCode=404, message="Cette réservation n'existe plus ou n'a jamais existé.")
     * @IsGranted("ROLE_EMPLOYEE")
     * @param Unavailability $unavailability
     * @return Response
     */
    public function show(Unavailability $unavailability = null): Response
    {
        return $this->render('unavailability/show.html.twig', [
            'unavailability' => $unavailability
        ]);
    }

    /**
     * Permet à l'admin ou à l'organisateur de modifier une réservation.
     * @Route("/modifier/reservation-{id}.html", name="unavailability_edit", methods={"GET","POST"})
     * @Security("unavailability != null", statusCode=404, message="Cette réservation n'existe plus ou n'a jamais existé.")
     * @Security("(unavailability.isOrganiser(user) or has_role('ROLE_ADMIN')) and unavailability.isNotPast()")
     * @param Request $request
     * @param Unavailability $unavailability
     * @return Response
     */
    public function edit(Request $request,
                         Unavailability $unavailability = null): Response
    {
        if ($this->getUser()->hasRole('ROLE_ADMIN')) {
            $form = $this->createForm(UnavailabilityAdminType::class, $unavailability);
        } else {
            $form = $this->createForm(UnavailabilityType::class, $unavailability);
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('unavailability_show', [
                'id' => $unavailability->getId()
            ]);
        }

        return $this->render('unavailability/edit.html.twig', [
            'unavailability' => $unavailability,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Permet à l'admin ou à l'organisateur de supprimer une réservation.
     * @Route("/supprimer/reservation-{id}.html", name="unavailability_delete", methods={"DELETE"})
     * @Security("unavailability != null", statusCode=404, message="Cette réservation n'existe plus ou n'a jamais existé.")
     * @Security("(unavailability.isOrganiser(user) or has_role('ROLE_ADMIN')) and unavailability.isNotPast()")
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param Unavailability $unavailability
     * @return Response
     */
    public function delete(Request $request,
                           ObjectManager $entityManager,
                           Unavailability $unavailability): Response
    {
        if ($this->isCsrfTokenValid('delete'.$unavailability->getId(), $request->request->get('_token'))) {
            $this->removeUnavailabilityFromDatabase($unavailability);
        }

        return $this->redirectToRoute('unavailability_index');
    }

    /**
     * Supprime toutes les réunions à venir organisées par un User.
     * @param User $organiser
     */
    public function deleteUpcomingUnavailabilityByOrganiser(User $organiser)
    {

        $unavailabilityRepository = $this->getDoctrine()->getRepository(Unavailability::class);
        $entityManager = $this->getDoctrine()->getManager();

        $unavailabilities = $unavailabilityRepository->findUpcomingUnavailabilitiesByOrganiser($organiser);

        foreach ($unavailabilities as $unavailability) {
            $this->removeUnavailabilityFromDatabase($unavailability);
        }
    }

    /**
     * Retire un utilisateur de la liste des invités aux réunions à venir.
     * @param User $user
     */
    public function removeUserFromUpcomingUnavailabilityGuests(User $user)
    {
        $unavailabilityRepository = $this->getDoctrine()->getRepository(Unavailability::class);
        $entityManager = $this->getDoctrine()->getManager();

        $unavailabilities = $unavailabilityRepository->findUpcomingUnavailabilitiesByGuest($user);

        foreach ($unavailabilities as $unavailability) {

            $unavailability->removeGuest($user);

            $entityManager->persist($unavailability);
            $entityManager->flush();
        }
    }

    /**
     * Supprime toutes les réunions à venir organisées dans une salle.
     * @param Room $room
     */
    public function deleteUpcomingUnavailabilityByRoom(Room $room)
    {
        $unavailabilityRepository = $this->getDoctrine()->getRepository(Unavailability::class);
        $entityManager = $this->getDoctrine()->getManager();

        $unavailabilities = $unavailabilityRepository->findUpcomingUnavailabilitiesByRoom($room);

        foreach ($unavailabilities as $unavailability) {
            $this->removeUnavailabilityFromDatabase($unavailability);
        }
    }

    /**
     * Supprime une Unavailability de la BDD.
     * @param Unavailability $unavailability
     */
    private function removeUnavailabilityFromDatabase(Unavailability $unavailability)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($unavailability);
        $entityManager->flush();
    }

    /**
     * @Route("/calendrier.html", name="unavailability_calendar")
     * @IsGranted("ROLE_GUEST")
     */
    public function calendar()
    {
        return $this->render('unavailability/calendar.html.twig');
    }
}
