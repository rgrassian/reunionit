<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\Unavailability;
use App\Entity\User;
use App\Form\UnavailabilityAdminType;
use App\Form\UnavailabilityType;
use App\Repository\RoomRepository;
use App\Repository\UnavailabilityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
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
     * @return Response
     */
    public function index(): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        // On désactive le filtre pour obtenir les réunions organisées dans des salles supprimées,
        // ou dont l'organisateur ou un invité a été supprimé.
        $entityManager->getFilters()->disable('softdeleteable');

        $queryBuilder = $entityManager->createQueryBuilder()
            ->select('u')
            ->from(Unavailability::class, 'u')
            ->orderBy('u.startDate', 'DESC');

        $adapter = new DoctrineORMAdapter($queryBuilder);

        $pagerfanta = new Pagerfanta($adapter);

        if (isset($_GET["page"])) {
            $pagerfanta->setCurrentPage($_GET["page"]);
        }

        return $this->render('unavailability/index.html.twig', [
            'unavailabilities_pager' => $pagerfanta
        ]);
    }

    /**
     * Permet de créer une nouvelle réservation.
     * @Route("/nouvelle-reservation.html", name="unavailability_new", methods={"GET","POST"})
     * @IsGranted("ROLE_EMPLOYEE")
     * @param Request $request
     * @param RoomRepository $roomRepository
     * @param \Swift_Mailer $mailer
     * @param UnavailabilityRepository $unavailabilityRepository
     * @return Response
     */
    public function new(Request $request,
                        RoomRepository $roomRepository,
                        \Swift_Mailer $mailer,
                        UnavailabilityRepository $unavailabilityRepository): Response
    {
        $unavailability = new Unavailability();

        $unavailability->setOrganiser($this->getUser());

        if ($this->getUser()->hasRole('ROLE_ADMIN')) {
            $form = $this->createForm(UnavailabilityAdminType::class, $unavailability);
            // Comme le formulaire admin a un champ Organiser, on l'indique.
            $form->get('organiser')->setData($this->getUser());
        } else {
            $form = $this->createForm(UnavailabilityType::class, $unavailability);
            $unavailability->setType(Unavailability::REUNION);
        }

        // Pré-remplissage des dates et de la salle sélectionnées
        if (!null == $request->query->get('startDate')
            && !null == $request->query->get('endDate')
            && !null == $request->query->get('roomId')) {

            $startDate = \DateTime::createFromFormat('d/m/Y H:i', $request->query->get('startDate'));
            $endDate = \DateTime::createFromFormat('d/m/Y H:i', $request->query->get('endDate'));
            $room = $roomRepository->findOneById($request->query->get('roomId'));

            $unavailability ->setStartDate($startDate)
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

            $formData = $form->getData();

            // Envoi de mail à l'organisateur
            $this->sendEmail($mailer,
                'ReunionIT | Enregistrement de votre réservation',
                $formData->getOrganiser()->getEmail(),
                'email/unavailability_new.html.twig',
                ['data' => $formData]
            );

            // Envoi de mails aux invités
            $guests = $formData->getGuests();
            foreach ($guests as $guest) {
                $this->sendEmail($mailer,
                    'ReunionIT | Nouvelle invitation',
                    $guest->getEmail(),
                    'email/unavailability_new_guest.html.twig',
                    ['guest'=>$guest,'data' => $formData]
                );
            }

            $this->addFlash('notice',
                'La réservation est enregistrée.');

            return $this->redirectToRoute('unavailability_calendar');
        }

//        $unavailabilities = $unavailabilityRepository->findUnavailabilitiesByRoom($room->getId());

        return $this->render('unavailability/new.html.twig', [
            'unavailability' => $unavailability,
            'form' => $form->createView(),
//            'unavailabilities' => $unavailabilities,
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
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');

        return $this->render('unavailability/show.html.twig', [
            'unavailability' => $unavailability
        ]);
    }

    /**
     * Permet à l'admin ou à l'organisateur de modifier une réservation.
     * @Route("/modifier/reservation-{id}.html", name="unavailability_edit", methods={"GET","POST"})
     * @Security("unavailability != null", statusCode=404,
     *     message="Cette réservation n'existe plus ou n'a jamais existé.")
     * @Security("(unavailability.isOrganiser(user) or has_role('ROLE_ADMIN')) and unavailability.isNotPast()")
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @param Unavailability $unavailability
     * @return Response
     */
    public function edit(Request $request,
                         \Swift_Mailer $mailer,
                         Unavailability $unavailability = null): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->getFilters()->disable('softdeleteable');

        if ($this->getUser()->hasRole('ROLE_ADMIN')) {
            $form = $this->createForm(UnavailabilityAdminType::class, $unavailability);
        } else {
            $form = $this->createForm(UnavailabilityType::class, $unavailability);
        }

        $oldGuests = $unavailability->getGuests()->toArray();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $formData = $form->getData();

            // Envoi de mail à l'organisateur
            $this->sendEmail($mailer,
                'ReunionIT | Modification de votre réservation',
                $formData->getOrganiser()->getEmail(),
                'email/unavailability_edit.html.twig',
                ['data' => $formData]
            );

            $newGuests = $unavailability->getGuests()->toArray();

//            $persistGuests = array_intersect_key($oldGuests, $newGuests);
//            $removedGuests = array_diff_key($oldGuests, $newGuests);
//            $additionalGuests = array_diff_key($newGuests, $oldGuests);

            $persistGuests = [];
            $removedGuests = [];
            $additionalGuests = [];
            foreach ($oldGuests as $guest) {
                if (in_array($guest, $newGuests)) {
                    $persistGuests[] = $guest;
                } else {
                    $removedGuests[] = $guest;
                }
            }
            foreach ($newGuests as $guest) {
                if (!in_array($guest, $oldGuests)) {
                    $additionalGuests[] = $guest;
                }
            }

            //dd($removedGuests);

            // Envoi de mails aux guests déjà invités
            foreach ($persistGuests as $guest) {
                $this->sendEmail($mailer,
                    'ReunionIT | Modification d\'une invitation',
                    $guest->getEmail(),
                    'email/unavailability_edit_guest.html.twig',
                    ['guest'=>$guest,'data' => $formData]
                );
            }

            // Envoi de mails aux guests nouvellement invités
            foreach ($additionalGuests as $guest) {
                $this->sendEmail($mailer,
                    'ReunionIT | Nouvelle invitation',
                    $guest->getEmail(),
                    'email/unavailability_new_guest.html.twig',
                    ['guest'=>$guest,'data' => $formData]
                );
            }

            // Envoi de mails aux guests nouvellement invités
            foreach ($removedGuests as $guest) {
                $this->sendEmail($mailer,
                    'ReunionIT | Invitation annulée',
                    $guest->getEmail(),
                    'email/unavailability_delete_guest.html.twig',
                    ['guest'=>$guest,'data' => $formData]
                );
            }

            $this->addFlash('notice',
                'La réservation a été modifiée.');

            return $this->redirectToRoute('unavailability_calendar');
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
     * @Security("unavailability.isOrganiser(user) or has_role('ROLE_ADMIN')",
     *     message="Impossible de supprimer une réunion dont vous n'êtes pas l'organisateur.")
     * @Security("unavailability.isNotPast()", message="Impossible de supprimer une réunion passée.")
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @param Unavailability $unavailability
     * @return Response
     */
    public function delete(Request $request,
                           \Swift_Mailer $mailer,
                           Unavailability $unavailability): Response
    {
        if ($this->isCsrfTokenValid('delete'.$unavailability->getId(), $request->request->get('_token'))) {

            // Envoi de mail à l'organisateur
            $this->sendEmail($mailer,
                'ReunionIT | Suppression de votre réservation',
                $unavailability->getOrganiser()->getEmail(),
                'email/unavailability_delete.html.twig',
                ['data' => $unavailability]
            );

            // Envoi de mails aux invités
            $guests = $unavailability->getGuests();
            foreach ($guests as $guest) {
                $this->sendEmail($mailer,
                    'ReunionIT | Annulation d\'une invitation',
                    $guest->getEmail(),
                    'email/unavailability_delete_guest.html.twig',
                    ['guest'=>$guest,'data' => $unavailability]
                );
            }


            $this->removeUnavailabilityFromDatabase($unavailability);
        }

        $this->addFlash('notice',
            'La réservation a été annulée.');

        return $this->redirectToRoute('unavailability_calendar');
    }

    /**
     * Supprime toutes les réunions à venir organisées par un User.
     * @param User $organiser
     */
    public function deleteUpcomingUnavailabilityByOrganiser(User $organiser)
    {
        $unavailabilityRepository = $this->getDoctrine()->getRepository(Unavailability::class);
        // à checker
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

    /**
     * @param \Swift_Mailer $mailer
     * @param $object
     * @param $to
     * @param $view
     * @param $options
     */
    public function sendEmail(\Swift_Mailer $mailer, $object, $to, $view, $options)
    {
        $message = (new \Swift_Message($object))
            ->setFrom('margouillat.reunion.it@gmail.com')
            ->setTo($to)
            ->setBody(
                $this->renderView($view, $options), 'text/html');
        $mailer->send($message);
    }
}
