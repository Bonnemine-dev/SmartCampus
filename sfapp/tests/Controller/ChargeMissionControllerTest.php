<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ChargeMissionControllerTest extends WebTestCase
{
    public function testPage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/charge-de-mission/plan-experimentation');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Menu');
        $this->assertSelectorTextContains('h4', 'Liste des salles');
        $this->assertSelectorExists('html div.salle-infos');
    }

    public function testSoumissionFiltre()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/charge-de-mission/plan-experimentation');

        $form = $crawler->selectButton('Valider')->form();

        $formData = [
            'filtre_salle_form' => [
                'etage' => ['0'],
                'orientation' => ['nord', 'sud'],
                'ordinateurs' => '1',
                'sa' => '3',
            ],
        ];

        $client->submit($form, $formData);

        $this->assertResponseIsSuccessful();
    }

    public function testSoumissionRecherche()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/charge-de-mission/plan-experimentation');

        $form = $crawler->filter('form[name="recherche_salle_form"]')->form();

        $formData = [
            'recherche_salle_form' => [
                'batiment' => '',
                'salle' => 'D201',
            ]
        ];

        $client->submit($form, $formData);

        $this->assertResponseIsSuccessful();
    }

    public function testReinitialisationFiltre()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/charge-de-mission/plan-experimentation');
        $crawler = $client->submitForm('Réinitialiser');

        $this->assertResponseIsSuccessful();

        $this->assertEquals([], $crawler->filter('input[name="filtreSalleForm[etage][]"]:checked')->extract(['value']));
        $this->assertEquals([], $crawler->filter('input[name="filtreSalleForm[orientation][]"]:checked')->extract(['value']));
    }

    public function testRedirectionAjoutExperimentation()
    {
        $client = static::createClient();

        $client->request('GET', '/charge-de-mission/plan-experimentation');

        $link = $client->getCrawler()->filter('a[href="plan-experimentation/ajouter-salle/D001"]')->link();
        $client->click($link);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

    }

    public function testAjoutExperimentation()
    {
        $client = static::createClient();

        $client->request('GET', '/charge-de-mission/plan-experimentation/ajouter-salle/D001');

        $link = $client->getCrawler()->selectLink('Valider')->link();
        $client->click($link);

        $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertSelectorTextContains('.alert-success', 'La salle D001 a été ajoutée au plan d\'expérimentation avec succès.');

    }

    public function testRedirectionSupprimerExperimentation()
    {
        $client = static::createClient();

        $client->request('GET', '/charge-de-mission/plan-experimentation');

        $link = $client->getCrawler()->filter('a[href="plan-experimentation/supprimer-salle/D001"]')->link();
        $client->click($link);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

    }

    public function testSupprimerExperimentation()
    {
        $client = static::createClient();

        $client->request('GET', '/charge-de-mission/plan-experimentation/supprimer-salle/D001');

        $link = $client->getCrawler()->selectLink('Valider')->link();
        $client->click($link);

        $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertSelectorTextContains('.alert-success', 'La salle D001 a été retirée du plan d\'expérimentation avec succès.');
    }

}
