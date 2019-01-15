<?php

namespace App\Controller;

use App\Entity\Room;
use App\Form\RoomType;
use App\Repository\RoomRepository;
use phpDocumentor\Reflection\Types\This;
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
     * @return Response
     */
    public function index(RoomRepository $roomRepository): Response
    {
        return $this->render('room/index.html.twig', ['rooms' => $roomRepository->findAll()]);
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

        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        # Si le formulaire est soumis et qu'il est valide
        if ($form->isSubmitted() && $form->isValid()) {

            #dump($article);
            # 1. Traitement de l'upload de l'image

            /** @var UploadedFile $picture */
            $picture = $room->getPicture();

            if (null !== $picture) {
                $fileName = strtolower($room->getName())
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
            'form' => $form->createView()
        ]);
    }

    /**
     * Affiche les caractéristiques d'une salle.
     * @Route("/salle-{id}.html", name="room_show", methods={"GET"})
     * @IsGranted("ROLE_EMPLOYEE")
     * @param Room $room
     * @return Response
     */
    public function show(Room $room): Response
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
     * @param Request $request
     * @param Room $room
     * @param Packages $packages
     * @return Response
     */
    public function edit(Request $request,
                         Room $room,
                         Packages $packages): Response
    {
        # On passe à notre formulaire l'URL de la picture
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

        $form = $this->createForm(RoomType::class, $room, $options);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('room_show', [
                'id' => $room->getId()
            ]);
        }

        return $this->render('room/edit.html.twig', [
            'room' => $room,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Permet à l'admin de supprimer une salle.
     * @Route("/admin/supprimer/salle-{id}.html", name="room_delete", methods={"DELETE"})
     * @param Request $request
     * @param Room $room
     * @return Response
     */
    public function delete(Request $request, Room $room): Response
    {
        if ($this->isCsrfTokenValid('delete'.$room->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($room);
            $entityManager->flush();
        }

        return $this->redirectToRoute('room_index');
    }
}
