<?php

namespace App\Controller;

use App\Entity\SA;
use App\Form\AjoutSAFormType;
use App\Repository\SARepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class SAController extends AbstractController
{
    #[Route('/technicien/gestion-sa/ajouter-sa', name: 'ajout-sa')]
    public function index(Request $request , SARepository $repository): Response
    {
        // Création des instances de formulaire
        $ajoutSAForm = $this->createForm(AjoutSAFormType::class);

        // Soumission des formulaires à la requête
        $ajoutSAForm->handleRequest($request);

        $erreur = "";
        if ($ajoutSAForm->isSubmitted() && $ajoutSAForm->isValid()) {
            $dataRecherche = $ajoutSAForm->getData();
            if(strlen($dataRecherche['nom']) < 6){
                $erreur = "* 6 caractères minimum";
            }
            else if ($repository->existeDeja($dataRecherche['nom']) != null){
                $erreur = "nom de SA deja atribuer";
            }
            else{
                // Extraire les données et les utiliser pour rechercher les salles
                $repository->ajoutSA(
                    $dataRecherche['nom'] ?? null
                );
                return $this->redirectToRoute('gestion-sa');
            }
        }

        return $this->render('sa/ajouter-sa.html.twig', [
            'ajoutSAForm' => $ajoutSAForm->createView() ,
            'erreur' => $erreur ,
        ]);
    }
}
