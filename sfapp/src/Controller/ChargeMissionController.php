<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChargeMissionController extends AbstractController
{

    /* Route vers le plan d'experimentation */
    #[Route('/charge-de-mission/plan-experimentation', name: 'app_charge_mission')]
    public function index(): Response
    {
        return $this->render('chargemission/plan-experimentation.html.twig', [
            'controller_name' => 'ChargeMissionController',
        ]);
    }

}
