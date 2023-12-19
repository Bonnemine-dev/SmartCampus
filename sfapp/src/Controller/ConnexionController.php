<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class ConnexionController extends AbstractController
{
    // La fonction index représente la page d'accueil de l'application.
    // Elle rend une vue Twig spécifique avec le nom du contrôleur en tant que paramètre.
    #[Route('/', name: 'app_connexion')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        if ($error){
            $this->addFlash('error', 'Connexion impossible : mot de passe incorrects');
        }

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        
        return $this->render('connexion/index.html.twig', [
            'controller_name' => 'LoginController',
            'last_username' => $lastUsername,
            'error' => $error,
          ]);
    }

    #[Route('/logout', name: 'app_deconnexion')]
    public function logout()
    {
        // The code is never executed, Symfony redirects before reaching this method
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }
}
