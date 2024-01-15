<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Component\Routing\RouterInterface;
use App\Entity\Experimentation;
use App\Entity\Salle;
use App\Repository\SalleRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class ChargeMissionControllerTest extends WebTestCase
{
    private $client;

    //Test de la page principale
    public function testPages(): void
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

        $url = $router->generate('app_modifier_chargemission');         
        $client->request('GET', $url);         
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $url = $router->generate('cm_tableau_de_bord');         
        $client->request('GET', $url);         
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $url = $router->generate('liste_salles');         
        $client->request('GET', $url);         
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    // Test de soumission du formulaire de filtre
    public function testSoumissionFiltrePE()
    {
        $client = static::createClient();         
        $userRepository = static::getContainer()->get(UserRepository::class);         
        $testUser = $userRepository->findOneByUsername('chargemission');         
        $client->loginUser($testUser); 
        $container = $client->getContainer();         
        $router = $container->get(RouterInterface::class);    

        $url = $router->generate('app_charge_mission');         
        $crawler = $client->request('GET', $url);    

        // Sélectionner le formulaire
        $form = $crawler->selectButton('Valider')->form();

        // Remplir le formulaire avec les données appropriées
        $formData = [
            'filtre_salle_form' => [
                'etage' => ['0'],
                'orientation' => ['nord', 'sud'],
                'ordinateurs' => '1',
                'sa' => '3',
            ],
        ];

        // Soumettre le formulaire
        $client->submit($form, $formData);

        // Vérifier la réponse
        $this->assertResponseIsSuccessful();
    }

    //Test de soumission du formulaire de recherche
    public function testSoumissionRecherchePE()
    {
        $client = static::createClient();         
        $userRepository = static::getContainer()->get(UserRepository::class);         
        $testUser = $userRepository->findOneByUsername('chargemission');         
        $client->loginUser($testUser); 
        $container = $client->getContainer();         
        $router = $container->get(RouterInterface::class);    

        $url = $router->generate('app_charge_mission');         
        $crawler = $client->request('GET', $url);    

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
    public function testReinitialisationFiltrePE()
    {
        $client = static::createClient();         
        $userRepository = static::getContainer()->get(UserRepository::class);         
        $testUser = $userRepository->findOneByUsername('chargemission');         
        $client->loginUser($testUser); 
        $container = $client->getContainer();         
        $router = $container->get(RouterInterface::class);    

        $url = $router->generate('app_charge_mission');         
        $client->request('GET', $url);   
        
        $crawler = $client->submitForm('Réinitialiser');

        $this->assertResponseIsSuccessful();

        $this->assertEquals([], $crawler->filter('input[name="filtreSalleForm[etage][]"]:checked')->extract(['value']));
        $this->assertEquals([], $crawler->filter('input[name="filtreSalleForm[orientation][]"]:checked')->extract(['value']));
    }

    // public function testBoutonDetail()
    // {
    //     $client = static::createClient();         
    //     $userRepository = static::getContainer()->get(UserRepository::class);         
    //     $testUser = $userRepository->findOneByUsername('chargemission');         
    //     $client->loginUser($testUser); 
    //     $container = $client->getContainer();         
    //     $router = $container->get(RouterInterface::class);

    //     $url = $router->generate('liste_salles');         
    //     $crawler = $client->request('GET', $url);    

    //     $boutonDetail = $crawler->selectLink('Détails')->link();

    //     $client->click($boutonDetail);
        
    //     $this->assertResponseIsSuccessful();
    // }

    // Test de soumission du formulaire de filtre
    public function testSoumissionFiltreSalle()
    {
        $client = static::createClient();         
        $userRepository = static::getContainer()->get(UserRepository::class);         
        $testUser = $userRepository->findOneByUsername('chargemission');         
        $client->loginUser($testUser); 
        $container = $client->getContainer();         
        $router = $container->get(RouterInterface::class);    

        $url = $router->generate('liste_salles');         
        $crawler = $client->request('GET', $url);    

        // Sélectionner le formulaire
        $form = $crawler->selectButton('Valider')->form();

        // Remplir le formulaire avec les données appropriées
        $formData = [
            'filtre_salle_form' => [
                'etage' => ['0'],
                'orientation' => ['nord', 'sud'],
                'ordinateurs' => '1',
                'sa' => '3',
            ],
        ];

        // Soumettre le formulaire
        $client->submit($form, $formData);

        // Vérifier la réponse
        $this->assertResponseIsSuccessful();
    }

    //Test de soumission du formulaire de recherche
    public function testSoumissionRechercheSalle()
    {
        $client = static::createClient();         
        $userRepository = static::getContainer()->get(UserRepository::class);         
        $testUser = $userRepository->findOneByUsername('chargemission');         
        $client->loginUser($testUser); 
        $container = $client->getContainer();         
        $router = $container->get(RouterInterface::class);    

        $url = $router->generate('liste_salles');         
        $crawler = $client->request('GET', $url);    

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
    public function testReinitialisationFiltreSalle()
    {
        $client = static::createClient();         
        $userRepository = static::getContainer()->get(UserRepository::class);         
        $testUser = $userRepository->findOneByUsername('chargemission');         
        $client->loginUser($testUser); 
        $container = $client->getContainer();         
        $router = $container->get(RouterInterface::class);    

        $url = $router->generate('liste_salles');         
        $client->request('GET', $url);   
        
        $crawler = $client->submitForm('Réinitialiser');

        $this->assertResponseIsSuccessful();

        $this->assertEquals([], $crawler->filter('input[name="filtreSalleForm[etage][]"]:checked')->extract(['value']));
        $this->assertEquals([], $crawler->filter('input[name="filtreSalleForm[orientation][]"]:checked')->extract(['value']));
    }
}
