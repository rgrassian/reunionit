<?php

namespace App\Tests;

use App\Controller\UnavailabilityController;
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

    private $userRepository;

    private $roomRepository;

    private $client;

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        parent::__construct();
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->unavailabilityController = self::$container->get(UnavailabilityController::class);
        $this->unavailabilityRepository = $this->entityManager->getRepository(Unavailability::class);
        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->roomRepository = $this->entityManager->getRepository(Room::class);
    }


    public function testUnavailabilityRepository()
    {
        $this->assertEmpty($this->unavailabilityRepository->findAll());

        $organiser = $this->userRepository->findOneBy(['id' => 3]);
        $room = $this->roomRepository->findOneById(['id' => 1 ]);
        $guest = $this->userRepository->findOneBy(['id' => 5]);

        $today = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/m/d 00:00:00'));
        $m0 = ($today->format('n') - 1) % 12;
        $lastMonth = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/'.$m0.'/01 00:00:00'));
        $m1 = ($today->format('n') + 1) % 12;
        $nextMonth = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/'.$m1.'/01 00:00:00'));
        $m2 = ($m1 + 1) % 12;
        $theMonthAfter = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/'.$m2.'/01 00:00:00'));

        $this->assertEmpty($this->unavailabilityRepository->findAllAndOrder());
        $this->assertEmpty($this->unavailabilityRepository->findByOrganiserAndOrder($organiser));
        $this->assertEmpty($this->unavailabilityRepository->findByGuestAndOrder($guest));
        $this->assertEmpty($this->unavailabilityRepository->findUnavailabilitiesByRoomByDates($room, $nextMonth, $theMonthAfter));
        $this->assertEmpty($this->unavailabilityRepository->findUnavailabilitiesByDates($nextMonth, $theMonthAfter));
        $this->assertEmpty($this->unavailabilityRepository->findUnavailabilitiesByRoom(1));
        $this->assertEmpty($this->unavailabilityRepository->findCurrentUnavailabilities());
        $this->assertEmpty($this->unavailabilityRepository->findUpcomingUnavailabilities());
        $this->assertEmpty($this->unavailabilityRepository->findUpcomingUnavailabilitiesByOrganiser($organiser));
        $this->assertEmpty($this->unavailabilityRepository->findUpcomingUnavailabilitiesByRoom($room));
        $this->assertEmpty($this->unavailabilityRepository->findUpcomingUnavailabilitiesByGuest($guest));
        $this->assertEmpty($this->unavailabilityRepository->findUpcomingUnavailabilitiesByGuest($guest));
        $this->assertEmpty($this->unavailabilityRepository->findLastUnavailability());
        $this->assertEmpty($this->unavailabilityRepository->findLastMonthUnavailabilities());

        $lastMonthUnavailability = new Unavailability();
        $lastMonthUnavailability->setObject('lastMonthUnavailability')
            ->setOrganiser($organiser)
            ->setRoom($room)
            ->addGuest($guest)
            ->setType(0)
            ->setStartDate($lastMonth)
            ->setEndDate($today);
        $this->entityManager->persist($lastMonthUnavailability);

        $currentUnavailability = new Unavailability();
        $currentUnavailability->setObject('currentUnavailability')
            ->setOrganiser($organiser)
            ->setRoom($room)
            ->addGuest($guest)
            ->setType(0)
            ->setStartDate($today)
            ->setEndDate($nextMonth);
        $this->entityManager->persist($currentUnavailability);

        $upcomingUnavailability = new Unavailability();
        $upcomingUnavailability->setObject('lastUnavailability')
            ->setOrganiser($organiser)
            ->setRoom($room)
            ->addGuest($guest)
            ->setType(0)
            ->setStartDate($nextMonth)
            ->setEndDate($theMonthAfter);
        $this->entityManager->persist($upcomingUnavailability);

        $this->entityManager->flush();

        $this->assertNotEmpty($this->unavailabilityRepository->findAll());

        $this->assertNotEmpty($this->unavailabilityRepository->findAllAndOrder());
        $this->assertNotEmpty($this->unavailabilityRepository->findByOrganiserAndOrder($organiser));
        $this->assertNotEmpty($this->unavailabilityRepository->findByGuestAndOrder($guest));
        $this->assertNotEmpty($this->unavailabilityRepository->findUnavailabilitiesByRoomByDates($room, $nextMonth, $theMonthAfter));
        $this->assertNotEmpty($this->unavailabilityRepository->findUnavailabilitiesByDates($nextMonth, $theMonthAfter));
        $this->assertNotEmpty($this->unavailabilityRepository->findUnavailabilitiesByRoom(1));
        $this->assertNotEmpty($this->unavailabilityRepository->findCurrentUnavailabilities());
        $this->assertSame('currentUnavailability', $this->unavailabilityRepository->findCurrentUnavailabilities()[0]->getObject());
        $this->assertNotEmpty($this->unavailabilityRepository->findUpcomingUnavailabilities());
        $this->assertSame('lastUnavailability', $this->unavailabilityRepository->findUpcomingUnavailabilities()[0]->getObject());
        $this->assertNotEmpty($this->unavailabilityRepository->findUpcomingUnavailabilitiesByOrganiser($organiser));
        $this->assertNotEmpty($this->unavailabilityRepository->findUpcomingUnavailabilitiesByRoom($room));
        $this->assertNotEmpty($this->unavailabilityRepository->findUpcomingUnavailabilitiesByGuest($guest));
        $this->assertNotEmpty($this->unavailabilityRepository->findLastUnavailability());
        $this->assertSame('lastUnavailability', $this->unavailabilityRepository->findLastUnavailability()->getObject());
        $this->assertNotEmpty($this->unavailabilityRepository->findLastMonthUnavailabilities());
        $this->assertSame('lastMonthUnavailability', $this->unavailabilityRepository->findLastMonthUnavailabilities()[0]->getObject());

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
