<?php

namespace App\Controller;

use App\Entity\Unavailability;
use App\Entity\User;
use App\Form\Model\ChangePassword;
use App\Form\UserAdminType;
use App\Form\UserPasswordChangeType;
use App\Repository\UnavailabilityRepository;
use Doctrine\ORM\Configuration;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class UserController extends AbstractController
{
    /**
     * Liste de tous les utilisateurs actifs.
     * @Route("/admin/utilisateurs.html", name="user_index", methods={"GET"})
     * @return Response
     */
    public function index(): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $queryBuilder = $entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->orderBy('u.lastName', 'ASC');


        $adapter = new DoctrineORMAdapter($queryBuilder);

        $pagerfanta = new Pagerfanta($adapter);

        if (isset($_GET["page"])) {
            $pagerfanta->setCurrentPage($_GET["page"]);
        }

        return $this->render('user/index.html.twig', [
            'user_pager' => $pagerfanta
        ]);
    }

    /**
     * Permet à l'admin de créer un nouvel utilisateur.
     * @Route("/admin/nouvel-utilisateur.html", name="user_new", methods={"GET","POST"})
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @return Response
     */
    public function new(Request $request,
                        \Swift_Mailer $mailer): Response
    {
        $user = new User();
//        $user->setActive(true);

        // On génère un mot de passe provisoire
        $temporaryPassword = uniqid();
        $user->setPassword(password_hash($temporaryPassword, PASSWORD_BCRYPT));

        $form = $this->createForm(UserAdminType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $message = (new \Swift_Message('Bienvenue sur RéunionIT !'))
            ->setFrom('margouillat.reunion.it@gmail.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'email/registration.html.twig', [
                        'firstName' => $user->getFirstName(),
                        'lastName' => $user->getLastName(),
                        'temporaryPassword' => $temporaryPassword
                    ]
                ),
                'text/html'
            )
        ;

        $mailer->send($message);

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Affiche les infos sur un utilisateur.
     * @Route("/utilisateur-{id}.html", name="user_show", methods={"GET"})
     * @Security("user != null and user.getDeletedAt() == null", statusCode=404, message="Cet utilisateur n'existe plus ou n'a jamais existé.")
     * @IsGranted("ROLE_EMPLOYEE")
     * @param User $user
     * @return Response
     */
    public function show(User $user = null): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * Permet à l'admin de modifier un utilisateur.
     * @Route("/admin/modifier/utilisateur-{id}.html", name="user_edit", methods={"GET","POST"})
     * @Security("user != null and user.getDeletedAt() == null", statusCode=404, message="Cet utilisateur n'existe plus ou n'a jamais existé.")
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function edit(Request $request,
                         User $user = null): Response
    {
        $form = $this->createForm(UserAdminType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index', ['id' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet à l'utilisateur de changer son mot de passe.
     * @Route("/mot-de-passe.html", name="password_change", methods={"GET","POST"})
     * @IsGranted("ROLE_EMPLOYEE")
     * @param Request $request
     * @return Response
     */
    public function changePassword(Request $request)
    {
        $changePasswordModel = new ChangePassword();

        $form = $this->createForm(UserPasswordChangeType::class, $changePasswordModel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getUser();
            $user->setPassword(password_hash($form->getData()->getNewPassword(), PASSWORD_BCRYPT));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('index');
        }

        return $this->render('user/password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet à l'admin de supprimer ou désactiver un utilisateur.
     * @Route("/admin/supprimer/utilisateur-{id}.html", name="user_delete", methods={"DELETE"})
     * @Security("user != null and user.getDeletedAt() == null", statusCode=404, message="Cet utilisateur n'existe plus ou n'a jamais existé.")
     * @param Request $request
     * @param UnavailabilityController $unavailabilityController
     * @param UnavailabilityRepository $unavailabilityRepository
     * @param User $user
     * @return Response
     */
    public function delete(Request $request,
                           UnavailabilityRepository $unavailabilityRepository,
                           User $user = null): Response
    {
        $config = new Configuration();
        $config->addFilter('softdeleteable', 'Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter');

        $entityManager = $this->getDoctrine()->getManager();

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->getFilters()->enable('softdeleteable');

            // Si l'utilisateur est l'organisateur de réunions à venir, on supprime ces réunions.
            if ($user->hasUpcomingUnavailabilities()) {
                $this->removeUserFromDatabase($user);
//                $unavailabilityController->deleteUpcomingUnavailabilityByOrganiser($user);
            }

            // Si le User est invité à des réunions à venir, on le supprime des guests de ces réunions.
            if ($user->hasUpcomingInvitations()) {
                $this->removeUserFromDatabase($user);
//                $unavailabilityController->removeUserFromUpcomingUnavailabilityGuests($user);
            }

            $entityManager->remove($user);
            $entityManager->flush();

            if (empty($user->getUnavailabilities()) && empty($unavailabilityRepository->findByGuestAndOrder($user))) {
                // Si l'utilisateur n'est l'organisateur d'aucune réunion, on le supprime.
                $this->removeUserFromDatabase($user);
            } else {
                // Si l'utilisateur est l'organisateur de réunions passées, on ne le supprime pas.
//                $user->setActive(false);

                $entityManager->persist($user);
                $entityManager->flush();
            }
        }
        return $this->redirectToRoute('user_index');
    }

    /**
     * Supprime un utilisateur de la BDD.
     * @param User $user
     */
    private function removeUserFromDatabase(User $user)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();
    }

    /**
     * @Route("/tableau-de-bord.html", name="user_dashboard")
     * @return Response
     */
    public function dashboard()
    {
//        $entityManager = $this->getDoctrine()->getManager();
//
//        $organiserQueryBuilder = $entityManager->createQueryBuilder()
//            ->select('u')
//            ->from(Unavailability::class, 'u')
//            ->where('u.organiser = :organiser')
//            ->setParameter('organiser', $this->getUser())
//            ->orderBy('u.startDate', 'DESC');
//        $organiserAdapter = new DoctrineORMAdapter($organiserQueryBuilder);
//        $unavailabilitiesAsOrganiser_pagerfanta = new Pagerfanta($organiserAdapter);
//
//        $guestQueryBuilder = $entityManager->createQueryBuilder()
//            ->select('u')
//            ->from(Unavailability::class, 'u')
//            ->join('u.guests', 'g')
//            ->join('u.room', 'r')
//            ->addSelect('r')
//            ->where('g = :guest')
//            ->setParameter('guest', $this->getUser())
//            ->orderBy('u.startDate', 'DESC');
//        $guestAdapter = new DoctrineORMAdapter($guestQueryBuilder);
//        $unavailabilitiesAsGuest_pagerfanta = new Pagerfanta($guestAdapter);
//
//        $unavailabilitiesAsGuest_pagerfanta->setMaxPerPage(2);
//        $unavailabilitiesAsOrganiser_pagerfanta->setMaxPerPage(2);
//
//        if (isset($_GET["page"])) {
//            $unavailabilitiesAsOrganiser_pagerfanta->setCurrentPage($_GET["page"]);
//            $unavailabilitiesAsGuest_pagerfanta->setCurrentPage($_GET["page"]);
//        }

//        $unavailabilitiesAsOrganiser_pagerfanta = $unavailabilityRepository->findByOrganiserAndOrder($this->getUser());
//        $unavailabilitiesAsGuest_pagerfanta = $unavailabilityRepository->findByGuestAndOrder($this->getUser());
//
//        return $this->render('user/dashboard.html.twig', [
//            'unavailabilitiesAsOrganiser_pager' => $unavailabilitiesAsOrganiser_pagerfanta,
//            'unavailabilitiesAsGuest_pager' => $unavailabilitiesAsGuest_pagerfanta
//        ]);

        return $this->render('user/dashboard.html.twig', ['page'=>1]);
    }
}
