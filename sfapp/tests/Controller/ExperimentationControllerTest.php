<?php

namespace App\Tests\Controller;

use App\Entity\Experimentation;
use App\Entity\SA;
use App\Entity\Salle;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class ExperimentationControllerTest extends WebTestCase
{
    public function testDemandeRetraitExperimentationInstallee()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByUsername('chargemission');
        $client->loginUser($testUser);
        $container = $client->getContainer();
        $router = $container->get(RouterInterface::class);

        $url = $router->generate('app_charge_mission');
        $client->request('GET', $url);

        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $expRepository = $entityManager->getRepository(Experimentation::class);
        $salleRepository = $entityManager->getRepository(Salle::class);
        $id = $salleRepository->nomSalleId('F004');

        $experimentation = $expRepository->findOneBy(['Salles' => ['id' => $id]]);

        if ($experimentation == null) {
            $expRepository->ajouterExperimentation('F004');
        }

        $client->request('GET', '/charge-de-mission/plan-experimentation/supprimer-salle/F004');

        $link = $client->getCrawler()->selectLink('Valider')->link();
        $client->click($link);

        $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertSelectorTextContains('.alert-success', 'La demande de retrait de la salle F004 a été envoyée avec succès.');
    }


    public function testRetraitExperimentationInstallee()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByUsername('technicien');
        $client->loginUser($testUser);
        $container = $client->getContainer();
        $router = $container->get(RouterInterface::class);

        $url = $router->generate('app_technicien');
        $client->request('GET', $url);

        $client->request('GET', 'modifier-etat-experimentation/retiree/F004');

        $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertSelectorTextContains('.alert-success', 'L\'état de l\'expérimentation de la salle F004 a été modifié avec succès.');
    }

    public function testDemandeInstallationExperimentation()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByUsername('chargemission');
        $client->loginUser($testUser);
        $container = $client->getContainer();
        $router = $container->get(RouterInterface::class);

        $url = $router->generate('app_charge_mission');
        $client->request('GET', $url);

        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $expRepository = $entityManager->getRepository(Experimentation::class);
        $salleRepository = $entityManager->getRepository(Salle::class);
        $id = $salleRepository->nomSalleId('F004');

        $experimentation = $expRepository->findOneBy(['Salles' => ['id' => $id]]);

        if ($experimentation == null) {
            $expRepository->ajouterExperimentation('F004');
        }

        $client->request('GET', '/charge-de-mission/plan-experimentation/ajouter-salle/F004');

        $link = $client->getCrawler()->selectLink('Valider')->link();
        $client->click($link);

        $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertSelectorTextContains('.alert-success', 'La salle F004 a été ajoutée au plan d\'expérimentation avec succès.');
    }


    public function testInstallationExperimentationInstallee()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByUsername('technicien');
        $client->loginUser($testUser);
        $container = $client->getContainer();
        $router = $container->get(RouterInterface::class);

        $url = $router->generate('app_technicien');
        $client->request('GET', $url);

        $client->request('GET', 'modifier-etat-experimentation/installee/F004');

        $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertSelectorTextContains('.alert-success', 'L\'état de l\'expérimentation de la salle F004 a été modifié avec succès.');
    }

    public function testRetraitExperimentationPasInstallee()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByUsername('chargemission');
        $client->loginUser($testUser);
        $container = $client->getContainer();
        $router = $container->get(RouterInterface::class);

        $url = $router->generate('app_charge_mission');
        $client->request('GET', $url);

        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $expRepository = $entityManager->getRepository(Experimentation::class);
        $salleRepository = $entityManager->getRepository(Salle::class);
        $id = $salleRepository->nomSalleId('F003');

        $entityManager->getRepository(SA::class)->ajoutSA('ESPtest-9999');
        $experimentation = $expRepository->findOneBy(['Salles' => ['id' => $id]]);

        $client->request('GET', '/charge-de-mission/plan-experimentation/ajouter-salle/F003');

        $link = $client->getCrawler()->selectLink('Valider')->link();
        $client->click($link);

        $client->followRedirect();

        $client->request('GET', '/charge-de-mission/plan-experimentation/supprimer-salle/F003');

        $link = $client->getCrawler()->selectLink('Valider')->link();
        $client->click($link);

        $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertSelectorTextContains('.alert-success', 'La salle F003 a été retirée du plan d\'expérimentation avec succès');
    }

}
