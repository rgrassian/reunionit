<?php

namespace App\Tests;

use App\Repository\UserRepository;
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

    public function testFindActiveUsersExceptCurrent()
    {
        $this->logIn();
        $crawler = $this->client->request('GET', '/nouvelle-reservation.html');

        /**
         * @var Response $rep
         */
        $rep = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $rep->getStatusCode());
        $this->assertNotContains(strval($this->connectedUser->getId()),
            $crawler->filter('option[value=' . $this->connectedUser->getId() . ']')->text());
    }

    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');
        $firewallName = 'main';
        // if you don't define multiple connected firewalls, the context defaults to the firewall name
        // See https://symfony.com/doc/current/reference/configuration/security.html#firewall-context
        $firewallContext = 'main';

        // you may need to use a different token class depending on your application.
        // for example, when using Guard authentication you must instantiate PostAuthenticationGuardToken
        $token = new UsernamePasswordToken($this->connectedUser, 'superadmin', $firewallName, ['ROLE_ADMIN']);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);

    }
}
