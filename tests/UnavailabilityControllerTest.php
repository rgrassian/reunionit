<?php

namespace App\Tests;

use App\Controller\UnavailabilityController;
use App\Entity\Unavailability;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UnavailabilityControllerTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    private $userRepository;

    private $unavailabilityRepository;

    private $unavailabilityController;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->unavailabilityController = self::$container->get(UnavailabilityController::class);
        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->unavailabilityRepository = $this->entityManager->getRepository(Unavailability::class);
    }

    public function testDeleteUpcomingUnavailabilitiesByOrganiser()
    {
        $organiser = $this->userRepository
            ->findOneBy(['id' => 56]);

        $this->unavailabilityController->deleteUpcomingUnavailabilitiesByOrganiser($organiser);

        $this->assertEquals(0, count($this->unavailabilityRepository->findUpcomingUnavailabilitiesByOrganiser($organiser)));
    }

    public function testRemoveUserFromUpcomingUnavailabilitiesGuests()
    {
        $user = $this->userRepository->findOneBy(['id' => 60]);

        $this->unavailabilityController->removeUserFromUpcomingUnavailabilitiesGuests($user);

        $this->entityManager->persist($user);

        $result = $user->hasUpcomingInvitations();

        $this->assertFalse($result);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null; // avoid memory leaks
    }
}
