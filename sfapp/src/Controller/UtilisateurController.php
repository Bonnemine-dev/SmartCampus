<?php

namespace App\Controller;

use App\Form\RechercheSalleFormType;
use App\Repository\BatimentRepository;
use App\Repository\ExperimentationRepository;
use App\Repository\SalleRepository;
use App\Repository\SARepository;
use App\Repository\UserRepository;
use App\Service\JsonDataHandling;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @class UtilisateurController
 * Contrôleur pour gérer les interactions des utilisateurs avec l'application.
 * @extends AbstractController
 */
class UtilisateurController extends AbstractController
{
    /**
     * Affiche la page d'accueil pour les utilisateurs et gère la recherche de salles.
     * @param Request $request Requête HTTP entrante.
     * @param SalleRepository $salleRepository Repository pour interagir avec les données des salles.
     * @param SARepository $saRepository Repository pour les systèmes d'acquisition.
     * @param BatimentRepository $batimentRepository Repository pour les bâtiments.
     * @return Response Réponse HTTP avec la vue de l'accueil.
     * @Route('/', name: 'app_accueil')
     */
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

    /**
     * Affiche les données relatives à une salle spécifique pour l'utilisateur.
     * @param UserRepository $userRepository Repository pour les utilisateurs.
     * @param JsonDataHandling $JsonDataHandling_service Service pour la gestion des données JSON.
     * @param ExperimentationRepository $experimentationRepository Repository pour les expérimentations.
     * @param SalleRepository $salleRepository Repository pour les salles.
     * @param string $nomsalle Nom de la salle.
     * @return Response Réponse HTTP avec la vue des données de la salle.
     * @Route('/utilisateur/{nomsalle}', name: 'utilisateur_donnees')
     */
    #[Route('/utilisateur/{nomsalle}', name: 'utilisateur_donnees')]
    public function utilisateur_donnees(UserRepository $userRepository, JsonDataHandling $JsonDataHandling_service, ExperimentationRepository $experimentationRepository,SalleRepository $salleRepository,string $nomsalle): Response
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

        $dernieres_donnees = $JsonDataHandling_service->extraireDerniereDonneeSalle($nomsalle);

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

        $intervalleTempSaison = $userRepository->intervallesTempSaison($dernieres_donnees['date_de_capture']);
        $recommandations = $userRepository->recommandationsSalles($dernieres_donnees, $dernieres_donnees['date_de_capture']);

        // Afficher la vue de salle details avec le résultat de l'existence
        return $this->render('utilisateur/utilisateur-donnees.html.twig', [
            //nom de la salle
            'nomsalle' => $nomsalle,
            //dernière données de la salle, null si inexistantes
            'dernieres_donnees' => $dernieres_donnees,
            //temps écoulé depuis la dernière remonté de données
            'elapsed' => $elapsed ?? null,
            //intervalle de température de la saison
            'intervalleTempSaison' => $intervalleTempSaison,
            //recommandations
            'recommandations' => $recommandations,
        ]);
    }
}
