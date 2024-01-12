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

/**
 * @class ChargeMissionController
 * Contrôleur pour gérer les actions du chargé de mission.
 * @extends AbstractController
 */
class ChargeMissionController extends AbstractController
{
    /**
     * Affiche la page de planification des expérimentations.
     * Gère les formulaires de filtre et de recherche pour les salles.
     * @param Request $request La requête HTTP entrante.
     * @param SalleRepository $salleRepository Le repository pour accéder aux données des salles.
     * @param SARepository $saRepository Le repository pour accéder aux données des SA.
     * @param BatimentRepository $batimentRepository Le repository pour accéder aux données des bâtiments.
     * @return Response La réponse HTTP avec la vue générée.
     * @Route('/charge-de-mission/plan-experimentation', name: 'app_charge_mission')
     */
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

    /**
     * Affiche la liste des expérimentations en cours.
     * Utilise les formulaires de filtre et de recherche pour filtrer les résultats.
     * @param SARepository $saRepository Le repository pour accéder aux données des SA.
     * @param UserRepository $userRepository Le repository pour accéder aux données des utilisateurs.
     * @param Request $request La requête HTTP entrante.
     * @param BatimentRepository $batimentRepository Le repository pour accéder aux données des bâtiments.
     * @param ExperimentationRepository $experimentationRepository Le repository pour accéder aux données des expérimentations.
     * @param JsonDataHandling $jsonDataHandling Le service de traitement des données JSON.
     * @return Response La réponse HTTP avec la vue générée.
     * @Route('/charge-de-mission/liste-salles', name: 'liste_salles')
     */
    #[Route('/charge-de-mission/liste-salles', name: 'liste_salles')]
    public function liste_experimentation(SARepository $saRepository, UserRepository $userRepository, Request $request, BatimentRepository $batimentRepository, ExperimentationRepository $experimentationRepository, JsonDataHandling $jsonDataHandling): Response
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
        $liste_experimentations = $experimentationRepository->filtreExperimentationAnalyse();

        // Filtrage des salles en fonction du formulaire de filtre
        if ($filtreSalleForm->isSubmitted() && $filtreSalleForm->isValid()) {
            $dataFiltre = $filtreSalleForm->getData();
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
            $liste_experimentations = $experimentationRepository->rechercheExperimentationAnalyse(
                $dataRecherche['batiment'] ?? null,
                $dataRecherche['salle'] ?? null
            );
        }
        // Logique métier supplémentaire
        $batiments = $batimentRepository->findAll();

        return $this->render('chargemission/liste-salles.html.twig', [
            'liste_experimentations' => $liste_experimentations, 
            'liste_batiments' => $batiments,
            'filtreSalleForm' => $filtreSalleForm->createView(),
            'rechercheSalleForm' => $rechercheSalleForm->createView(),
        ]);
    }

    /**
     * Affiche le tableau de bord du chargé de mission.
     * Inclut des données météorologiques et des statistiques sur les salles.
     * @param JsonDataHandling $jsonDataHandling Le service de traitement des données JSON.
     * @param UserRepository $userRepository Le repository pour accéder aux données des utilisateurs.
     * @return Response La réponse HTTP avec la vue générée.
     * @Route('/charge-de-mission/tableau-de-bord', name: 'cm_tableau_de_bord')
     */
    #[Route('/charge-de-mission/tableau-de-bord', name: 'cm_tableau_de_bord')]
    public function cm_tableau_de_bord(JsonDataHandling $jsonDataHandling, UserRepository $userRepository): Response
    {
        //récuperer la température exterireur
        $apiKey = 'fb96e1802894f03c5c50e5408b058bce';

        // La Rochelle, France - ID de la ville
        $cityId = 3006787;

        // URL de l'API OpenWeatherMap
        $apiUrl = "http://api.openweathermap.org/data/2.5/weather?id=$cityId&appid=$apiKey";

        // Effectuer la requête HTTP pour récupérer les données météorologiques
            $response = file_get_contents($apiUrl);

        $temperature_ext = 0;
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

        $salles = [];
        $compteur = 0;
        foreach ($jsonDataHandling->getSalles() as $nomSalle => $infosSalle) {
            if ($compteur < 12) {
                $salles[] = $nomSalle; // Ajoutez le nom de la salle au tableau
                $compteur++; // Incrémentez le compteur
            } else {
                break; // Arrêtez la boucle après avoir ajouté les 12 premières salles
            }
        }

        return $this->render('chargemission/tableau-de-bord.html.twig', [
             'salles' => $salles,
             'temperature_ext' => $temperature_ext
        ]);
    }

    /**
     * Permet au chargé de mission de modifier son mot de passe.
     * Gère le formulaire de modification de mot de passe.
     * @param Request $request La requête HTTP entrante.
     * @param UserRepository $repository Le repository pour accéder aux données des utilisateurs.
     * @param EntityManagerInterface $manager Le gestionnaire d'entités.
     * @param UserPasswordHasherInterface $hasher L'outil de hashage des mots de passe.
     * @return Response La réponse HTTP avec la vue générée.
     * @Route('/charge-de-mission/modifier', name: 'app_modifier_chargemission')
     */
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
                $this->addFlash('error', "Mot de passe actuel incorrect");
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
        return $this->render('chargemission/modifier.html.twig', [
            'userForm' => $userForm->createView() ,
            'erreur' => $erreur ,
        ]);
    }
}
