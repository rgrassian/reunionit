<?php

namespace App\DataFixtures;


use App\Entity\Room;
use App\Provider\FeaturesProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class RoomTestFixtures extends Fixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $room1 = new Room();
        $room1->setName('Salle 1');
        $room1->setCapacity(12);
        $room1->setFeatures(['Wifi', 'Vidéoprojecteur', 'Estrade']);
        $room1->setPicture('salle-1.jpg');
        $manager->persist($room1);

        $room2 = new Room();
        $room2->setName('Salle 2');
        $room2->setCapacity(4);
        $room2->setFeatures(['Wifi', 'Paperboard']);
        $room2->setPicture('salle-2.jpg');
        $manager->persist($room2);

        $room3 = new Room();
        $room3->setName('Salle 3');
        $room3->setCapacity(8);
        $room3->setFeatures(['Wifi', 'Vidéoprojecteur']);
        $room3->setPicture('salle-3.jpg');
        $manager->persist($room3);

        $manager->flush();
    }
}