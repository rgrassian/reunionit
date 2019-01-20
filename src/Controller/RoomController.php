<?php

namespace App\Controller;

use Doctrine\ORM\Configuration;
use App\Entity\Room;
use App\Form\RoomType;
use App\Provider\FeaturesProvider;
use App\Repository\RoomRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\LogicException;

class RoomController extends AbstractController
{
    /**
     * Liste de toutes les salles.
     * @Route("/liste-des-salles.html", name="room_index", methods={"GET"})
     * @IsGranted("ROLE_EMPLOYEE")
     * @param RoomRepository $roomRepository
     * @param FeaturesProvider $featuresProvider
     * @return Response
     */
    public function index(RoomRepository $roomRepository, FeaturesProvider $featuresProvider): Response
    {
        $features = $featuresProvider->getFeatures();
        return $this->render('room/index.html.twig', [
            'rooms' => $roomRepository->findAll(),
            'features' => $features
        ]);
    }

    /**
     * Permet à l'admin de créer une nouvelle salle.
     * @Route("admin/nouvelle-salle.html", name="room_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $room = new Room();
//        $room->setActive(true);

        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        # Si le formulaire est soumis et qu'il est valide
        if ($form->isSubmitted() && $form->isValid()) {

            #dump($article);
            # 1. Traitement de l'upload de l'image

            /** @var UploadedFile $picture */
            $picture = $room->getPicture();

            if (null !== $picture) {
                $fileName = 'salle-' . $room->getId()
                    . '.' . $picture->guessExtension();

                try {
                    $picture->move(
                        $this->getParameter('rooms_assets_dir'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // TODO: ??
                }

                # Mise à jour de l'image
                $room->setPicture($fileName);

                try {
                    # 3. Sauvegarde en BDD
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($room);
                    $em->flush();

                    # 5. Redirection vers l'article créé
                    return $this->redirectToRoute('room_show', [
                        'id' => $room->getId()
                    ]);

                } catch (LogicException $e) {

                    # Transition non autorisé...
                    $this->addFlash('error',
                        $e->getMessage());
                }

            } else {

                # 4. Notification
                $this->addFlash('error',
                    "N'oubliez pas de choisir une image d'illustration");
            }
        }

        return $this->render('room/new.html.twig', [
            'room' => $room,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Affiche les caractéristiques d'une salle.
     * @Route("/salle-{id}.html", name="room_show", methods={"GET"})
     * @Security("room != null and room.getDeletedAt() == null", statusCode=404, message="Cette salle n'existe plus ou n'a jamais existé.")
     * @IsGranted("ROLE_EMPLOYEE")
     * @param Room $room
     * @return Response
     */
    public function show(Room $room = null): Response
    {
        return $this->render('room/show.html.twig', [
            'room' => $room
        ]);
    }

    /**
     * Permet à l'admin de modifier
     * les caractéristiques d'une salle.
     * @Route("/admin/modifier/salle-{id}.html",
     *     name="room_edit",
     *     methods={"GET","POST"}))
     * @Security("room != null and and room.getDeletedAt() == null", statusCode=404, message="Cette salle n'existe plus ou n'a jamais existé.")
     * @param Request $request
     * @param Room $room
     * @param Packages $packages
     * @return Response
     */
    public function edit(Request $request,
                         Room $room,
                         Packages $packages): Response
    {
        # On passe à notre formulaire l'URL de la featuredImage
        $options = [
            'image_url' => $packages->getUrl('images/room/'
                . $room->getPicture())
        ];

        # Récupération de l'image
        $pictureName = $room->getPicture();

        # Notre formulaire attend une instance de File pour l'edition
        # de la featuredImage
        $room->setPicture(
            new File($this->getParameter('rooms_assets_dir')
                . '/' . $pictureName)
        );

        # Création / Récupération du Formulaire
        $form = $this->createForm(RoomType::class, $room, $options)
            ->handleRequest($request);

        # Si le formulaire est soumis et qu'il est valide
        if ($form->isSubmitted() && $form->isValid()) {

            #dump($article);
            # 1. Traitement de l'upload de l'image

            /** @var UploadedFile $picture */
            $picture = $room->getPicture();

            if (null !== $picture) {

                $fileName = 'salle-' . $room->getId()
                    . '.' . $picture->guessExtension();

                try {
                    $picture->move(
                        $this->getParameter('rooms_assets_dir'),
                        $fileName
                    );
                } catch (FileException $e) {

                }

                # Mise à jour de l'image
                $room->setPicture($fileName);

            } else {
                $room->setPicture($pictureName);
            }

            # 3. Sauvegarde en BDD
            $em = $this->getDoctrine()->getManager();
            $em->persist($room);
            $em->flush();

            # 5. Redirection vers l'article créé
            return $this->redirectToRoute('room_show', [
                'id' => $room->getId()
            ]);

        }

        # Affichage du Formulaire
        return $this->render('room/edit.html.twig', [
            'form' => $form->createView(),
            'room' => $room
        ]);
    }

    /**
     * Permet à l'admin de supprimer une salle.
     * @Route("/admin/supprimer/salle-{id}.html", name="room_delete", methods={"DELETE"})
     * @Security("room != null and room.getDeletedAt() == null", statusCode=404, message="Cette salle n'existe plus ou n'a jamais existé.")
     * @param Request $request
     * @param UnavailabilityController $unavailabilityController
     * @param Room $room
     * @return Response
     */
    public function delete(Request $request,
                           UnavailabilityController $unavailabilityController,
                           Room $room): Response
    {
        $config = new Configuration();
        $config->addFilter('softdeleteable', 'Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter');

        if ($this->isCsrfTokenValid('delete'.$room->getId(), $request->request->get('_token'))) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->getFilters()->enable('softdeleteable');

            // Si l'utilisateur est l'organisateur de réunions à venir, on supprime ces réunions.
            if ($room->hasUpcomingUnavailabilities()) {
                $unavailabilityController->deleteUpcomingUnavailabilityByRoom($room);
            }

            $entityManager->remove($room);
            $entityManager->flush();

            if (empty($room->getUnavailabilities())) {
                // Si aucune réunion n'est organisée dans la salle, on la supprime.
                $entityManager->remove($room);
                $entityManager->flush();
            } else {
                // Si des réunions ont eu lieu dans la salle, on set sa propriété Active à false
//                $room->setActive(false);
                $entityManager->persist($room);
                $entityManager->flush();
            }
        }

        return $this->redirectToRoute('room_index');
    }
}
