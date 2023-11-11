<?php

namespace App\Controller;

use App\Repository\SalleRepository;
use App\Repository\SARepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChargeMissionController extends AbstractController
{

    /* Route vers le plan d'experimentation */
    #[Route('/charge-de-mission/plan-experimentation', name: 'app_charge_mission')]
    public function index(SalleRepository $salle_repository, SARepository $sa_repository): Response
    {
        $salles = $salle_repository->listerSallesAvecLeurExperimentation();
        $nb_sa = $sa_repository->compteSASansExperimentation();
        return $this->render('chargemission/plan-experimentation.html.twig', [
            'liste_salles' => $salles,
            'nb_sa' => $nb_sa,
        ]);
    }

}
