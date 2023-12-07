<?php

namespace App\Tests\Controller;

use App\Entity\Experimentation;
use App\Entity\Salle;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TechnicienControllerTest extends WebTestCase
{
    // Test des pages du contrôleur Technicien
    public function testPages(): void
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

        $client->request('GET', '/technicien/liste-souhaits');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h5', 'D001');
    }
}
