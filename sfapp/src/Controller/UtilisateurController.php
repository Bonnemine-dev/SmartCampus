<?php

namespace App\Controller;

use App\Config\EtatExperimentation;
use App\Form\FiltreSalleFormType;
use App\Form\RechercheSalleFormType;
use App\Repository\BatimentRepository;
use App\Repository\ExperimentationRepository;
use App\Repository\SalleRepository;
use App\Repository\SARepository;
use App\Service\JsonDataHandling;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UtilisateurController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(Request $request, SalleRepository $salleRepository, SARepository $saRepository, BatimentRepository $batimentRepository): Response
    {
        // Création des instances de formulaire
        $rechercheSalleForm = $this->createForm(RechercheSalleFormType::class, null, [
            'liste_batiments' => $batimentRepository->tableauBatimentsNomID(),
        ]);

        // Soumission des formulaires à la requête
        $rechercheSalleForm->handleRequest($request);

        // Recherche des salles en fonction du formulaire de recherche
        if ($rechercheSalleForm->isSubmitted() && $rechercheSalleForm->isValid()) {
            $dataRecherche = $rechercheSalleForm->getData();
            // Extraire les données et les utiliser pour rechercher la salles
            $salle = $salleRepository->findOneBy(['nom' => $dataRecherche['salle']]);
            if($salle != null){
                return $this->redirect('/utilisateur/'.$salle->getNom());
            }
            else{
                $this->addFlash('error', "La salle ".$dataRecherche['salle']." n'existe pas");
            }

        }

        return $this->render('utilisateur/index.html.twig', [
            'rechercheSalleForm' => $rechercheSalleForm->createView(),
        ]);
    }

    #[Route('/utilisateur/{nomsalle}', name: 'utilisateur_donnees')]
    public function utilisateur_donnees(JsonDataHandling $JsonDataHandling_service, PaginatorInterface $paginator,ExperimentationRepository $experimentationRepository,SalleRepository $salleRepository,Request $request,$nomsalle): Response
    {
        //salle inexistante ?
        if ($salleRepository->findOneBy(['nom' => $nomsalle]) === null) {
            $this->addFlash('error', "La salle ".$nomsalle." n'existe pas");
            return $this->redirectToRoute('app_accueil');
        }

        if (!$experimentationRepository->estExistante($nomsalle)) {
            $this->addFlash('error', "La salle ".$nomsalle." n'est pas dans le plan d'experimenattion");
            return $this->redirectToRoute('app_accueil');
        }

        //lecture du fichier JSON
        $jsonFilePath = $this->getParameter('kernel.project_dir') . "/public/json/moy_der_valeurs.json";
        $jsonContent = file_get_contents($jsonFilePath);
        $dataArray = json_decode($jsonContent, true);

        //extraction des dernière donnée d'une salle si il y en a pas alors est null
        $dernieres_donnees = $JsonDataHandling_service->extraireDerniereDonneeSalle($dataArray,$nomsalle);

        if($dernieres_donnees['date_de_capture'] != null){
            $date_de_capture = new \DateTime($dernieres_donnees['date_de_capture']);
            $now = new \DateTime();
            $interval = $date_de_capture->diff($now);

            // Format l'intervalle de temps de manière lisible
            if ($interval->y > 0) {
                $elapsed = $interval->y . ' années';
            } elseif ($interval->m > 0) {
                $elapsed = $interval->m . ' mois';
            } elseif ($interval->d > 0) {
                $elapsed = $interval->d . ' jours';
            } elseif ($interval->h > 0) {
                $elapsed = $interval->h . ' heures';
            } elseif ($interval->i > 0) {
                $elapsed = $interval->i . ' minutes';
            } else {
                $elapsed = $interval->s . ' secondes';
            }
        }

        // Afficher la vue de salle details avec le résultat de l'existence
        return $this->render('utilisateur/utilisateur-donnees.html.twig', [
            //nom de la salle
            'nomsalle' => $nomsalle,
            //dernière données de la salle, null si inexistantes
            'dernieres_donnees' => $dernieres_donnees ?? null,
            //temps écoulé depuis la dernière remonté de données
            'elapsed' => $elapsed ?? null,
        ]);
    }
}
