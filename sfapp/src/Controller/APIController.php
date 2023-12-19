<?php

namespace App\Controller;

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
    /**
     * @throws GuzzleException
     */
    #[Route('/api/captures/{nomsalle?}/{type?}', name: 'app_api')]
    public function index(SARepository $SARepository, $nomsalle = null, $type = null): Response
    {
        $client = new Client();
        $nomsa = $SARepository->sa_associe_salle($nomsalle);
        $response = $client->request('GET', 'https://sae34.k8s.iut-larochelle.fr/api/captures', [
            'query' => [
                'nom' => $type,
                'nomsa' => $nomsa,
                'page' => 1
            ],
            'headers' => [
                'accept' => 'application/json',
                'dbname' => 'sae34bdl2eq2',
                'username' => 'l2eq2',
                'userpass' => 'wiqnyt-fuqgyc-7vUhby'
            ]
        ]);

        return new JsonResponse(json_decode($response->getBody(), true));
    }

    /**
     * @throws GuzzleException
     */
    #[Route('/api/captures/{nomsalle?}/{type?}/{count?}', name: 'app_api_limit')]
    public function limit(SARepository $SARepository, $type = null, $nomsalle = null, $count = null): Response
    {
        $client = new Client();
        $nomsa = $SARepository->sa_associe_salle($nomsalle);
        $response = $client->request('GET', 'https://sae34.k8s.iut-larochelle.fr/api/captures/last', [
            'query' => [
                'nom' => $type,
                'nomsa' => $nomsa,
                'limit' => $count,
                'page' => 1
            ],
            'headers' => [
                'accept' => 'application/json',
                'dbname' => 'sae34bdl2eq2',
                'username' => 'l2eq2',
                'userpass' => 'wiqnyt-fuqgyc-7vUhby'
            ]
        ]);

        return new JsonResponse(json_decode($response->getBody(), true));
    }

    /**
     * @throws GuzzleException
     */
    #[Route('/api/captures/interval/{type}/{date1}/{date2}', name: 'app_api_interval')]
    public function interval(Request $request, $type, $date1, $date2): Response
    {
        $client = new Client();
        $response = $client->request('GET', 'https://sae34.k8s.iut-larochelle.fr/api/captures/interval', [
            'query' => [
                'nom' => $type,
                'date1' => $date1,
                'date2' => $date2,
                'page' => 1
            ],
            'headers' => [
                'accept' => 'application/json',
                'dbname' => 'sae34bdl2eq2',
                'username' => 'l2eq2',
                'userpass' => 'wiqnyt-fuqgyc-7vUhby'
            ]
        ]);

        return new JsonResponse(json_decode($response->getBody(), true));
    }


}
