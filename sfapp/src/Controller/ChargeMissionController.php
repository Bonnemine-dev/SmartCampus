<?php

namespace App\Controller;

use App\Repository\SalleRepository;
use App\Repository\SARepository;
use App\Repository\BatimentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChargeMissionController extends AbstractController
{

    /* Route vers le plan d'experimentation */
    #[Route('/charge-de-mission/plan-experimentation', name: 'app_charge_mission')]
    public function index(Request $request,SalleRepository $salle_repository, SARepository $sa_repository, BatimentRepository $Bat_repository): Response
    {
        $batiment_selectionne = $request->query->get('batiment');
        $nom_salle = $request->query->get('salle');

        $salles = $salle_repository->listerSallesAvecLeurExperimentation($batiment_selectionne,$nom_salle);
        $nb_sa = $sa_repository->compteSASansExperimentation();
        $batiments = $Bat_repository->findAll();
        return $this->render('chargemission/plan-experimentation.html.twig', [
            'liste_salles' => $salles,
            'nb_sa' => $nb_sa,
            'liste_batiments' => $batiments,
            'batiment_selectionne' => $batiment_selectionne,
            'nom_salle' => $nom_salle
        ]);
    }

}
