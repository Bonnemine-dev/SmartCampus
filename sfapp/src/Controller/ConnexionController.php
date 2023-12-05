<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConnexionController extends AbstractController
{
    // La fonction index représente la page d'accueil de l'application.
    // Elle rend une vue Twig spécifique avec le nom du contrôleur en tant que paramètre.
    #[Route('/', name: 'app_connexion')]
    public function index(): Response
    {
        // Rend la vue 'connexion/ajouter-sa.html.twig' avec des données supplémentaires, ici le nom du contrôleur.
        return $this->render('connexion/ajouter-sa.html.twig', [
            'controller_name' => 'ConnexionController',
        ]);
    }
}
