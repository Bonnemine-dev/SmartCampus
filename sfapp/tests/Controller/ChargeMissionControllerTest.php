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
    public function testSoumissionFiltre()
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
    public function testSoumissionRecherche()
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
    public function testReinitialisationFiltre()
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

    // // Test de l'ajout d'expérimentation
    // public function testAjoutExperimentation()
    // {
    //     $client = static::createClient();         
    //     $userRepository = static::getContainer()->get(UserRepository::class);         
    //     $testUser = $userRepository->findOneByUsername('chargemission');         
    //     $client->loginUser($testUser); 
    //     $container = $client->getContainer();         
    //     $router = $container->get(RouterInterface::class);    

    //     $url = $router->generate('app_charge_mission');         
    //     $client->request('GET', $url);

    //     $entityManager = $this->getContainer()->get('doctrine')->getManager();
    //     $expRepository = $entityManager->getRepository(Experimentation::class);
    //     $salleRepository = $entityManager->getRepository(Salle::class); // Correction ici
    //     $id = $salleRepository->nomSalleId('D001');

    //     $experimentation = $expRepository->findOneBy(['Salles' => ['id' => $id]]);

    //     if ($experimentation != null) {
    //         $expRepository->supprimerExperimentation('D001');
    //     }

    //     $this->client->request('GET', '/charge-de-mission/plan-experimentation/ajouter-salle/D001');

    //     $link = $this->client->getCrawler()->selectLink('Valider')->link();
    //     $this->client->click($link);

    //     $this->client->followRedirect();

    //     $this->assertResponseIsSuccessful();
    //     $this->assertSelectorTextContains('.alert-success', 'La salle D001 a été ajoutée au plan d\'expérimentation avec succès.');
    // }

    // // Test de la suppression d'expérimentation
    // public function testSupprimerExperimentation()
    // {
    //     $client = static::createClient();         
    //     $userRepository = static::getContainer()->get(UserRepository::class);         
    //     $testUser = $userRepository->findOneByUsername('chargemission');         
    //     $client->loginUser($testUser); 
    //     $container = $client->getContainer();         
    //     $router = $container->get(RouterInterface::class);    

    //     $url = $router->generate('app_charge_mission');         
    //     $client->request('GET', $url);

    //     $entityManager = $this->getContainer()->get('doctrine')->getManager();
    //     $expRepository = $entityManager->getRepository(Experimentation::class);
    //     $salleRepository = $entityManager->getRepository(Salle::class); // Correction ici
    //     $id = $salleRepository->nomSalleId('D001');

    //     $experimentation = $expRepository->findOneBy(['Salles' => ['id' => $id]]);

    //     if ($experimentation == null) {
    //         $expRepository->ajouterExperimentation('D001');
    //     }

    //     $this->client->request('GET', '/charge-de-mission/plan-experimentation/supprimer-salle/D001');

    //     $link = $this->client->getCrawler()->selectLink('Valider')->link();
    //     $this->client->click($link);

    //     $this->client->followRedirect();

    //     $this->assertResponseIsSuccessful();
    //     $this->assertSelectorTextContains('.alert-success', 'La salle D001 a été retirée du plan d\'expérimentation avec succès.');
    // }
}
