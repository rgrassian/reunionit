<?php

namespace App\Tests;

use App\Repository\UserRepository;
use App\Service\UnavailabilityManager;
use Doctrine\ORM\EntityManager;
use PHPUnit\Runner\Exception;
use Proxies\__CG__\App\Entity\Unavailability;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class UserControllerTest extends WebTestCase
{
    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    /**
     * User $connecteduser;
     */
    private $connectedUser;

    /**
     * @var Client
     */
    private $client = null;
    /**
     * @var UserRepository $userRepository
     */
    private $userRepository;

    public function setUp()
    {
        self::bootKernel();
        $container = self::$container;
        $this->entityManager = $container
            ->get('doctrine')
            ->getManager();
        $this->client = static::createClient();
        $this->userRepository = self::$container->get(UserRepository::class);
    }

    public function testNew()
    {
        // Test d'accès Admin
        $this->connectedUser = $this->userRepository->findOneById('1');
        $this->login('adminadmin', ['ROLE_ADMIN']);

        $crawler = $this->client->request('GET', '/admin/nouvel-utilisateur.html');

        $rep = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $rep->getStatusCode());

        $this->assertSame('Nouvel utilisateur',
            $crawler->filter('h1')->text());

        $form = $crawler->filter('button[title="Enregistrer"]')->form();
        $form['user_admin[firstName]'] = 'testFirstName';
        $form['user_admin[lastName]'] = 'testLastName';
        $form['user_admin[email]'] = 'test.email@reunion.it';
        $form['user_admin[roles]'] = 'ROLE_EMPLOYEE';
        $this->client->submit($form);

        $crawler = $this->client->followRedirect();

        $user = $this->userRepository->findOneByLastName('testLastName');
        $this->assertSame('test.email@reunion.it', $user->getEmail());
        $this->assertSame('Utilisateurs', $crawler->filter('h1')->text());

        $this->logout();

        $this->assertEquals(0, $crawler->filter('html:contains("Nos locaux :")')->count());

        $this->connectedUser = $this->userRepository->findOneByEmail('test.email@reunion.it');
        $this->login('user', ['ROLE_EMPLOYEE']);

        $crawler = $this->client->request('GET', '/');

        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nos locaux :")')->count());

        // Test d'accès Employee
        $this->connectedUser = $this->userRepository->findOneById('3');
        $this->login('user', ['ROLE_EMPLOYEE']);

        $crawler = $this->client->request('GET', '/admin/nouvel-utilisateur.html');
        $rep = $this->client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $rep->getStatusCode());

        $this->logout();

        // Test d'accès Guest
        $this->connectedUser = $this->userRepository->findOneById('6');
        $this->login('user', ['ROLE_GUEST']);

        $crawler = $this->client->request('GET', '/admin/nouvel-utilisateur.html');
        $rep = $this->client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $rep->getStatusCode());
    }

    public function testShow()
    {
        $this->connectedUser = $this->userRepository->findOneById('6');
        $this->login('user', ['ROLE_GUEST']);

        $crawler = $this->client->request('GET', '/utilisateur-1.html');
        $rep = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $rep->getStatusCode());
        $this->assertSame('Jacques Grenier', $crawler->filter('h1')->text());
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
        $crawler = $this->client->followRedirect();
    }
}
