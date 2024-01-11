<?php

namespace App\Controller;

use App\Repository\SARepository;
use App\Service\JsonDataHandling;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class APIController extends AbstractController
{
    private JsonDataHandling $jsonDataHandling;

    public function __construct(JsonDataHandling $jsonDataHandling)
    {
        $this->jsonDataHandling = $jsonDataHandling;
    }

    #[Route('/api/captures/{nomsalle?}/{type?}', name: 'app_api')]
    public function index(string $nomsalle = null, string $type = null): Response
    {
        $data = $this->jsonDataHandling->getCaptureData($nomsalle, $type);
        return new JsonResponse($data);
    }

    #[Route('/api/captures/{nomsalle?}/{type?}/{count?}', name: 'app_api_limit')]
    public function limit(string $type = null, string $nomsalle = null, int $count = null): Response
    {
        $data = $this->jsonDataHandling->getCaptureDataLimited($nomsalle, $type, $count);
        return new JsonResponse($data);
    }

    #[Route('/api/captures/interval/{nomsalle?}/{type}/{date1}/{date2}', name: 'app_api_interval')]
    public function interval(string $type, string $date1, string $date2, string $nomsalle = null): Response
    {
        $data = $this->jsonDataHandling->getCaptureDataInterval($nomsalle, $type, $date1, $date2);
        return new JsonResponse($data);
    }

    #[Route('/api/captures/moyenne/par/type/{type?}', name: 'app_api_moyenne_type')]
    public function moyenne(string $type): Response
    {
        $data = $this->jsonDataHandling->getMoyenneParType($type);
        return new JsonResponse($data);
    }

    #[Route('/api/captures/liste/salles/avec/donnees', name: 'app_api_moyenne_salle')]
    public function listeSallesAvecDonnees(SARepository $saRepository): Response
    {
        $data = $this->jsonDataHandling->extraireDernieresDonneesDesSalles([]);
        $saRepository->sa_eteint_probleme($data);
        return new JsonResponse($data);
    }

}
