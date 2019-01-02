<?php

namespace App\Controller;

use App\Entity\Unavailability;
use App\Form\UnavailabilityType;
use App\Repository\UnavailabilityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/unavailability")
 */
class UnavailabilityController extends AbstractController
{
    /**
     * @Route("/", name="unavailability_index", methods={"GET"})
     */
    public function index(UnavailabilityRepository $unavailabilityRepository): Response
    {
        return $this->render('unavailability/index.html.twig', ['unavailabilities' => $unavailabilityRepository->findAll()]);
    }

    /**
     * @Route("/new", name="unavailability_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $unavailability = new Unavailability();
        $form = $this->createForm(UnavailabilityType::class, $unavailability);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($unavailability);
            $entityManager->flush();

            return $this->redirectToRoute('unavailability_index');
        }

        return $this->render('unavailability/new.html.twig', [
            'unavailability' => $unavailability,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="unavailability_show", methods={"GET"})
     */
    public function show(Unavailability $unavailability): Response
    {
        return $this->render('unavailability/show.html.twig', ['unavailability' => $unavailability]);
    }

    /**
     * @Route("/{id}/edit", name="unavailability_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Unavailability $unavailability): Response
    {
        $form = $this->createForm(UnavailabilityType::class, $unavailability);
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
     * @Route("/{id}", name="unavailability_delete", methods={"DELETE"})
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
}
