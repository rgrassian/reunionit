<?php

namespace App\Tests;

use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RoomRepositoryTest extends KernelTestCase
{
    public function testFindMaxCapacityRoom()
    {
        self::bootKernel();
        $roomRepository = self::$container->get(RoomRepository::class);

        $this->assertEquals(12, $roomRepository->findMaxCapacityRoom());
    }
}
