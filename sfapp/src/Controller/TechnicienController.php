<?php

namespace App\Controller;

use App\Form\RechercheSAFormType;
use App\Repository\ExperimentationRepository;
use App\Repository\SARepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TechnicienController extends AbstractController
{
    // La fonction index représente la page principale des fonctionnalités du technicien.
    // Elle récupère la liste des expérimentations sans date d'installation à partir du repository.
    // Ensuite, elle rend la vue 'technicien/liste-souhaits.html.twig' avec les expérimentations récupérées.
    #[Route('/technicien/liste-souhaits', name: 'app_technicien')]
    public function index(ExperimentationRepository $repository): Response
    {
        // Récupère les expérimentations sans date d'installation du repository.
        $experimentations = $repository->trouveExperimentationsSansDateInstallation();

        // Rend la vue avec la liste des expérimentations.
        return $this->render('technicien/liste-souhaits.html.twig', [
            'experimentations' => $experimentations
        ]);
    }

    // La fonction gestionSA représente la page de gestion des SA du technicien.
    // Elle récupère la liste des SA à partir du repository.
    // Ensuite, elle rend la vue 'technicien/gestion-sa.html.twig' avec les SA récupérées.
    #[Route('/technicien/gestion-sa', name: 'gestion_sa')]
    public function gestion_sa(Request $request , SARepository $saRepository): Response
    {
        // Récupère les expérimentations sans date d'installation du repository.
        $liste_sa = $saRepository->toutLesSA();

        // Création des instances de formulaire
        $rechercheSAForm = $this->createForm(RechercheSAFormType::class);

        // Soumission des formulaires à la requête
        $rechercheSAForm->handleRequest($request);

        // Recherche des salles en fonction du formulaire de recherche
        if ($rechercheSAForm->isSubmitted() && $rechercheSAForm->isValid()) {
            $dataRecherche = $rechercheSAForm->getData();
            // Extraire les données et les utiliser pour rechercher les salles
            if($dataRecherche['sa_nom'] == '')
            {
                $liste_sa = $saRepository->toutLesSA();
            }
            else
            {
                $liste_sa = $saRepository->rechercheSA($dataRecherche['sa_nom']);
            }
        }
        if(empty($liste_sa))$this->addFlash('error', "Votre recherche " . $dataRecherche['sa_nom'] . " ne correspond ni a un système d'acquisition ni a une salle");
        // Rend la vue avec la liste des expérimentations.
        return $this->render('technicien/gestion-sa.html.twig', [
            'liste_sa' => $liste_sa ,
            'rechercheSAForm' => $rechercheSAForm->createView() ,
        ]);
    }
}
