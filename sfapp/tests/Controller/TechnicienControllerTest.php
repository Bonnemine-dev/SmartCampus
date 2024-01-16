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

    public function testSoumissionFiltre()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByUsername('technicien');
        $client->loginUser($testUser);
        $container = $client->getContainer();
        $router = $container->get(RouterInterface::class);

        $url = $router->generate('gestion_sa');
        $crawler = $client->request('GET', $url);

        // Sélectionner le formulaire
        $form = $crawler->selectButton('Valider')->form();

        // Remplir le formulaire avec les données appropriées
        $formData = [
            'filtre_sa_form' => [
                'etat' => ['0'],
                'localisation' => ['stock', 'salle'],
            ],
        ];

        // Soumettre le formulaire
        $client->submit($form, $formData);

        // Vérifier la réponse
        $this->assertResponseIsSuccessful();
    }

    public function testSoumissionRecherche()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByUsername('technicien');
        $client->loginUser($testUser);
        $container = $client->getContainer();
        $router = $container->get(RouterInterface::class);

        $url = $router->generate('gestion_sa');
        $crawler = $client->request('GET', $url);

        $form = $crawler->filter('form[name="recherche_sa_form"]')->form();

        $formData = [
            'recherche_sa_form' => [
                'sa_nom' => 'ESPtest-001',
            ]
        ];

        $client->submit($form, $formData);

        $this->assertSelectorTextContains('h5', 'ESPtest-001');
        $this->assertResponseIsSuccessful();
    }

    public function testReinitialisationFiltre()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByUsername('technicien');
        $client->loginUser($testUser);
        $container = $client->getContainer();
        $router = $container->get(RouterInterface::class);

        $url = $router->generate('gestion_sa');
        $client->request('GET', $url);

        $crawler = $client->submitForm('Réinitialiser');

        $this->assertResponseIsSuccessful();

        $this->assertEquals([], $crawler->filter('input[name="filtreSaForm[etat][]"]:checked')->extract(['value']));
        $this->assertEquals([], $crawler->filter('input[name="filtreSaForm[localisation][]"]:checked')->extract(['value']));
    }
}



