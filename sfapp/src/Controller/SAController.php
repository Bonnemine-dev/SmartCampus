<?php

namespace App\Controller;

use App\Form\AjoutSAFormType;
use App\Repository\SARepository;
use App\Repository\UserRepository;
use App\Service\JsonDataHandling;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @class SAController
 * Contrôleur pour gérer les actions liées aux systèmes d'acquisition (SA).
 * @extends AbstractController
 */
class SAController extends AbstractController
{
    /**
     * Affiche et traite le formulaire d'ajout d'un système d'acquisition.
     * @param Request $request La requête HTTP entrante.
     * @param SARepository $saRepository Le repository pour accéder aux données des SA.
     * @return Response La réponse HTTP avec la vue du formulaire d'ajout de SA.
     * @Route('/technicien/gestion-sa/ajouter-sa', name: 'ajout_sa')
     */
    #[Route('/technicien/gestion-sa/ajouter-sa', name: 'ajout_sa')]
    public function index(Request $request, SARepository $saRepository): Response
    {
        // Création des instances de formulaire
        $ajoutSAForm = $this->createForm(AjoutSAFormType::class);

        // Soumission des formulaires à la requête
        $ajoutSAForm->handleRequest($request);

        $erreur = "";
        if ($ajoutSAForm->isSubmitted() && $ajoutSAForm->isValid()) {
            $dataRecherche = $ajoutSAForm->getData();
            if (strlen($dataRecherche['nom']) < 7) {
                $erreur = "* 7 caractères minimum";
            }
            else if (strlen($dataRecherche['nom']) > 15) {
                $erreur = "* 15 caractères maximum";
            }
            else if ($saRepository->existeDeja($dataRecherche['nom']) != null) {
                $erreur = "* Ce nom de SA est deja attribué";
            } else {
                // Extraire les données et les utiliser pour rechercher les salles
                $saRepository->ajoutSA(
                    $dataRecherche['nom'] ?? null
                );
                return $this->redirectToRoute('gestion_sa', ['scrollTo' => $dataRecherche['nom']]);
            }
        }

        return $this->render('sa/ajouter-sa.html.twig', [
            'ajoutSAForm' => $ajoutSAForm->createView(),
            'erreur' => $erreur,
        ]);
    }

    /**
     * Affiche la page de confirmation de suppression d'un système d'acquisition.
     * @param Request $request La requête HTTP entrante.
     * @param SARepository $saRepository Le repository pour accéder aux données des SA.
     * @param string $nomsa Le nom du système d'acquisition à supprimer.
     * @return Response La réponse HTTP avec la vue de suppression de SA.
     * @Route('/technicien/gestion-sa/supprimer-sa/{nomsa}', name: 'supprimer_sa')
     */
    #[Route('/technicien/gestion-sa/supprimer-sa/{nomsa}', name: 'supprimer_sa')]
    public function supprimer_sa(Request $request, SARepository $saRepository, string $nomsa): Response
    {
        if ($saRepository->findOneBy(['nom' => $nomsa]) == null) {
            $this->addFlash('error', "Le système d'acquisition " . $nomsa . " ne fait pas encore partie de votre stock.");
            return $this->redirectToRoute('gestion_sa');
        } elseif ($saRepository->findOneBy(['nom' => $nomsa])->isDisponible()) //Oui le SA est libre
        {
            return $this->render('sa/supprimer-sa.html.twig', [
                'nomsa' => $nomsa
            ]);
        } else { // Non , le SA est encore dans une expérimentation
            $this->addFlash('error', "Le système d'acquisition " . $nomsa . " fait partie d'une expérimenation en cours.");
            return $this->redirectToRoute('gestion_sa');
        }
    }

    /**
     * Valide la suppression d'un système d'acquisition.
     * @param SARepository $saRepository Le repository pour accéder aux données des SA.
     * @param string $nomsa Le nom du système d'acquisition à supprimer.
     * @return Response La réponse HTTP avec redirection vers la page de gestion des SA.
     * @Route('/technicien/gestion-sa/suppression-sa/{nomsa}', name: 'valid_supprimer_sa')
     */
    #[Route('/technicien/gestion-sa/suppression-sa/{nomsa}', name: 'valid_supprimer_sa')]
    public function valid_supprimer_sa(SARepository $saRepository, string $nomsa): Response
    {
        // Recherchez l'objet en fonction du nom de la salle entre dans la condition si le sa n'existe pas
        // si il existe il est supprimé
        if (!$saRepository->supprimerSA($nomsa)) //entre dedans si la suppression a échoué
        {
            //ajout du message flash
            $this->addFlash('error', "Le système d'acquisition " . $nomsa . " n'à pas pu être supprimé.");
            // Redirection
            return $this->redirectToRoute('gestion_sa');
        }
        //ajout du message flash
        $this->addFlash('success', "Le système d'acquisition " . $nomsa . " à été supprimé avec succès.");
        // Redirection
        return $this->redirectToRoute('gestion_sa');
    }

    /**
     * Affiche les détails d'un système d'acquisition spécifique.
     * @param UserRepository $userRepository Le repository pour accéder aux données des utilisateurs.
     * @param JsonDataHandling $JsonDataHandling_service Le service de traitement des données JSON.
     * @param SARepository $saRepository Le repository pour accéder aux données des SA.
     * @param string $nomsa Le nom du système d'acquisition à afficher.
     * @return Response La réponse HTTP avec la vue de détails de SA.
     * @Route('/technicien/gestion-sa/details-sa/{nomsa}', name: 'details_sa')
     */
    #[Route('/technicien/gestion-sa/details-sa/{nomsa}', name: 'details_sa')]
    public function details_sa(UserRepository $userRepository, JsonDataHandling $JsonDataHandling_service, SARepository $saRepository, string $nomsa): Response
    {
        //salle inexistante ?
        if ($saRepository->findOneBy(['nom' => $nomsa]) === null) {
            return $this->redirectToRoute('gestion_sa');
        } 

        $etat_sa = $saRepository->findOneBy(['nom' => $nomsa]);

        //Recherche le nom de la salle dans laquelle se trouve le SA
        $nom_salle_associe_sa = $saRepository->salle_associe_sa($nomsa);
        //Récupère les dernières données remonté par le SA
        if($nom_salle_associe_sa != null) {
            $dernieres_donnees = $JsonDataHandling_service->extraireDerniereDonneeSalle($nom_salle_associe_sa[0]['nom']);
        }

        if($nom_salle_associe_sa != null and $dernieres_donnees['date_de_capture'] != null) {
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
            $intervalleTempSaison = $userRepository->intervallesTempSaison($dernieres_donnees['date_de_capture']);
        }


        // Afficher la vue de salle details avec le résultat de l'existence
        return $this->render('sa/details-sa.html.twig', [
            //nom du SA
            'nomsa' => $nomsa,
            //Informations sur le SA
            'etat_sa' => $etat_sa,
            //dernière données de la salle, null si inexistantes
            'dernieres_donnees' => $dernieres_donnees ?? null,
            //temps écoulé depuis la dernière remonté de données
            'elapsed' => $elapsed ?? null,
            //intervalle de température de la saison actuelle
            'intervalleTempSaison' => $intervalleTempSaison ?? null,
        ]);
    }
}
