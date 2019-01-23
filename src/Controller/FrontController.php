<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\Unavailability;
use App\Repository\RoomRepository;
use App\Repository\UnavailabilityRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;

class FrontController extends AbstractController
{
    /**
     * Page d'accueil de l'application.
     * @Route("/", name="index")
     * @param RoomRepository $roomRepository
     * @param UnavailabilityRepository $unavailabilityRepository
     * @param UserRepository $userRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(RoomRepository $roomRepository,
                          UnavailabilityRepository $unavailabilityRepository,
                          UserRepository $userRepository)
    {
        // Si l'application n'a jamais été utilisée, on initialise les stats avec des valeurs par défaut.
        $totalRoomCount = $roomRepository->count([]);
        $upcomingUnavailabilitiesCount = count($unavailabilityRepository->findUpcomingUnavailabilities())   ?? 0;
        $roomMaxCapacity = $roomRepository->findMaxCapacityRoom()                                           ?? 0;
        $currentUnavailability = count($unavailabilityRepository->findCurrentUnavailabilities())            ?? 0;
        $currentAvailableRoomCount = $totalRoomCount - $currentUnavailability;
        $lastOrganiser = $unavailabilityRepository->findLastUnavailability()
            ? $unavailabilityRepository->findLastUnavailability()->getOrganiser()
            : $this->getUser();
        $lastMonthOrganiser = $userRepository->findLastMonthOrganiser()[0]  ?? $this->getUser();
        $lastMonthGuest = $userRepository->findLastMonthGuest()[0]          ?? $this->getUser();

        return $this->render('front/index.html.twig', [
            'totalRoomCount' => $totalRoomCount,
            'upcomingUnavailabilities' => $upcomingUnavailabilitiesCount,
            'roomMacCapacity' => $roomMaxCapacity,
            'currentAvailableRoomCount' => $currentAvailableRoomCount,
            'currentUnavailability' => $currentUnavailability,
            'lastOrganiser' => $lastOrganiser,
            'lastMonthOrganiser' => $lastMonthOrganiser,
            'lastMonthGuest' => $lastMonthGuest
        ]);
    }
}
