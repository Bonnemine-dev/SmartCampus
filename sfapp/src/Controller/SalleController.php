<?php

namespace App\Controller;

use App\Config\EtatExperimentation;
use App\Config\EtatSA;
use App\Entity\Experimentation;
use App\Repository\SARepository;
use App\Repository\UserRepository;
use App\Service\JsonDataHandling;
use App\Repository\SalleRepository;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\ExperimentationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SalleController extends AbstractController
{
    #[Route('/charge-de-mission/plan-experimentation/ajouter-salle/{nomsalle}', name: 'ajout_salle')]
    public function ajout_salle(SalleRepository $salleRepository , SARepository $saRepository ,ExperimentationRepository $experimentationRepository,$nomsalle): Response
    {

        // Vérifier si la salle existe déjà dans les expérimentations
        if($salleRepository->nomSalleId($nomsalle) == null){
            return $this->render('bundles/TwigBundle/Exception/error404.html.twig');
        }
        $existeDeja = 0;
        $SADispo = $saRepository->compteSASansExperimentation();
        if($experimentationRepository->estExistante($nomsalle)) {
            $existeDeja = 1;
        }


        // Afficher la vue d'ajout de salle avec le résultat de l'existence
        return $this->render('chargemission/ajouter-salle.html.twig', [
            'nomsalle' => $nomsalle,
            'existedeja' => $existeDeja,
            'SADispo' => $SADispo,
        ]);
    }

    #[Route('/charge-de-mission/plan-experimentation/supprimer-salle/{nomsalle}', name: 'supprimer_salle')]
    public function supprimer_salle(SalleRepository $salleRepository , ExperimentationRepository $experimentationRepository , $nomsalle): Response
    {
        // Vérifier si la salle existe déjà dans les expérimentations
        if($salleRepository->nomSalleId($nomsalle) == null){
            return $this->render('bundles/TwigBundle/Exception/error404.html.twig');
        }

        if($experimentationRepository->estExistante($nomsalle)) {
            $existeDeja = 1;
        }
        else{
            $existeDeja = 0;
        }

        // Afficher la vue de suppression de salle avec le résultat de l'existence
        return $this->render('chargemission/supprimer-salle.html.twig', [
            'nomsalle' => $nomsalle,
            'existedeja' => $existeDeja,
        ]);
    }

    #[Route('/charge-de-mission/liste-salles/details-salle/{nomsalle}', name: 'details_salle')]
    public function details_salle(UserRepository $userRepository, JsonDataHandling $JsonDataHandling_service, PaginatorInterface $paginator,ExperimentationRepository $experimentationRepository,SalleRepository $salleRepository,Request $request,$nomsalle): Response
    {
        // Salle inexistante ?
        if ($salleRepository->findOneBy(['nom' => $nomsalle]) === null or !$experimentationRepository->estExistante($nomsalle)) {
            return $this->redirectToRoute('liste_salles');
        }

        $etat_sa = $salleRepository->SAAssocie($nomsalle);

        //extraction des dernière donnée d'une salle si il y en a pas alors est null
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



            //determine quel recommandation faire
            $etatExp = $experimentationRepository->etatExp($nomsalle) ?? null;
            foreach ($etatExp as $exp) {
                if ($exp['etat_exp'] == EtatExperimentation::demandeInstallation ) {
                    $recommandation = 'demande_installation_en_cours';
                } elseif ($exp['etat_exp'] == EtatExperimentation::installee) {
                    $recommandation = 'installee';
                } elseif ($exp['etat_exp'] == EtatExperimentation::demandeRetrait) {
                    $recommandation = 'demande_retrait_en_cours';
                }
            }
            if(!isset($recommandation)){$recommandation = 'pas_de_exp';}

        $intervalleTempSaison = $userRepository->intervallesTempSaison($dernieres_donnees['date_de_capture']);

        // Afficher la vue de salle details avec le résultat de l'existence
        return $this->render('salle/details-salle.html.twig', [
            //nom de la salle
            'nomsalle' => $nomsalle,
            //Infoemration sur le sa présent dans la salle si il existe
            'etat_sa' => $etat_sa ?? null,
            //dernière données de la salle, null si inexistantes
            'dernieres_donnees' => $dernieres_donnees ?? null,
            //temps écoulé depuis la dernière remonté de données
            'elapsed' => $elapsed ?? null,
            //liste d'une liste contenant des information sur l'intervalle et toutes les données associé, null si inexistantes
            'recommandation' => $recommandation ?? null,

            'intervalleTempSaison' => $intervalleTempSaison ?? null,
        ]);
    }

    #[Route('/charge-de-mission/liste-salles/historique/{nomsalle}', name: 'historique_salle')]
    public function historique_salle(UserRepository $userRepository, JsonDataHandling $JsonDataHandling_service, PaginatorInterface $paginator,ExperimentationRepository $experimentationRepository,SalleRepository $salleRepository,Request $request,$nomsalle): Response
    {
        $liste_donnee_historique = null;
        //salle inexistante ?
        if ($salleRepository->findOneBy(['nom' => $nomsalle]) === null) {
            return $this->redirectToRoute('liste_salles');
        }

        //Obtention via BD de la date de début de l'expérimentation en cours
        $dateInstallExpActuelle = $experimentationRepository->extraireDateInstallExpActuelle($nomsalle);

        //Si il existe une expérimentation en cours alors créer le tableau
        if ($dateInstallExpActuelle != null and $dateInstallExpActuelle['date_install'] != null) {
            $liste_donnee_historique = $paginator->paginate(
                $JsonDataHandling_service->extraireToutesLesDonneeActuellesSalle($dateInstallExpActuelle),
                $request->query->getInt('pageH',1),
                18,[
                    'pageParameterName' => 'pageH', // Spécifiez le nom du paramètre de page pour la première entité
                ]
            );
        }

        if($liste_donnee_historique  == null or $liste_donnee_historique->getTotalItemCount() == 0){
            return $this->redirectToRoute('details_salle',['nomsalle' => $nomsalle]);
        }

        $intervalleTempSaison = $userRepository->intervallesTempSaison(date('Y-m-d H:i:s'));

        // Afficher la vue de salle details avec le résultat de l'existence
        return $this->render('salle/historique-salle.html.twig', [
            //nom de la salle
            'nomsalle' => $nomsalle,
            //liste de toutes les données de l'expérimentation en cours, null si inexistantes
            'liste_donnee_historique' => $liste_donnee_historique ?? null,
            'intervalleTempSaison' => $intervalleTempSaison ?? null,
        ]);
    }

    #[Route('/charge-de-mission/liste-salles/archives/{nomsalle}', name: 'archives_salle')]
    public function archives_salle(UserRepository $userRepository, JsonDataHandling $JsonDataHandling_service, PaginatorInterface $paginator,ExperimentationRepository $experimentationRepository,SalleRepository $salleRepository,Request $request,$nomsalle): Response
    {
        //salle inexistante ?
        if ($salleRepository->findOneBy(['nom' => $nomsalle]) === null) {
            return $this->redirectToRoute('liste_salles');
        }

        //Obtention via BD de toutes les intervalles des expérimentation qui son terminées
        $liste_intervalles = $experimentationRepository->listerLesIntervallesArchives($nomsalle);

        //Initialisation du tableau qui sera envoié à la twig
        $liste_de_liste_donnee_archive = [];
        $i = 1;

        //parcours tous les intervalles et pour chaque intervalla extrait les données du json qui
        //sont dans cet intervalle et dont la salle à le nom voulue
        foreach ($liste_intervalles as $intervalle) {
            $donneesPagine = $paginator->paginate(
                $JsonDataHandling_service->extraireDonneeSurIntervalle($intervalle['date_install'], $intervalle['date_desinstall']),
                $request->query->getInt('pageA'.$i,1),
                10,[
                    'pageParameterName' => 'pageA'.$i, // Spécifiez le nom du paramètre de page pour la première entité
                ]
            );

            array_push($liste_de_liste_donnee_archive, [
                'date_install' => $intervalle['date_install'],
                'date_desinstall' => $intervalle['date_desinstall'],
                'donnees' => $donneesPagine]);
            $i++;
        }

        if($liste_de_liste_donnee_archive  == null){
            return $this->redirectToRoute('details_salle',['nomsalle' => $nomsalle]);
        }

        $intervalleTempSaison = $userRepository->intervallesTempSaison(date('Y-m-d H:i:s'));

        // Afficher la vue de salle details avec le résultat de l'existence
        return $this->render('salle/archives-salle.html.twig', [
            //nom de la salle
            'nomsalle' => $nomsalle,
            //liste d'une liste contenant des information sur l'intervalle et toutes les données associé, null si inexistantes
            'liste_de_liste_donnee_archive' => $liste_de_liste_donnee_archive ?? null,
            //liste d'une liste contenant des information sur l'intervalle et toutes les données associé, null si inexistantes
            'recommandation' => $recommandation ?? null,
            'intervalleTempSaison' => $intervalleTempSaison ?? null,
        ]);
    }

    #[Route('/charge-de-mission/liste-salles/diagnostic/{nomsalle?}', name: 'diagnostic_salle')]
    public function diagnostic_salle($nomsalle): Response
    {
        // Afficher la vue de salle details avec le résultat de l'existence
        return $this->render('salle/diagnostic-salle.html.twig', [
            //nom de la salle
            'nomsalle' => $nomsalle,
        ]);
    }

}