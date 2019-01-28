<?php

namespace App\Tests;

use App\Repository\UserRepository;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class UserControllerTest extends WebTestCase
{
    /**
     * User $connecteduser;
     */
    private $connectedUser;

    /**
     * @var Client
     */
    private $client = null;
    private $userRepository;

    public function setUp()
    {
        static::bootKernel();
        $this->client = static::createClient();
        $this->userRepository = self::$container->get(UserRepository::class);
        $this->connectedUser = self::$container->get(UserRepository::class)->findOneById('48');
    }

//    public function testSomething()
//    {
//        $crawler = $this->client->request('GET', '/');
//
//        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
//        $this->assertContains('Halte', $crawler->filter('h1')->text());
//    }


}
