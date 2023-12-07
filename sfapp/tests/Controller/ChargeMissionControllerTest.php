<?php

namespace App\Tests\Controller;

use App\Entity\Experimentation;
use App\Entity\Salle;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ChargeMissionControllerTest extends WebTestCase
{
    // Test de la page principale
    public function testPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/charge-de-mission/plan-experimentation');

        $this->assertResponseIsSuccessful();
    }

    // Test de soumission du formulaire de filtre
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

    // Test de soumission du formulaire de recherche
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

    // Test de la réinitialisation du formulaire de filtre
    public function testReinitialisationFiltre()
    {
        $client = static::createClient();

        $client->request('GET', '/charge-de-mission/plan-experimentation');
        $crawler = $client->submitForm('Réinitialiser');

        $this->assertResponseIsSuccessful();

        $this->assertEquals([], $crawler->filter('input[name="filtreSalleForm[etage][]"]:checked')->extract(['value']));
        $this->assertEquals([], $crawler->filter('input[name="filtreSalleForm[orientation][]"]:checked')->extract(['value']));
    }

    // Test de la redirection vers la page d'ajout d'expérimentation
    public function testRedirectionAjoutExperimentation()
    {
        $client = static::createClient();

        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $expRepository = $entityManager->getRepository(Experimentation::class);
        $salleRepository = $entityManager->getRepository(Salle::class);
        $id = $salleRepository->nomSalleId('D001');

        $experimentation = $expRepository->findOneBy(['Salle' => ['id' => $id]]);

        if ($experimentation != null) {
            $expRepository->supprimerExperimentation('D001');
        }

        $client->request('GET', '/charge-de-mission/plan-experimentation');

        $link = $client->getCrawler()->filter('a[href="plan-experimentation/ajouter-salle/D001"]')->link();
        $client->click($link);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

    }

    // Test de l'ajout d'expérimentation
    public function testAjoutExperimentation()
    {
        $client = static::createClient();

        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $expRepository = $entityManager->getRepository(Experimentation::class);
        $salleRepository = $entityManager->getRepository(Salle::class);
        $id = $salleRepository->nomSalleId('D001');

        $experimentation = $expRepository->findOneBy(['Salle' => ['id' => $id]]);

        if ($experimentation != null) {
            $expRepository->supprimerExperimentation('D001');
        }

        $client->request('GET', '/charge-de-mission/plan-experimentation/ajouter-salle/D001');

        $link = $client->getCrawler()->selectLink('Valider')->link();
        $client->click($link);

        $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertSelectorTextContains('.alert-success', 'La salle D001 a été ajoutée au plan d\'expérimentation avec succès.');

    }

    // Test de la redirection vers la page de suppression d'expérimentation
    public function testRedirectionSupprimerExperimentation()
    {
        $client = static::createClient();

        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $expRepository = $entityManager->getRepository(Experimentation::class);
        $salleRepository = $entityManager->getRepository(Salle::class);
        $id = $salleRepository->nomSalleId('D001');

        $experimentation = $expRepository->findOneBy(['Salle' => ['id' => $id]]);

        if ($experimentation == null) {
            $expRepository->ajouterExperimentation('D001');
        }

        $client->request('GET', '/charge-de-mission/plan-experimentation');

        $link = $client->getCrawler()->filter('a[href="plan-experimentation/supprimer-salle/D001"]')->link();
        $client->click($link);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

    }

    // Test de la suppression d'expérimentation
    public function testSupprimerExperimentation()
    {
        $client = static::createClient();

        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $expRepository = $entityManager->getRepository(Experimentation::class);
        $salleRepository = $entityManager->getRepository(Salle::class);
        $id = $salleRepository->nomSalleId('D001');

        $experimentation = $expRepository->findOneBy(['Salle' => ['id' => $id]]);

        if ($experimentation == null) {
            $expRepository->ajouterExperimentation('D001');
        }

        $client->request('GET', '/charge-de-mission/plan-experimentation/supprimer-salle/D001');

        $link = $client->getCrawler()->selectLink('Valider')->link();
        $client->click($link);

        $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertSelectorTextContains('.alert-success', 'La salle D001 a été retirée du plan d\'expérimentation avec succès.');
    }

}
