<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConnexionControllerTest extends WebTestCase
{
    // Test de la page principale
    public function testPage(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }

    // Test de la redirection vers la page de Chargé de mission
    public function testRedirectionChargeMission(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $link = $client->getCrawler()->selectLink('Chargé de mission')->link();
        $client->click($link);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    // Test de la redirection vers la page de Technicien
    public function testRedirectionTechnicien(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $link = $client->getCrawler()->selectLink('Technicien')->link();
        $client->click($link);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
