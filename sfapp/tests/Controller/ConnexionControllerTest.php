<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConnexionControllerTest extends WebTestCase
{
    // Test de la page principale
    public function testPage(): void
    {
        $client = static::createClient();

        $client->request('GET', '/connexion');

        $this->assertResponseIsSuccessful();
    }

    // Test de la redirection vers la page de Chargé de mission
    public function testRedirectionChargeMission()     
    {         
        $client = static::createClient();         

        $userRepository = static::getContainer()->get(UserRepository::class);         
        $testUser = $userRepository->findOneByUsername('chargemission');         
        $client->loginUser($testUser);   

        $container = $client->getContainer();         
        $router = $container->get(RouterInterface::class);         
        $url = $router->generate('app_charge_mission');         
        $client->request('GET', $url);         

        $this->assertEquals(200, $client->getResponse()->getStatusCode());     
    }
    
    // Test de la redirection vers la page de Technicien
    public function testRedirectionTechnicien()     
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
    }
}


