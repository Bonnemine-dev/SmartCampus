<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @class ConnexionController
 * Contrôleur pour gérer les actions de connexion et de déconnexion des utilisateurs.
 * @extends AbstractController
 */
class ConnexionController extends AbstractController
{
    /**
     * Gère la page de connexion de l'application.
     * Récupère les éventuelles erreurs de connexion et affiche la vue de connexion.
     * @param AuthenticationUtils $authenticationUtils Utilitaire pour gérer l'authentification.
     * @return Response La réponse HTTP avec la vue de la page de connexion.
     * @Route('/connexion', name: 'app_connexion')
     */
    #[Route('/connexion', name: 'app_connexion')]
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

    /**
     * Gère la déconnexion de l'utilisateur.
     * Le code de cette méthode n'est jamais exécuté car Symfony redirige avant son atteinte.
     * @Route('/logout', name: 'app_deconnexion')
     * @return void
     */
    #[Route('/logout', name: 'app_deconnexion')]
    public function logout(): void
    {
        // The code is never executed, Symfony redirects before reaching this method
    }
}
