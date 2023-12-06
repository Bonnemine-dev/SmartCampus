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
    #[Route('/technicien/gestion-sa', name: 'gestion-sa')]
    public function gestionSA(Request $request , SARepository $saRepository , ExperimentationRepository $expRepository): Response
    {
        // Récupère les expérimentations sans date d'installation du repository.
        $SA = $saRepository->toutLesSA();
        $experimentations = $expRepository->trouveExperimentationsSansDateInstallation();

        // Création des instances de formulaire
        $rechercheSAForm = $this->createForm(RechercheSAFormType::class);

        // Soumission des formulaires à la requête
        $rechercheSAForm->handleRequest($request);

        // Recherche des salles en fonction du formulaire de recherche
        if ($rechercheSAForm->isSubmitted() && $rechercheSAForm->isValid()) {
            $dataRecherche = $rechercheSAForm->getData();
            // Extraire les données et les utiliser pour rechercher les salles
            $SA = $saRepository->rechercheSA(
                $dataRecherche['nom'] ?? null
            );
        }

        // Rend la vue avec la liste des expérimentations.
        return $this->render('technicien/gestion-sa.html.twig', [
            'SA' => $SA ,
            'experimentations' => $experimentations ,
            'rechercheSAForm' => $rechercheSAForm->createView() ,
        ]);
    }
}
