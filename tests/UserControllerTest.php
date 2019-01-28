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
    }

    public function testNew()
    {
        // Test d'accès Admin
        $this->connectedUser = self::$container->get(UserRepository::class)->findOneById('48');
        $this->adminLogIn();

        $crawler = $this->client->request('GET', '/admin/nouvel-utilisateur.html');

        $rep = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $rep->getStatusCode());

        $this->assertSame('Nouvel utilisateur',
            $crawler->filter('h1')->text());

        $this->logout();

        // Test d'accès Employee
        $this->connectedUser = self::$container->get(UserRepository::class)->findOneById('52');
        $this->employeeLogIn();

        $crawler = $this->client->request('GET', '/admin/nouvel-utilisateur.html');
        $rep = $this->client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $rep->getStatusCode());

        $this->logout();

        // Test d'accès Guest
        $this->connectedUser = self::$container->get(UserRepository::class)->findOneById('61');
        $this->guestLogIn();

        $crawler = $this->client->request('GET', '/admin/nouvel-utilisateur.html');
        $rep = $this->client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $rep->getStatusCode());
    }

//    public function testSomething()
//    {
//        $crawler = $this->client->request('GET', '/');
//
//        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
//        $this->assertContains('Halte', $crawler->filter('h1')->text());
//    }

    private function adminLogIn()
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

    private function employeeLogIn()
    {
        $session = $this->client->getContainer()->get('session');
        $firewallName = 'main';
        // if you don't define multiple connected firewalls, the context defaults to the firewall name
        // See https://symfony.com/doc/current/reference/configuration/security.html#firewall-context
        $firewallContext = 'main';

        // you may need to use a different token class depending on your application.
        // for example, when using Guard authentication you must instantiate PostAuthenticationGuardToken
        $token = new UsernamePasswordToken($this->connectedUser, 'user', $firewallName, ['ROLE_EMPLOYEE']);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    private function guestLogIn()
    {
        $session = $this->client->getContainer()->get('session');
        $firewallName = 'main';
        // if you don't define multiple connected firewalls, the context defaults to the firewall name
        // See https://symfony.com/doc/current/reference/configuration/security.html#firewall-context
        $firewallContext = 'main';

        // you may need to use a different token class depending on your application.
        // for example, when using Guard authentication you must instantiate PostAuthenticationGuardToken
        $token = new UsernamePasswordToken($this->connectedUser, 'user', $firewallName, ['ROLE_GUEST']);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    public function logout()
    {
        $crawler = $this->client->request('GET', '/deconnexion');
        $crawler = $this->client->request('GET', '/');
    }
}
