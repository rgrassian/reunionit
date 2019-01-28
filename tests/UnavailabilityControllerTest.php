<?php

namespace App\Tests;

use App\Controller\UnavailabilityController;
use App\Entity\Unavailability;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UnavailabilityControllerTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    private $userRepository;

    private $unavailabilityRepository;

    private $unavailabilityController;

    private $client;

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
        $this->client = static::createClient();
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
