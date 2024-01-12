<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\FiltreSAFormType;
use App\Form\RechercheSAFormType;
use App\Form\UserType;
use App\Repository\ExperimentationRepository;
use App\Repository\SARepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @class TechnicienController
 * Contrôleur pour gérer les actions liées aux techniciens.
 * @extends AbstractController
 */
class TechnicienController extends AbstractController
{
    /**
     * Affiche la liste des souhaits d'expérimentations pour le technicien.
     * @param ExperimentationRepository $repository Repository pour interagir avec les données d'expérimentation.
     * @return Response Réponse HTTP avec la vue de la liste des souhaits d'expérimentations.
     * @Route('/technicien/liste-souhaits', name: 'app_technicien')
     */
    #[Route('/technicien/liste-souhaits', name: 'app_technicien')]
    public function index(ExperimentationRepository $repository): Response
    {

        // Récupère les expérimentations du repository.
        $experimentations = $repository->trouveExperimentationDemandeInstallation();
        $experimentations = $repository->triExperimentation($experimentations);

        // Rend la vue avec la liste des expérimentations.
        return $this->render('technicien/liste-souhaits.html.twig', [
            'experimentations' => $experimentations
        ]);
    }

    /**
     * Gère la page de gestion des systèmes d'acquisition (SA) pour le technicien.
     * @param Request $request Requête HTTP entrante.
     * @param SARepository $saRepository Repository pour les systèmes d'acquisition.
     * @return Response Réponse HTTP avec la vue de gestion des SA.
     * @Route('/technicien/gestion-sa', name: 'gestion_sa')
     */
    #[Route('/technicien/gestion-sa', name: 'gestion_sa')]
    public function gestion_sa(Request $request , SARepository $saRepository): Response
    {
        $filtreSAForm = $this->createForm(FiltreSAFormType::class);
        $filtreSAForm->handleRequest($request);
        $rechercheSAForm = $this->createForm(RechercheSAFormType::class);
        $rechercheSAForm->handleRequest($request);

        if ($filtreSAForm->isSubmitted() && $filtreSAForm->isValid()) {
            $dataFiltre = $filtreSAForm->getData();
            // Extraire les données et les utiliser pour filtrer les salles
            $liste_sa = $saRepository->filtrerSAGestionSA(
                $dataFiltre['etat'] ?? null,
                $dataFiltre['localisation'] ?? null,
            );
            if(empty($liste_sa))$this->addFlash('error', "Votre recherche ne correspond ni a un système d'acquisition ni a une salle");
        }
        else if ($rechercheSAForm->isSubmitted() && $rechercheSAForm->isValid()) {
            $dataRecherche = $rechercheSAForm->getData();
            // Extraire les données et les utiliser pour rechercher les salles
            $liste_sa = $saRepository->rechercheSA($dataRecherche['sa_nom']);
            if(empty($liste_sa))$this->addFlash('error', "Votre recherche " . $dataRecherche['sa_nom'] . " ne correspond ni a un système d'acquisition ni a une salle");
        }
        else $liste_sa = $saRepository->toutLesSA();

        //$liste_sa = $saRepository->trierSA($liste_sa);

        // Rend la vue avec la liste des expérimentations.
        return $this->render('technicien/gestion-sa.html.twig', [
            'liste_sa' => $liste_sa ,
            'rechercheSAForm' => $rechercheSAForm->createView() ,
            'filtreSAForm' => $filtreSAForm->createView(),
        ]);
    }

    /**
     * Permet au technicien de modifier ses informations personnelles, notamment le mot de passe.
     * @param Request $request Requête HTTP entrante.
     * @param UserRepository $repository Repository pour les utilisateurs.
     * @param EntityManagerInterface $manager Gestionnaire d'entité pour la persistance des données.
     * @param UserPasswordHasherInterface $hasher Interface pour le hachage de mot de passe.
     * @return Response Réponse HTTP avec la vue de modification des informations personnelles.
     * @Route('/technicien/modifier', name: 'app_modifier')
     */
    #[Route('/technicien/modifier', name: 'app_modifier')]
    public function modifier(Request $request ,UserRepository $repository, EntityManagerInterface $manager , UserPasswordHasherInterface $hasher ): Response
    {
        $user = $repository->rechercheUser('technicien');
        $userForm = $this->createForm(UserType::class);
        $userForm->handleRequest($request);
        $erreur = null;

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $data = $userForm->getData();

            if($data['PlainPassword'] != $data['verif']){
                $this->addFlash('error', "Vos nouveaux mots de passe ne correspondent pas entre eux. Veuillez réessayer.");
            }
            else if(!$hasher->isPasswordValid($user,$data['MDP'])){
                $this->addFlash('error', "Mot de passe actuel incorrects");
            }
            else if(strlen($data['PlainPassword']) < 8 )
            {
                $erreur = "Le mot de passe doit contenir au moins 8 caractère";
            }
            else if(preg_match('/[a-z]/', $data['PlainPassword']) !== 1){
                $erreur = "Le mot de passe doit contenir au moins une minuscule";
            }
            else if(preg_match('/[A-Z]/', $data['PlainPassword']) !== 1){
                $erreur = "Le mot de passe doit contenir au moins une majuscule";
            }
            else if(preg_match('/[^a-zA-Z0-9]/', $data['PlainPassword']) !== 1){
                $erreur = "Le mot de passe doit contenir au moins un un caractère spécial";
            }
            else{
                $user->setPlainPassword($data['PlainPassword']);
                $manager->persist($user);
                $manager->flush();
                $this->addFlash('success', "Mot de passe modifié !");
            }

        }
        // Rend la vue avec la liste des expérimentations.
        return $this->render('technicien/modifier.html.twig', [
            'userForm' => $userForm->createView() ,
            'erreur' => $erreur ,
        ]);
    }
}
