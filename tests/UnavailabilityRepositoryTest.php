<?php

namespace App\Tests;

use App\Controller\UnavailabilityController;
use App\Service\UnavailabilityManager;
use App\Entity\Room;
use App\Entity\Unavailability;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UnavailabilityRepositoryTest extends KernelTestCase
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

    private $organiser1;
    private $organiser2;

    private $guest1;
    private $guest2;

    private $room1;
    private $room2;
    private $room3;

    private $today;
    private $lastMonth;
    private $nextMonth;
    private $theMonthAfter;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->unavailabilityController = self::$container->get(UnavailabilityController::class);
        $this->unavailabilityRepository = $this->entityManager->getRepository(Unavailability::class);
        $this->unavailabilityManager = self::$container->get(UnavailabilityManager::class);

        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->roomRepository = $this->entityManager->getRepository(Room::class);

        $this->organiser1 = $this->userRepository->findOneBy(['id' => 1]);
        $this->organiser2 = $this->userRepository->findOneBy(['id' => 2]);

        $this->guest1 = $this->userRepository->findOneBy(['id' => 3]);
        $this->guest2 = $this->userRepository->findOneBy(['id' => 4]);

        $this->room1 = $this->roomRepository->findOneById(['id' => 1 ]);
        $this->room2 = $this->roomRepository->findOneById(['id' => 2 ]);
        $this->room3 = $this->roomRepository->findOneById(['id' => 3 ]);

        $this->today = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/m/d 00:00:00'));
        $m0 = ($this->today->format('n') - 1) % 12;
        $this->lastMonth = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/'.$m0.'/01 00:00:00'));
        $m1 = ($this->today->format('n') + 1) % 12;
        $this->nextMonth = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/'.$m1.'/01 00:00:00'));
        $m2 = ($m1 + 1) % 12;
        $this->theMonthAfter = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/'.$m2.'/01 00:00:00'));
    }

    public function createUnavailabilities()
    {
        $lastMonthUnavailability = new Unavailability();
        $lastMonthUnavailability->setObject('lastMonthUnavailability')
            ->setOrganiser($this->organiser1)
            ->setRoom($this->room1)
            ->addGuest($this->guest1)
            ->setType(0)
            ->setStartDate($this->lastMonth)
            ->setEndDate($this->today);
        $this->entityManager->persist($lastMonthUnavailability);

        $currentUnavailability = new Unavailability();
        $currentUnavailability->setObject('currentUnavailability')
            ->setOrganiser($this->organiser1)
            ->setRoom($this->room1)
            ->addGuest($this->guest2)
            ->setType(0)
            ->setStartDate($this->today)
            ->setEndDate($this->nextMonth);
        $this->entityManager->persist($currentUnavailability);

        $upcomingUnavailability = new Unavailability();
        $upcomingUnavailability->setObject('upcomingUnavailability')
            ->setOrganiser($this->organiser1)
            ->setRoom($this->room1)
            ->addGuest($this->guest2)
            ->setType(0)
            ->setStartDate($this->nextMonth)
            ->setEndDate($this->theMonthAfter);
        $this->entityManager->persist($upcomingUnavailability);

        $room2Unavailability = new Unavailability();
        $room2Unavailability->setObject('room2Unavailability')
            ->setOrganiser($this->organiser2)
            ->setRoom($this->room2)
            ->addGuest($this->guest1)
            ->setType(0)
            ->setStartDate($this->today)
            ->setEndDate($this->nextMonth);
        $this->entityManager->persist($room2Unavailability);

        $lastUnavailability = new Unavailability();
        $lastUnavailability->setObject('lastUnavailability')
            ->setOrganiser($this->organiser1)
            ->setRoom($this->room3)
            ->addGuest($this->guest1)
            ->setType(0)
            ->setStartDate($this->today)
            ->setEndDate($this->nextMonth);
        $this->entityManager->persist($lastUnavailability);

        $this->entityManager->flush();
    }

    public function deleteUnavailabilities()
    {
        $unavailabilities = $this->unavailabilityRepository->findAll();

        foreach ($unavailabilities as $unavailability) {
            $this->unavailabilityManager->removeUnavailabilityFromDatabase($unavailability);
        }

    }

    public function testFindAllAndOrder()
    {
        $this->createUnavailabilities();

        $this->assertNotEmpty($this->unavailabilityRepository->findAllAndOrder());
        $this->assertEquals(5, count($this->unavailabilityRepository->findAllAndOrder()));

        $this->deleteUnavailabilities();
    }

    public function testFindByOrganiserAndOrder()
    {
        $this->createUnavailabilities();

        $this->assertEquals(4, count($this->unavailabilityRepository->findByOrganiserAndOrder($this->organiser1)));
        $this->assertEquals(1, count($this->unavailabilityRepository->findByOrganiserAndOrder($this->organiser2)));

        $this->deleteUnavailabilities();
    }

    public function testFindByGuestAndOrder()
    {
        $this->createUnavailabilities();

        $this->assertEquals(3, count($this->unavailabilityRepository->findByGuestAndOrder($this->guest1)));
        $this->assertEquals(2, count($this->unavailabilityRepository->findByGuestAndOrder($this->guest2)));

        $this->deleteUnavailabilities();
    }

    public function testFindUnavailabilitiesByRoomByDates()
    {
        $this->createUnavailabilities();

        $this->assertNotEmpty($this->unavailabilityRepository->findUnavailabilitiesByRoomByDates($this->room1, $this->nextMonth, $this->theMonthAfter));
        $this->assertEquals(2, count($this->unavailabilityRepository->findUnavailabilitiesByRoomByDates($this->room1, $this->lastMonth, $this->today)));
        $this->assertEquals(2, count($this->unavailabilityRepository->findUnavailabilitiesByRoomByDates($this->room1, $this->today, $this->nextMonth)));
        $this->assertEquals(1, count($this->unavailabilityRepository->findUnavailabilitiesByRoomByDates($this->room1, $this->nextMonth, $this->theMonthAfter)));

        $this->deleteUnavailabilities();
    }

    public function testFindUnavailabilitiesByDates()
    {
        $this->createUnavailabilities();

        $this->assertEquals(4, count($this->unavailabilityRepository->findUnavailabilitiesByDates($this->lastMonth, $this->today)));
        $this->assertEquals(4, count($this->unavailabilityRepository->findUnavailabilitiesByDates($this->today, $this->nextMonth)));
        $this->assertEquals(1, count($this->unavailabilityRepository->findUnavailabilitiesByDates($this->nextMonth, $this->theMonthAfter)));

        $this->deleteUnavailabilities();
    }

    public function testFindUnavailabilitiesByRoom()
    {
        $this->createUnavailabilities();

        $this->assertEquals(3, count($this->unavailabilityRepository->findUnavailabilitiesByRoom($this->room1->getId())));
        $this->assertEquals(1, count($this->unavailabilityRepository->findUnavailabilitiesByRoom($this->room2->getId())));
        $this->assertEquals(1, count($this->unavailabilityRepository->findUnavailabilitiesByRoom($this->room3->getId())));

        $this->deleteUnavailabilities();
    }

    public function testFindCurrentUnavailabilities()
    {
        $this->createUnavailabilities();

        $this->assertNotEmpty($this->unavailabilityRepository->findCurrentUnavailabilities());
        $this->assertSame('currentUnavailability', $this->unavailabilityRepository->findCurrentUnavailabilities()[0]->getObject());

        $this->deleteUnavailabilities();
    }

    public function testFindUpcomingUnavailabilities()
    {
        $this->createUnavailabilities();

        $this->assertNotEmpty($this->unavailabilityRepository->findUpcomingUnavailabilities());
        $this->assertSame('upcomingUnavailability', $this->unavailabilityRepository->findUpcomingUnavailabilities()[0]->getObject());

        $this->deleteUnavailabilities();
    }

    public function testFindUpcomingUnavailabilitiesByOrganiser()
    {
        $this->createUnavailabilities();

        $this->assertEquals(1, count($this->unavailabilityRepository->findUpcomingUnavailabilitiesByOrganiser($this->organiser1)));
        $this->assertEquals(0, count($this->unavailabilityRepository->findUpcomingUnavailabilitiesByOrganiser($this->organiser2)));

        $this->deleteUnavailabilities();
    }

    public function testFindUpcomingUnavailabilitiesByRoom()
    {
        $this->createUnavailabilities();

        $this->assertEquals(1, count($this->unavailabilityRepository->findUpcomingUnavailabilitiesByRoom($this->room1)));
        $this->assertEquals(0, count($this->unavailabilityRepository->findUpcomingUnavailabilitiesByRoom($this->room2)));

        $this->deleteUnavailabilities();
    }

    public function testFindUpcomingUnavailabilitiesByGuest()
    {
        $this->createUnavailabilities();

        $this->assertEquals(0, count($this->unavailabilityRepository->findUpcomingUnavailabilitiesByGuest($this->guest1)));
        $this->assertEquals(1, count($this->unavailabilityRepository->findUpcomingUnavailabilitiesByGuest($this->guest2)));

        $this->deleteUnavailabilities();
    }

    public function testFindLastUnavailability()
    {
        $this->createUnavailabilities();

        $this->assertSame('lastUnavailability', $this->unavailabilityRepository->findLastUnavailability()->getObject());

        $this->deleteUnavailabilities();
    }

    public function testFindLastMonthUnavailability()
    {
        $this->createUnavailabilities();

        $this->assertSame('lastMonthUnavailability', $this->unavailabilityRepository->findLastMonthUnavailabilities()[0]->getObject());

        $this->deleteUnavailabilities();
    }
}
