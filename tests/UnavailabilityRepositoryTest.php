<?php

namespace App\Tests;

use App\Repository\UnavailabilityRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UnavailabilityRepositoryTest extends KernelTestCase
{
    private $unavailabilityRepository;

    /**
     * UserRepositoryTest constructor.
     */
    public function __construct()
    {
        parent::__construct();
        self::bootKernel();
        $this->unavailabilityRepository = self::$container->get(UnavailabilityRepository::class);
    }

    public function testFindLastMonthUnavailabilities()
    {
        $result = $this->unavailabilityRepository->findLastMonthUnavailabilities();

        foreach ($result as $unavailability) {
            $Ids[] = $unavailability->getId();
        }

        $this->assertEquals([4], $Ids);
    }

    public function testFindUpcomingUnavailabilitiesByOrganiser()
    {

    }
}
