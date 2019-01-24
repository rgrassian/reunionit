<?php

namespace App\Tests;

use App\Controller\UnavailabilityController;
use App\Repository\UnavailabilityRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UnavailabilityControllerTest extends KernelTestCase
{
    private $unavailabilityController;
    private $userRepository;
    private $unavailabilityRepository;

    /**
     * UnavailabilityControllerTest constructor.
     */
    public function __construct()
    {
        parent::__construct();
        self::bootKernel();
        $this->unavailabilityController = self::$container->get(UnavailabilityController::class);
        $this->userRepository = self::$container->get(UserRepository::class);
        $this->unavailabilityRepository = self::$container->get(UnavailabilityRepository::class);
    }


    public function testDeleteUpcomingUnavailabilitiesByOrganiser()
    {
        $organiser = $this->userRepository->findOneById(56);

        $this->unavailabilityController->deleteUpcomingUnavailabilitiesByOrganiser($organiser);

        $this->assertEquals(0, count($this->unavailabilityRepository->findUpcomingUnavailabilitiesByOrganiser($organiser)));
    }

    public function testRemoveUserFromUpcomingUnavailabilitiesGuests()
    {
        $user = $this->userRepository->findOneById(60);

        $this->unavailabilityController->removeUserFromUpcomingUnavailabilitiesGuests($user);

//        $manager = self::$container->get(EntityManager::class);
//        $manager->persist($user);
//        $manager->flush();

        $result = $user->hasUpcomingInvitations();

        $this->assertFalse($result);
    }
}
