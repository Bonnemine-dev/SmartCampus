<?php

namespace App\Tests\Controller;

use App\Entity\Experimentation;
use App\Entity\Salle;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class TechnicienControllerTest extends WebTestCase
{
    // Test des pages du contrôleur Technicien
    public function testPages(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByUsername('technicien');
        $client->loginUser($testUser);
        $container = $client->getContainer();
        $router = $container->get(RouterInterface::class);

        $url = $router->generate('app_technicien');
        $client->request('GET', $url);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $url = $router->generate('app_modifier');
        $client->request('GET', $url);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $url = $router->generate('gestion_sa');
        $client->request('GET', $url);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}



