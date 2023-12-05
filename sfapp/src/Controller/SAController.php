<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SAController extends AbstractController
{
    #[Route('/technicien/gestion-sa/ajouter-sa', name: 'ajout-sa')]
    public function index(): Response
    {
        return $this->render('sa/ajouter-sa.html.twig', [
            'controller_name' => 'SAController',
        ]);
    }
}
