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
                'batiment' => '2',
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
}
