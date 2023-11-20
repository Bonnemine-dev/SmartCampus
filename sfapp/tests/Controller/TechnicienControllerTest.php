<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TechnicienControllerTest extends WebTestCase
{
    // Test des pages du contrôleur Technicien
    public function testPages(): void
    {
        $client = static::createClient();
        $client->request('GET', '/technicien/liste-souhaits');
            
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h4', "Systèmes d'acquisitions à installer");
        // Ajoutez d'autres assertions en fonction de votre logique de test spécifique à cette route
    }
}
