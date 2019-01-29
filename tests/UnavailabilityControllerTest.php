<?php

namespace App\Tests;

use App\Controller\UnavailabilityController;
use App\Entity\Room;
use App\Entity\Unavailability;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UnavailabilityControllerTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    private $unavailabilityRepository;

    private $unavailabilityController;

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
        $this->client = static::createClient();
    }


    public function testDeleteUpcomingUnavailabilitiesByOrganiser()
    {
        $organiser = $this->userRepository->findOneBy(['id' => 1]);
        $this->assertEmpty($this->unavailabilityRepository->findUpcomingUnavailabilitiesByOrganiser($organiser));

        $room = $this->roomRepository->findOneById(['id' => 1 ]);

        $today = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/m/d 00:00:00'));
        $m1 = ($today->format('n') + 1) % 12;
        $nextMonth = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/'.$m1.'/01 00:00:00'));
        $m2 = ($m1 + 1) % 12;
        $theMonthAfter = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/'.$m2.'/01 00:00:00'));

        $upcomingUnavailability = new Unavailability();
        $upcomingUnavailability->setObject('test')
            ->setOrganiser($organiser)
            ->setRoom($room)
            ->setType(0)
            ->setStartDate($nextMonth)
            ->setEndDate($theMonthAfter);

        $this->entityManager->persist($upcomingUnavailability);
        $this->entityManager->flush();

        $this->assertNotEmpty($this->unavailabilityRepository->findUpcomingUnavailabilitiesByOrganiser($organiser));

        $this->unavailabilityController->deleteUpcomingUnavailabilitiesByOrganiser($organiser);

        $this->assertEmpty($this->unavailabilityRepository->findUpcomingUnavailabilitiesByOrganiser($organiser));
    }

//    public function testRemoveUserFromUpcomingUnavailabilitiesGuests()
//    {
//        $user = $this->userRepository->findOneBy(['id' => 60]);
//
//        $this->unavailabilityController->removeUserFromUpcomingUnavailabilitiesGuests($user);
//
//        $this->entityManager->persist($user);
//
//        $result = $user->hasUpcomingInvitations();
//
//        $this->assertFalse($result);
//    }
//
//    public function testDeleteUpcomingUnavailabilitiesByRoom()
//    {
//        $organiser = $this->userRepository->findOneBy(['id' => 70]);
//
//        $room = $this->roomRepository->findOneById(['id' => 38 ]);
//
//        $today = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/m/d 00:00:00'));
//        $m1 = ($today->format('n') + 1) % 12;
//        $nextMonth = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/'.$m1.'/01 00:00:00'));
//        $m2 = ($m1 + 1) % 12;
//        $theMonthAfter = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/'.$m2.'/01 00:00:00'));
//
//        $unavailability = new Unavailability();
//        $unavailability->setObject('test')
//            ->setOrganiser($organiser)
//            ->setRoom($room)
//            ->setType(0)
//            ->setStartDate($nextMonth)
//            ->setEndDate($theMonthAfter);
//
//        $this->entityManager->persist($unavailability);
//        $this->entityManager->flush();
//
//        $this->assertNotNull($this->unavailabilityRepository->findUpcomingUnavailabilitiesByRoom($room));
//
//        $this->unavailabilityController->deleteUpcomingUnavailabilitiesByRoom($room);
//
//        $this->assertEquals(0, count($this->unavailabilityRepository->findUpcomingUnavailabilitiesByRoom($room)));
//    }

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
