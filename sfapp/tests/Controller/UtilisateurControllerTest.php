<?php

namespace App\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UtilisateurControllerTest extends WebTestCase
{
    // Test de la page principale
    public function testPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }

    // Test de soumission du formulaire de recherche
    public function testSoumissionRecherche()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $form = $crawler->filter('form[name="recherche_salle_form"]')->form();

        $formData = [
            'recherche_salle_form' => [
                'salle' => 'D001',
            ]
        ];

        $client->submit($form, $formData);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testRedirectConnexion()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $link = $client->getCrawler()->filter('a[href="/connexion"]')->link();

        $client->click($link);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testRedirectAccueil()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/utilisateur/D001');

        $link = $client->getCrawler()->filter('a[href="/"]')->link();

        $client->click($link);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}