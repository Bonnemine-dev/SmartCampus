<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\FiltreSAFormType;
use App\Form\RechercheSAFormType;
use App\Form\UserType;
use App\Repository\ExperimentationRepository;
use App\Repository\SARepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TechnicienController extends AbstractController
{
    // La fonction index représente la page principale des fonctionnalités du technicien.
    // Elle récupère la liste des expérimentations sans date d'installation à partir du repository.
    // Ensuite, elle rend la vue 'technicien/liste-souhaits.html.twig' avec les expérimentations récupérées.
    #[Route('/technicien/liste-souhaits', name: 'app_technicien')]
    public function index(ExperimentationRepository $repository): Response
    {

        // Récupère les expérimentations du repository.
        $experimentations = $repository->trouveExperimentations();
        $experimentations = $repository->triexperimentation($experimentations);

        // Rend la vue avec la liste des expérimentations.
        return $this->render('technicien/liste-souhaits.html.twig', [
            'experimentations' => $experimentations
        ]);
    }

    // La fonction gestionSA représente la page de gestion des SA du technicien.
    // Elle récupère la liste des SA à partir du repository.
    // Ensuite, elle rend la vue 'technicien/gestion-sa.html.twig' avec les SA récupérées.
    #[Route('/technicien/gestion-sa', name: 'gestion_sa')]
    public function gestion_sa(Request $request , SARepository $saRepository): Response
    {
        $filtreSAForm = $this->createForm(FiltreSAFormType::class);
        $filtreSAForm->handleRequest($request);
        $rechercheSAForm = $this->createForm(RechercheSAFormType::class);
        $rechercheSAForm->handleRequest($request);

        if ($filtreSAForm->isSubmitted() && $filtreSAForm->isValid()) {
            $dataFiltre = $filtreSAForm->getData();
            // Extraire les données et les utiliser pour filtrer les salles
            $liste_sa = $saRepository->filtrerSAGestionSA(
                $dataFiltre['etat'] ?? null,
                $dataFiltre['localisation'] ?? null,
            );
            if(empty($liste_sa))$this->addFlash('error', "Votre recherche ne correspond ni a un système d'acquisition ni a une salle");
        }
        else if ($rechercheSAForm->isSubmitted() && $rechercheSAForm->isValid()) {
            $dataRecherche = $rechercheSAForm->getData();
            // Extraire les données et les utiliser pour rechercher les salles
            $liste_sa = $saRepository->rechercheSA($dataRecherche['sa_nom']);
            if(empty($liste_sa))$this->addFlash('error', "Votre recherche " . $dataRecherche['sa_nom'] . " ne correspond ni a un système d'acquisition ni a une salle");
        }
        else $liste_sa = $saRepository->toutLesSA();

        $liste_sa = $saRepository->trisa($liste_sa);

        // Rend la vue avec la liste des expérimentations.
        return $this->render('technicien/gestion-sa.html.twig', [
            'liste_sa' => $liste_sa ,
            'rechercheSAForm' => $rechercheSAForm->createView() ,
            'filtreSAForm' => $filtreSAForm->createView(),
        ]);
    }

    #[Route('/technicien/modifier', name: 'app_modifier')]
    public function modifier(Request $request ,UserRepository $repository, EntityManagerInterface $manager ): Response
    {
        $user = $repository->rechercheUser('technicien');
        $userForm = $this->createForm(UserType::class);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $data = $userForm->getData();
            $user->setPlainPassword($data['PlainPassword']);
            $manager->persist($user);
            $manager->flush();
        }
        // Rend la vue avec la liste des expérimentations.
        return $this->render('connexion/modifier.html.twig', [
            'userForm' => $userForm->createView() ,
        ]);
    }
}
