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

    #[Route('/charge-de-mission/tableau-de-bord', name: 'cm_tableau_de_bord')]
    public function cm_tableau_de_bord(ExperimentationRepository $experimentationRepository, SalleRepository $salleRepository): Response
    {
        // Afficher la vue d'ajout de salle avec le résultat de l'existence
        // 1. Lire le fichier JSON
        $jsonFilePath = $this->getParameter('kernel.project_dir') . "/public/json/moy_der_valeurs.json";
        $jsonContent = file_get_contents($jsonFilePath);
        $dataArray = json_decode($jsonContent, true);
        $taux_carbone_moy = null;
        $temp_moy = null;
        $hum_moy = null;
        // 2. Organiser les données par salle
        foreach ($dataArray as $data) {
            // 3. Calculer les dernières valeurs pour chaque type de mesure
            if ($data['nom'] === 'temp') {
                $temp_moy = $data['valeur'];
            } elseif ($data['nom'] === 'hum') {
                $hum_moy = $data['valeur'];
            } elseif ($data['nom'] === 'co2') {
                $taux_carbone_moy = $data['valeur'];
            }
        }
        return $this->render('chargemission/tableau-de-bord.html.twig', [
             'temp_moy' => $temp_moy,
             'hum_moy' => $hum_moy,
             'taux_carbone_moy' => $taux_carbone_moy
        ]);
    }
}
