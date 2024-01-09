<?php

namespace App\Controller;

use App\Form\FiltreSalleFormType;
use App\Form\RechercheSalleFormType;
use App\Form\UserType;
use App\Repository\SalleRepository;
use App\Repository\SARepository;
use App\Repository\BatimentRepository;
use App\Repository\UserRepository;
use App\Service\JsonDataHandling;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
    public function liste_experimentation(UserRepository $userRepository, Request $request, SalleRepository $salleRepository, SARepository $saRepository, BatimentRepository $batimentRepository, ExperimentationRepository $experimentationRepository, JsonDataHandling $jsonDataHandling): Response
    {

        $intervalleTempSaison = [-50,-20,50,100];

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
        $liste_experimentations = $experimentationRepository->filtreExperimentationAnalyse();

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
            $liste_experimentations = $experimentationRepository->filtreExperimentationAnalyse(
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
            $liste_experimentations = $experimentationRepository->rechercheExperimentationAnalyse(
                $dataRecherche['batiment'] ?? null,
                $dataRecherche['salle'] ?? null
            );
        }
        // Logique métier supplémentaire
        $batiments = $batimentRepository->findAll();

        // Afficher la vue d'ajout de salle avec le résultat de l'existence
        $listeSallesAvecDonnees = $jsonDataHandling->extraireDernieresDonneesDesSalles($liste_experimentations);
        if(empty($liste_experimentations)){
            $this->addFlash('error', "Votre recherche ne correspond pas a une expérimentation en cours");
        }
        
        if(!empty($listeSallesAvecDonnees)){
            $intervalleTempSaison = $userRepository->intervallesTempSaison($listeSallesAvecDonnees[0]['dateCapture']);
        }

        return $this->render('chargemission/liste-salles.html.twig', [
            'liste_experimentations' => $liste_experimentations, 
            'listeDerniereValeur' => $listeSallesAvecDonnees,
            'liste_batiments' => $batiments,
            'filtreSalleForm' => $filtreSalleForm->createView(),
            'rechercheSalleForm' => $rechercheSalleForm->createView(),
            'intervalleTempSaison' => $intervalleTempSaison,
        ]);
    }

    #[Route('/charge-de-mission/tableau-de-bord', name: 'cm_tableau_de_bord')]
    public function cm_tableau_de_bord(SalleRepository $salleRepository,ExperimentationRepository $experimentationRepository): Response
    {
        //récuperer la température exterireur
        $apiKey = 'fb96e1802894f03c5c50e5408b058bce';

        // La Rochelle, France - ID de la ville
        $cityId = 3006787;

        // URL de l'API OpenWeatherMap
        $apiUrl = "http://api.openweathermap.org/data/2.5/weather?id=$cityId&appid=$apiKey";

        // Effectuer la requête HTTP pour récupérer les données météorologiques
            $response = file_get_contents($apiUrl);

        // Vérifier si la requête a réussi
        if ($response !== false) {
            // Convertir la réponse JSON en tableau associatif
            $weatherData = json_decode($response, true);

            // Vérifier si la réponse contient des données valides
            if ($weatherData && isset($weatherData['main']['temp'])) {
                // Température en Kelvin, convertir en Celsius
                $temperatureCelsius = $weatherData['main']['temp'] - 273.15;

                // récuperer la température
                $temperature_ext = round($temperatureCelsius, 2);
                //arondir a 1 aprés la virgule
                $temperature_ext = round($temperature_ext, 1);
            } else {
                echo "Erreur lors de la récupération des données météorologiques.";
            }
        } else {
            echo "Erreur lors de la requête vers l'API OpenWeatherMap.";
        }

        // Afficher la vue d'ajout de salle avec le résultat de l'existence
        // 1. Lire le fichier JSON
        $jsonFilePath = $this->getParameter('kernel.project_dir') . "/public/json/moy_der_valeurs.json";
        $jsonContent = file_get_contents($jsonFilePath);
        $dataArray = json_decode($jsonContent, true);
        $listsalle = $salleRepository->listerSalles();
        $salles = $experimentationRepository->listerSallesAvecDonnees($dataArray,$listsalle);
        $moyDonnees = $experimentationRepository->moyennesDonnees($dataArray);

        return $this->render('chargemission/tableau-de-bord.html.twig', [
             'temp_moy' => $moyDonnees['temp_moy'],
             'hum_moy' => $moyDonnees['hum_moy'],
             'taux_carbone_moy' => $moyDonnees['co2_moy'],
             'salles' => $salles,
            'temperature_ext' => $temperature_ext
        ]);
    }

    #[Route('/charge-de-mission/modifier', name: 'app_modifier_chargemission')]
    public function modifier(Request $request ,UserRepository $repository, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $user = $repository->rechercheUser('chargemission');
        $userForm = $this->createForm(UserType::class);
        $userForm->handleRequest($request);
        $erreur = null;

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $data = $userForm->getData();

            if($data['PlainPassword'] != $data['verif']){
                $this->addFlash('error', "Vos nouveaux mots de passe ne correspondent pas entre eux. Veuillez réessayer.");
            }
            else if(!$hasher->isPasswordValid($user,$data['MDP'])){
                $this->addFlash('error', "mot de passe actuel incorrects");
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
                $this->addFlash('success', "mot de passe modifier !");
            }

        }
        // Rend la vue avec la liste des expérimentations.
        return $this->render('chargemission/modifier.html.twig', [
            'userForm' => $userForm->createView() ,
            'erreur' => $erreur ,
        ]);
    }
}
