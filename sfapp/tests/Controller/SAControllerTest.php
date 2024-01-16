<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Component\Routing\RouterInterface;
use App\Entity\Experimentation;
use App\Entity\Salle;
use App\Repository\SalleRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class SAControllerTest extends WebTestCase
{
    private $client;
    
    public function testAjoutSA(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByUsername('technicien');
        $client->loginUser($testUser);
        $container = $client->getContainer();
        $router = $container->get(RouterInterface::class);

        $url = $router->generate('ajout_sa');
        $crawler = $client->request('GET', $url);

        $form = $crawler->selectButton('Ajouter')->form();

        $formData = [
            'ajout_sa_form' => [
                'nom' => 'ESPtest-000',
            ]
        ];

        $client->submit($form, $formData);
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testSupprSA(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByUsername('technicien');
        $client->loginUser($testUser);
        
        $crawler = $client->request('GET', '/technicien/gestion-sa/supprimer-sa/ESPtest-000');
        $link = $crawler->selectLink('Valider')->link();

        $client->click($link);
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-success', 'Le système d\'acquisition ESPtest-000 à été supprimé avec succès.');
    }
}
