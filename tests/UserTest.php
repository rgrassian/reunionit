<?php

namespace App\Tests;

use App\Entity\Unavailability;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    protected $object;

    protected function setUp()
    {
        $this->object = new User();
    }

    public function testUser()
    {
        $this->assertNull($this->object->getId());

        $this->object->setFirstName("prénom");
        $this->assertEquals("prénom", $this->object->getFirstName());

        $this->object->setLastName("nom");
        $this->assertEquals("nom", $this->object->getLastName());

        $this->object->setEmail("prenom.nom@reunion.it");
        $this->assertEquals("prenom.nom@reunion.it", $this->object->getEmail());

        $this->object->setPassword("password");
        $this->assertEquals("password", $this->object->getPassword());

        $this->object->setRoles(['ROLE_ADMIN']);
        $this->assertEquals(['ROLE_ADMIN'], $this->object->getRoles());

        $this->object->addRole('ROLE_GUEST');
        $this->assertEquals(['ROLE_ADMIN', 'ROLE_GUEST'], $this->object->getRoles());

        $this->object->addRole('ROLE_GUEST');
        $this->assertEquals(['ROLE_ADMIN', 'ROLE_GUEST'], $this->object->getRoles());

        $this->assertTrue($this->object->hasRole('ROLE_ADMIN'));

        $this->assertFalse($this->object->hasRole('ROLE_EMPLOYEE'));

        $unavailability = new Unavailability();
        $unavailability->setObject('objet');
        $this->object->addUnavailability($unavailability);
        foreach ($this->object->getUnavailabilities() as $unavailability) {
            $objects[] = $unavailability->getObject();
        }
        $this->assertEquals(['objet'], $objects);

    }
}
