<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Model\ChangePassword;
use App\Form\UserAdminType;
use App\Form\UserPasswordChangeType;
use App\Repository\UnavailabilityRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class UserController extends AbstractController
{
    /**
     * Liste de tous les utilisateurs.
     * @Route("/admin/utilisateurs.html", name="user_index", methods={"GET"})
     * @param UserRepository $userRepository
     * @return Response
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', ['users' => $userRepository->findAll()]);
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
        $user->setActive(true);

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
     * @Security("user != null", statusCode=404, message="Cet utilisateur n'existe plus ou n'a jamais existé.")
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
     * @Security("user != null", statusCode=404, message="Cet utilisateur n'existe plus ou n'a jamais existé.")
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function edit(Request $request, User $user): Response
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
     * @Security("user != null", statusCode=404, message="Cet utilisateur n'existe plus ou n'a jamais existé.")
     * @param Request $request
     * @param UnavailabilityController $unavailabilityController
     * @param UnavailabilityRepository $unavailabilityRepository
     * @param User $user
     * @return Response
     */
    public function delete(Request $request,
                           UnavailabilityController $unavailabilityController,
                           UnavailabilityRepository $unavailabilityRepository,
                           User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {

            // Si l'utilisateur est l'organisateur de réunions à venir, on supprime ces réunions.
            if ($user->hasUpcomingUnavailabilities()) {
                $unavailabilityController->deleteUpcomingUnavailabilityByOrganiser($user);
            }

            if (empty($user->getUnavailabilities())) {
                // Si l'utilisateur n'est l'organisateur d'aucune réunion, on le supprime.
                $this->removeUserFromDatabase($user);
            } else {
                // Si l'utilisateur est l'organisateur de réunions passées, on set sa propriété Active à false
                $user->setActive(false);
            }

            // Si le User est invité à des réunions, le supprimer des guests des réunions à venir.
            if ($user->hasUpcomingInvitations()) {
                $unavailabilityController->removeUserFromUpcomingUnavailabilityGuests($user);
            }

            if (empty($unavailabilityRepository->findByGuestAndOrder($user))) {
                // S'il n'est invité à aucune réunion, on le supprime.
                $this->removeUserFromDatabase($user);
            } else {
                // Sinon, le set active = false.
                $user->setActive(false);
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
     * @param UnavailabilityRepository $unavailabilityRepository
     * @return Response
     */
    public function dashboard(UnavailabilityRepository $unavailabilityRepository)
    {
        $unavailabilitiesAsOrganiser = $unavailabilityRepository->findByOrganiserAndOrder($this->getUser());
        $unavailabilitiesAsGuest = $unavailabilityRepository->findByGuestAndOrder($this->getUser());

        return $this->render('user/dashboard.html.twig', [
            'unavailabilitiesAsOrganiser' => $unavailabilitiesAsOrganiser,
            'unavailabilitiesAsGuest' => $unavailabilitiesAsGuest
        ]);
    }
}
