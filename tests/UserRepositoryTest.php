<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private $userRepository;

    /**
     * UserRepositoryTest constructor.
     */
    public function __construct()
    {
        parent::__construct();
        self::bootKernel();
        $this->userRepository = self::$container->get(UserRepository::class);
    }

    public function testFindActiveUsers()
    {
        $this->assertEquals(14, count($this->userRepository->findActiveUsers()));
    }

    // A faire sous forme de test fonctionnel
//    public function testFindActiveUsersExceptCurrent()
//    {
//        $this->assertEquals(13, count($this->userRepository->findActiveUsersExceptCurrent()));
//    }

    public function testFindLastMonthOrganiser()
    {
        $this->assertEquals(50, $this->userRepository->findLastMonthOrganiser()->getId());
    }

    public function testFindLastMonthMostInvited()
    {
        $this->assertEquals(56, $this->userRepository->findLastMonthMostInvited()->getId());
    }
}


