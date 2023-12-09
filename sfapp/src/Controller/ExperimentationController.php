<?php

namespace App\Controller;

use App\Config\EtatExperimentation;
use App\Repository\ExperimentationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExperimentationController extends AbstractController
{
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
        return $this->redirectToRoute('app_charge_mission', ['scrollTo' => $nomsalle]);
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
        return $this->redirectToRoute('app_charge_mission', ['scrollTo' => $nomsalle]);
    }

    #[Route('/technicien/modifier-etat-experimentation/{etat}/{nomsalle}', name: 'modifier_etat_exp')]
    public function modifierEtatExperimentation(ExperimentationRepository $experimentationRepository, $etat, $nomsalle): Response
    {
        // Utilisez la méthode du repository pour modifier l'état de l'expérimentation
        if ($etat == "installee") {
            $nouvelEtat = EtatExperimentation::installee;
        } else if ($etat == "retiree") {
            $nouvelEtat = EtatExperimentation::retiree;
        } else {
            $this->addFlash('error', "L'état de l'expérimentation de la salle " . $nomsalle . " n'a pas pu être modifié.");
            return $this->redirectToRoute('app_technicien');
        }

        $experimentationRepository->modifierEtat($nouvelEtat, $nomsalle);

        // Vérifiez le résultat et ajoutez un message flash approprié
        if ($experimentationRepository->etatExperimentation($nomsalle) == $nouvelEtat) {
            $this->addFlash('success', "L'état de l'expérimentation de la salle " . $nomsalle . " a été modifié avec succès.");
        } else {
            $this->addFlash('error', "L'état de l'expérimentation de la salle " . $nomsalle . " n'a pas pu être modifié.");
        }

        // Redirigez l'utilisateur après l'ajout réussi, par exemple à une page de confirmation
        return $this->redirectToRoute('app_technicien', ['scrollTo' => $nomsalle]);
    }
}
