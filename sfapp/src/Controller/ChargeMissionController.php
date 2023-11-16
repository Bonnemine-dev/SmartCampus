<?php

namespace App\Controller;

use App\Form\FiltreSalleFormType;
use App\Form\RechercheSalleFormType;
use App\Repository\SalleRepository;
use App\Repository\SARepository;
use App\Repository\BatimentRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
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
            'liste_batiments' => $batimentRepository->getTableauBatimentsNomID(),
        ]);

        // Soumission des formulaires à la requête
        $filtreSalleForm->handleRequest($request);
        $rechercheSalleForm->handleRequest($request);

        // Initialisation des résultats de la salle
        $salles = $salleRepository->listerSalles();

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

    #[Route('/charge-de-mission/plan-experimentation/ajouter-salle/{nomsalle}', name: 'ajout_salle')]
    public function ajout_salle(ExperimentationRepository $experimentationRepository,$nomsalle): Response
    {
        if($experimentationRepository->verifierExperimentation($nomsalle)) {
            $existeDeja = 1;
        }
        else{
            $existeDeja = 0;
        }

        return $this->render('chargemission/ajouter-salle.html.twig', [
            'nomsalle' => $nomsalle,
            'existedeja' => $existeDeja,
        ]);
    }

    #[Route('/charge-de-mission/plan-experimentation/ajout-experimentation/{nomsalle}', name: 'ajout_exp')]
    public function ajouterExperimentation(ExperimentationRepository $experimentationRepository, $nomsalle): Response
    {
        // Logique d'ajout d'expérimentation
        $experimentationRepository->ajouterExperimentation($nomsalle);

        // Vérifiez le résultat et ajoutez un message flash approprié
        if ($experimentationRepository->verifierExperimentation($nomsalle)) {
            $this->addFlash('success', "La salle " . $nomsalle . " a été ajoutée au plan d'expérimentation avec succès.");
        } else {
            $this->addFlash('error', "La salle " . $nomsalle . " n'a pas pu être ajoutée au plan d'expérimentation.");
        }

        // Redirection
        return $this->redirectToRoute('app_charge_mission');
    }

    #[Route('/charge-de-mission/plan-experimentation/supprimer-salle/{nomsalle}', name: 'supprimer_salle')]
    public function supprimer_salle(ExperimentationRepository $experimentationRepository , $nomsalle): Response
    {
        if($experimentationRepository->verifierExperimentation($nomsalle)) {
            $existeDeja = 1;
        }
        else{
            $existeDeja = 0;
        }

        return $this->render('chargemission/supprimer-salle.html.twig', [
            'nomsalle' => $nomsalle,
            'existedeja' => $existeDeja,
        ]);
    }

    #[Route('/charge-de-mission/plan-experimentation/supprimer-experimentation/{nomsalle}', name: 'supprimer_exp')]
    public function supprimerExperimentation(ExperimentationRepository $experimentationRepository , $nomsalle): Response
    {
        // Utilisez la méthode du repository pour ajouter des données
        $experimentationRepository->supprimerExperimentation($nomsalle);

        // Vérifiez le résultat et ajoutez un message flash approprié
        if (!$experimentationRepository->verifierExperimentation($nomsalle)) {
            $this->addFlash('success', "La salle " . $nomsalle . " a été retirée du plan d'expérimentation avec succès.");
        } else {
            $this->addFlash('error', "La salle " . $nomsalle . " n'a pas pu être retirée du plan d'expérimentation.");
        }

        // Redirigez l'utilisateur après l'ajout réussi, par exemple à une page de confirmation
        return $this->redirectToRoute('app_charge_mission');
    }
}