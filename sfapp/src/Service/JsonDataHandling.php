<?php

namespace App\Service;

use GuzzleHttp\Client;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\SARepository;

class JsonDataHandling
{

    private $saRepository;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->saRepository = new SARepository($managerRegistry);
    }

    public function getCaptureData($nomsalle, $type)
    {

        $nomsa = $this->saRepository->sa_associe_salle($nomsalle);

        $client = new Client();
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

        return json_decode($response->getBody(), true);
    }

    public function getCaptureDataLimited($nomsalle, $type, $count)
    {
        $nomsa = $this->saRepository->sa_associe_salle($nomsalle);

        $client = new Client();
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

        return json_decode($response->getBody(), true);
    }

    public function getCaptureDataInterval($type, $date1, $date2)
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

        return json_decode($response->getBody(), true);
    }

    /**
     * @throws \Exception
     */
    public function extraireDerniereDonneeSalle($nomsalle)
    {
        date_default_timezone_set('Europe/Paris');

        $derniereDonnee = [
            'hum' => null,
            'temp' => null,
            'co2' => null,
            'date_de_capture' => null
        ];

        $types = ['hum', 'temp', 'co2'];

        foreach ($types as $type) {
            $donnees = $this->getCaptureDataLimited($nomsalle, $type, 1);

            if (!empty($donnees)) {
                $derniereDonnee[$type] = $donnees[0]['valeur'];
                $dateCapture = new \DateTime($donnees[0]['dateCapture']);
                if ($derniereDonnee['date_de_capture'] === null || $dateCapture > new \DateTime($derniereDonnee['date_de_capture'])) {
                    $derniereDonnee['date_de_capture'] = $dateCapture->format('Y-m-d H:i:s');
                }
            }
        }

        return $derniereDonnee;
    }

    /**
     * @throws \Exception
     */
    public function extraireToutesLesDonneeActuellesSalle($date_install)
    {
        $dateInstallString = $date_install['date_install']->format('Y-m-d');
        $dateActuelle = new \DateTime();
        $dateActuelleString = $dateActuelle->format('Y-m-d');

        $types = ['hum', 'temp', 'co2'];
        $groupedData = [];

        foreach ($types as $type) {
            $data = $this->getCaptureDataInterval($type, $dateInstallString, $dateActuelleString);

            foreach ($data as $entry) {
                $date = $entry['dateCapture'];
                if (!isset($groupedData[$date])) {
                    $groupedData[$date] = [
                        'date' => $date,
                        'temp' => null,
                        'hum' => null,
                        'co2' => null,
                    ];
                }
                $groupedData[$date][$type] = $entry['valeur'];
            }
        }

        // Tri par date et conversion en tableau indexé
        uasort($groupedData, function ($a, $b) {
            return new \DateTime($b['date']) <=> new \DateTime($a['date']);
        });

        return array_values($groupedData);
    }

    public function extraireDonneeSurIntervalle($date_install, $date_desinstall)
    {
        $dateInstallString = $date_install->format('Y-m-d');
        $dateDesinstallString = $date_desinstall->format('Y-m-d');

        $types = ['hum', 'temp', 'co2']; // Types de données à récupérer
        $groupedData = [];

        foreach ($types as $type) {
            // Remplacez cette partie par l'appel à votre API
            $data = $this->getCaptureDataInterval($type, $dateInstallString, $dateDesinstallString);

            foreach ($data as $entry) {
                $date = $entry['dateCapture'];
                if (!isset($groupedData[$date])) {
                    $groupedData[$date] = [
                        'date' => $date,
                        'temp' => null,
                        'hum' => null,
                        'co2' => null,
                    ];
                }
                $groupedData[$date][$type] = $entry['valeur'];
            }
        }

        // Tri par date et conversion en tableau indexé
        uasort($groupedData, function ($a, $b) {
            return new \DateTime($b['date']) <=> new \DateTime($a['date']);
        });

        return array_values($groupedData);
    }

    public function extraireDernieresDonneesDesSalles($experimentations)
    {
        $resultats = [];

        foreach ($experimentations as $experimentation) {
            $nomsalle = $experimentation['nom'];

            $derniereDonnee = $this->extraireDerniereDonneeSalle($nomsalle);

            // Si des données ont été trouvées pour la salle
            if ($derniereDonnee['date_de_capture'] !== null) {
                $resultats[] = [
                    'localisation' => $nomsalle,
                    'co2' => $derniereDonnee['co2'],
                    'hum' => $derniereDonnee['hum'],
                    'temp' => $derniereDonnee['temp'],
                    'dateCapture' => $derniereDonnee['date_de_capture']
                ];
            }
        }

        return $resultats;
    }

}
