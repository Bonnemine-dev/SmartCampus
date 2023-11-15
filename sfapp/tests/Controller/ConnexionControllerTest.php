<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConnexionControllerTest extends WebTestCase
{
    public function testPage(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Smart Campus.');
    }

    public function testRessourcesStatiques(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('link[rel="stylesheet"][href="/style/style.css"]');
        $this->assertSelectorExists('img[alt="logo"][src="/img/logo.jpg"]');
    }

    public function testRedirectionChargeMission(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $link = $client->getCrawler()->selectLink('Chargé de mission')->link();
        $client->click($link);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testRedirectionTechnicien(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $link = $client->getCrawler()->selectLink('Technicien')->link();
        $client->click($link);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
