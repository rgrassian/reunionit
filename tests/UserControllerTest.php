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
        $this->client->followRedirects();
        $this->userRepository = self::$container->get(UserRepository::class);
    }

    public function testNew()
    {
        // Test d'accès Admin
        $this->connectedUser = self::$container->get(UserRepository::class)->findOneById('48');
        $this->login('superadmin', ['ROLE_ADMIN']);

        $crawler = $this->client->request('GET', '/admin/nouvel-utilisateur.html');

        $rep = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $rep->getStatusCode());

        $this->assertSame('Nouvel utilisateur',
            $crawler->filter('h1')->text());

        $form = $crawler->filter('button[title="Enregistrer"]')->form();
        $form['user_admin[firstName]'] = 'test';
        $form['user_admin[lastName]'] = 'test';
        $form['user_admin[email]'] = 'bz@reunion.it';
        $form['user_admin[roles]'] = 'ROLE_EMPLOYEE';
        $this->client->submit($form);

        $this->assertSame('bz@reunion.it', $this->userRepository->findOneByLastName('test')->getEmail());

        $this->logout();

        // Test d'accès Employee
        $this->connectedUser = self::$container->get(UserRepository::class)->findOneById('52');
        $this->login('user', ['ROLE_EMPLOYEE']);

        $crawler = $this->client->request('GET', '/admin/nouvel-utilisateur.html');
        $rep = $this->client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $rep->getStatusCode());

        $this->logout();

        // Test d'accès Guest
        $this->connectedUser = self::$container->get(UserRepository::class)->findOneById('61');
        $this->login('user', ['ROLE_GUEST']);

        $crawler = $this->client->request('GET', '/admin/nouvel-utilisateur.html');
        $rep = $this->client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $rep->getStatusCode());
    }

    public function login($credentials, $role)
    {
        $session = $this->client->getContainer()->get('session');
        $firewallName = 'main';
        $firewallContext = 'main';

        $token = new UsernamePasswordToken($this->connectedUser, $credentials, $firewallName, $role);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    public function logout()
    {
        $crawler = $this->client->request('GET', '/deconnexion');
    }
}
