<?php
/**
 * Created by PhpStorm.
 * User: Etudiant
 * Date: 30/01/2019
 * Time: 11:43
 */

namespace App\Tests;


use App\Controller\UnavailabilityController;
use App\Entity\Room;
use App\Entity\Unavailability;
use App\Entity\User;
use App\Service\EmailManager;
use App\Service\UnavailabilityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UnavailabilityManagerTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    private $unavailabilityRepository;

    private $unavailabilityController;

    private $unavailabilityManager;

    private $emailManager;

    private $userRepository;

    private $roomRepository;

    private $mailer;

    public function setUp()
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
        $this->emailManager = self::$container->get(EmailManager::class);
        $this->mailer = self::$container->get(\Swift_Mailer::class);

    }

    public function testDeleteUpcomingUnavailabilitiesByOrganiser()
    {
        $organiser = $this->userRepository->findOneBy(['id' => 1]);

        $this->unavailabilityManager->deleteUpcomingUnavailabilitiesByOrganiser($this->emailManager, $organiser, $this->mailer);

        $this->assertEmpty($this->unavailabilityRepository->findUpcomingUnavailabilitiesByOrganiser($organiser));

        $room = $this->roomRepository->findOneById(['id' => 1 ]);

        $today = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/m/d 00:00:00'));
        $m1 = ($today->format('n') + 1) % 12;
        $nextMonth = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/'.$m1.'/01 00:00:00'));
        $m2 = ($m1 + 1) % 12;
        $theMonthAfter = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/'.$m2.'/01 00:00:00'));

        $upcomingUnavailability = new Unavailability();
        $upcomingUnavailability->setObject('test1')
            ->setOrganiser($organiser)
            ->setRoom($room)
            ->setType(0)
            ->setStartDate($nextMonth)
            ->setEndDate($theMonthAfter);

        $this->entityManager->persist($upcomingUnavailability);
        $this->entityManager->flush();

        $this->assertNotEmpty($this->unavailabilityRepository->findUpcomingUnavailabilitiesByOrganiser($organiser));

        $this->unavailabilityManager->deleteUpcomingUnavailabilitiesByOrganiser($this->emailManager, $organiser, $this->mailer);

        $this->assertEmpty($this->unavailabilityRepository->findUpcomingUnavailabilitiesByOrganiser($organiser));
    }

    public function testDeleteUpcomingUnavailabilitiesByRoom()
    {
        $room = $this->roomRepository->findOneBy(['id' => 2]);

        $this->unavailabilityManager->deleteUpcomingUnavailabilitiesByRoom($this->emailManager, $room, $this->mailer);

        $this->assertEmpty($this->unavailabilityRepository->findUpcomingUnavailabilitiesByRoom($room));

        $organiser = $this->userRepository->findOneById(['id' => 2 ]);

        $today = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/m/d 00:00:00'));
        $m1 = ($today->format('n') + 1) % 12;
        $nextMonth = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/'.$m1.'/01 00:00:00'));
        $m2 = ($m1 + 1) % 12;
        $theMonthAfter = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/'.$m2.'/01 00:00:00'));

        $upcomingUnavailability = new Unavailability();
        $upcomingUnavailability->setObject('test2')
            ->setOrganiser($organiser)
            ->setRoom($room)
            ->setType(0)
            ->setStartDate($nextMonth)
            ->setEndDate($theMonthAfter);

        $this->entityManager->persist($upcomingUnavailability);
        $this->entityManager->flush();

        $this->assertNotEmpty($this->unavailabilityRepository->findUpcomingUnavailabilitiesByRoom($room));

        $this->unavailabilityManager->deleteUpcomingUnavailabilitiesByRoom($this->emailManager, $room, $this->mailer);

        $this->assertEmpty($this->unavailabilityRepository->findUpcomingUnavailabilitiesByRoom($room));
    }

}