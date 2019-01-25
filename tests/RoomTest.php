<?php

namespace App\Tests;

use App\Entity\Room;
use PHPUnit\Framework\TestCase;

class RoomTest extends TestCase
{
    protected $object;

    protected function setUp()
    {
        $this->object = new Room();
    }

    public function testRoom()
    {
        $this->assertNull($this->object->getId());

        $this->object->setName("nom");
        $this->assertEquals("nom", $this->object->getName());

        $this->object->setCapacity(10);
        $this->assertEquals(10, $this->object->getCapacity());

    }
}
