<?php

namespace App\Controller;

use App\Config\EtatExperimentation;
use App\Entity\Experimentation;
use App\Form\FiltreSalleFormType;
use App\Form\RechercheSalleFormType;
use App\Repository\SalleRepository;
use App\Repository\SARepository;
use App\Repository\BatimentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ExperimentationRepository;

class ChargeMissionController extends AbstractController
{
    #[Route('/charge-de-mission/plan-experimentation', name: 'app_charge_mission')]
    public function index(Request $request, SalleRepository $salleRepository, SARepository $saRepository, BatimentRepository $batimentRepository): Response
    {
        // Création des instances de formulaire
        $filtreSalleForm = $this->createForm(FiltreSalleFormType::class);
        $rechercheSalleForm = $this->createForm(RechercheSalleFormType::class, null, [
            'liste_batiments' => $batimentRepository->tableauBatimentsNomID(),
        ]);

        // Soumission des formulaires à la requête
        $filtreSalleForm->handleRequest($request);
        $rechercheSalleForm->handleRequest($request);

        // Initialisation des résultats de la salle
        $salles = $salleRepository->filtrerSallePlanExp();

        // Filtrage des salles en fonction du formulaire de filtre
        if ($filtreSalleForm->isSubmitted() && $filtreSalleForm->isValid()) {
            $dataFiltre = $filtreSalleForm->getData();
            // Extraire les données et les utiliser pour filtrer les salles
            $salles = $salleRepository->filtrerSallePlanExp(
                $dataFiltre['etage'] ?? null,
                $dataFiltre['orientation'] ?? null,
                $dataFiltre['ordinateurs'] ?? null,
                $dataFiltre['sa'] ?? null
            );
        }

        // Recherche des salles en fonction du formulaire de recherche
        if ($rechercheSalleForm->isSubmitted() && $rechercheSalleForm->isValid()) {
            $dataRecherche = $rechercheSalleForm->getData();
            // Extraire les données et les utiliser pour rechercher les salles
            $salles = $salleRepository->rechercheSallePlanExp(
                $dataRecherche['batiment'] ?? null,
                $dataRecherche['salle'] ?? null
            );
        }

        // Logique métier supplémentaire
        $salles = $salleRepository->triListeSalle($salles);
        $nb_sa = $saRepository->compteSASansExperimentation();
        $batiments = $batimentRepository->findAll();

        // Passer les instances de formulaire au template
        return $this->render('chargemission/plan-experimentation.html.twig', [
            'liste_salles' => $salles,
            'nb_sa' => $nb_sa,
            'liste_batiments' => $batiments,
            'filtreSalleForm' => $filtreSalleForm->createView(),
            'rechercheSalleForm' => $rechercheSalleForm->createView(),
        ]);
    }
    
    #[Route('/charge-de-mission/liste-salles', name: 'liste_salles')]
    public function liste_experimentation(ExperimentationRepository $experimentationRepository): Response
    {
        $liste_experimentations = $experimentationRepository->extraireLesExperimentations();
        // Afficher la vue d'ajout de salle avec le résultat de l'existence
        // 1. Lire le fichier JSON
        $jsonFilePath = $this->getParameter('kernel.project_dir') . "/public/json/liste_exp.json";
        $jsonContent = file_get_contents($jsonFilePath);
        $dataArray = json_decode($jsonContent, true);

        // 2. Organiser les données par salle
        $listeDerniereValue = [];
        foreach ($dataArray as $data) {
            $nomSalle = $data['localisation'];

            // Si la salle n'existe pas encore dans la liste, ajoutez-la
            if (!isset($listeDerniereValeur[$nomSalle])) {
                $listeDerniereValeur[$nomSalle] = [
                    'hum' => null,
                    'temp' => null,
                    'co2' => null,
                ];
            }

            // 3. Calculer les dernières valeurs pour chaque type de mesure
            if ($data['nom'] === 'temp') {
                $listeDerniereValeur[$nomSalle]['temp'] = $data['valeur'];
            } elseif ($data['nom'] === 'hum') {
                $listeDerniereValeur[$nomSalle]['hum'] = $data['valeur'];
            } elseif ($data['nom'] === 'co2') {
                $listeDerniereValeur[$nomSalle]['co2'] = $data['valeur'];
            }
        }
        return $this->render('chargemission/liste-salles.html.twig', [
            'liste_experimentations' => $liste_experimentations, 
            'listeDerniereValeur' => $listeDerniereValeur
        ]);
    }
}