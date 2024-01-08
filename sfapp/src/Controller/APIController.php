<?php

namespace App\Controller;

use App\Service\JsonDataHandling;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\SARepository;

class APIController extends AbstractController
{
    private $jsonDataHandling;

    public function __construct(JsonDataHandling $jsonDataHandling)
    {
        $this->jsonDataHandling = $jsonDataHandling;
    }

    #[Route('/api/captures/{nomsalle?}/{type?}', name: 'app_api')]
    public function index( $nomsalle = null, $type = null): Response
    {
        $data = $this->jsonDataHandling->getCaptureData($nomsalle, $type);
        return new JsonResponse($data);
    }

    #[Route('/api/captures/{nomsalle?}/{type?}/{count?}', name: 'app_api_limit')]
    public function limit($type = null, $nomsalle = null, $count = null): Response
    {
        $data = $this->jsonDataHandling->getCaptureDataLimited($nomsalle, $type, $count);
        return new JsonResponse($data);
    }

    #[Route('/api/captures/interval/{type}/{date1}/{date2}', name: 'app_api_interval')]
    public function interval($type, $date1, $date2): Response
    {
        $data = $this->jsonDataHandling->getCaptureDataInterval($type, $date1, $date2);
        return new JsonResponse($data);
    }


}
