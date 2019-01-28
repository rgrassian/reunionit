<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class UserRepositoryTest extends WebTestCase
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
        $this->assertEquals(12, count($this->userRepository->findActiveUsers()));
    }

    public function testFindLastMonthOrganiser()
    {
        $this->assertEquals(50, $this->userRepository->findLastMonthOrganiser()->getId());
    }

    public function testFindLastMonthMostInvited()
    {
        $this->assertEquals(56, $this->userRepository->findLastMonthMostInvited()->getId());
    }
}


