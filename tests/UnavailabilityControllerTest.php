<?php

namespace App\Tests;

use App\Controller\UnavailabilityController;
use App\Entity\Room;
use App\Entity\Unavailability;
use App\Entity\User;
use App\Service\UnavailabilityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UnavailabilityControllerTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    private $unavailabilityRepository;

    private $unavailabilityController;

    private $unavailabilityManager;

    private $userRepository;

    private $roomRepository;

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
        $this->unavailabilityRepository = $this->entityManager->getRepository(Unavailability::class);
        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->roomRepository = $this->entityManager->getRepository(Room::class);
        $this->unavailabilityManager = self::$container->get(UnavailabilityManager::class);
        $this->client = static::createClient();
    }

    public function exampleTest()
    {

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
