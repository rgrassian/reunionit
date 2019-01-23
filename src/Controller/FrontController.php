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
        $totalRoomCount = $roomRepository->count([]);
        $upcomingUnavailabilitiesCount = count($unavailabilityRepository->findUpcomingUnavailabilities());
        $roomMaxCapacity = $roomRepository->findMaxCapacityRoom();
        $currentUnavailability = count($unavailabilityRepository->findCurrentUnavailabilities());
        $currentAvailableRoomCount = $totalRoomCount - $currentUnavailability;
        $lastOrganiser = $unavailabilityRepository->findLastUnavailability()->getOrganiser();
        $lastMonthOrganiser = $userRepository->findLastMonthOrganiser()[0];
        $lastMonthGuest = $userRepository->findLastMonthGuest()[0];

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
