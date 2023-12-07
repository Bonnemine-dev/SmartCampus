<?php

namespace App\Controller;

use App\Repository\ExperimentationRepository;
use App\Repository\SalleRepository;
use App\Repository\SARepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        if($experimentationRepository->verifierExperimentation($nomsalle)) {
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

        if($experimentationRepository->verifierExperimentation($nomsalle)) {
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
}
