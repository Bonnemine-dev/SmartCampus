<?php

namespace App\Controller;

use App\Form\AjoutSAFormType;
use App\Repository\SARepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class SAController extends AbstractController
{
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

    #[Route('/technicien/gestion-sa/supprimer-sa/{nomsa}', name: 'supprimer_sa')]
    public function supprimer_sa(Request $request, SARepository $saRepository, $nomsa): Response
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

    #[Route('/charge-de-mission/plan-experimentation/suppression-sa/{nomsa}', name: 'valid_supprimer_sa')]
    public function valid_supprimer_sa(SARepository $saRepository, $nomsa): Response
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
}
