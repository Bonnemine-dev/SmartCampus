<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TechnicienController extends AbstractController
{
    #[Route('/technicien/liste-souhaits', name: 'app_technicien')]
    public function index(): Response
    {
        return $this->render('technicien/liste-souhaits.html.twig', [
            'controller_name' => 'TechnicienController',
        ]);
    }
}
