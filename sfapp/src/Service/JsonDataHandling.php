<?php

namespace App\Service;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\SARepository;

class JsonDataHandling
{


    /*public function extraireToutesLesDonneeActuellesSalle($dataArray, $nomsalle, $date_install)
    {
        $groupedData = [];

        foreach ($dataArray as $data) {
            if ($data['localisation'] == $nomsalle) {
                $date = $data['dateCapture'];

                // Convertir les dates en objets DateTime
                $dateCapture = new \DateTime($date);
                // Vérifier si la date est après la date d'installation
                if ($dateCapture > $date_install['date_install']) {

                    if (!isset($groupedData[$date])) {
                        $groupedData[$date] = [
                            'date' => $date,
                            'temp' => null,
                            'hum' => null,
                            'co2' => null,
                        ];
                    }

                    switch ($data['nom']) {
                        case 'temp':
                            $groupedData[$date]['temp'] = $data['valeur'];
                            break;
                        case 'hum':
                            $groupedData[$date]['hum'] = $data['valeur'];
                            break;
                        case 'co2':
                            $groupedData[$date]['co2'] = $data['valeur'];
                            break;
                    }
                }
            }
        }

        // Retirer les clés pour obtenir un tableau indexé

        // Fonction de comparaison pour trier les données par date décroissante
        uasort($groupedData, function ($a, $b) {
            $dateA = new \DateTime($a['date']);
            $dateB = new \DateTime($b['date']);
            return $dateB <=> $dateA; // Utilise l'opérateur 'spaceship' pour la comparaison
        });

        return array_values($groupedData);
    }*/

    public function extraireDonneeSurIntervalle($dataArray, $nomsalle, $date_install, $date_desinstall)
    {
        $groupedData = [];

        foreach ($dataArray as $data) {
            if ($data['localisation'] == $nomsalle) {
                $date = $data['dateCapture'];

                // Convertir les dates en objets DateTime
                $dateCapture = new \DateTime($date);


                // Vérifier si la date est dans l'intervalle spécifié
                if ($dateCapture >= $date_install && $dateCapture <= $date_desinstall) {
                    if (!isset($groupedData[$date])) {
                        $groupedData[$date] = [
                            'date' => $date,
                            'temp' => null,
                            'hum' => null,
                            'co2' => null,
                        ];
                    }

                    switch ($data['nom']) {
                        case 'temp':
                            $groupedData[$date]['temp'] = $data['valeur'];
                            break;
                        case 'hum':
                            $groupedData[$date]['hum'] = $data['valeur'];
                            break;
                        case 'co2':
                            $groupedData[$date]['co2'] = $data['valeur'];
                            break;
                    }
                }
            }
        }

        // Retirer les clés pour obtenir un tableau indexé
        uasort($groupedData, function ($a, $b) {
            $dateA = new \DateTime($a['date']);
            $dateB = new \DateTime($b['date']);
            return $dateB <=> $dateA; // Utilise l'opérateur 'spaceship' pour la comparaison
        });

        return array_values($groupedData);
    }


    public function getCaptureData($nomsalle, $type)
    {
        $client = new Client();
        $response = $client->request('GET', 'https://sae34.k8s.iut-larochelle.fr/api/captures', [
            'query' => [
                'nom' => $type,
                'nomsa' => $nomsalle,
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
        $client = new Client();
        $response = $client->request('GET', 'https://sae34.k8s.iut-larochelle.fr/api/captures/last', [
            'query' => [
                'nom' => $type,
                'nomsa' => $nomsalle,
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
    public function extraireToutesLesDonneeActuellesSalle($nomsalle, $date_install_array)
    {
        // Vérifier si la date d'installation est une chaîne de caractères et la convertir en DateTime
        $date_install = is_string($date_install_array['date_install'])
            ? new \DateTime($date_install_array['date_install'])
            : $date_install_array['date_install'];

        // Obtenez la date actuelle
        $dateFin = new \DateTime();

        // Appeler getCaptureDataInterval pour obtenir les données
        $donneesInterval = $this->getCaptureDataInterval($nomsalle, $date_install->format('Y-m-d'), $dateFin->format('Y-m-d'));

        $groupedData = [];

        foreach ($donneesInterval as $data) {
            $date = $data['dateCapture'];

            // Convertir les dates en objets DateTime
            $dateCapture = new \DateTime($date);

            if (!isset($groupedData[$date])) {
                $groupedData[$date] = [
                    'date' => $date,
                    'temp' => null,
                    'hum' => null,
                    'co2' => null,
                ];
            }

            switch ($data['nom']) {
                case 'temp':
                    $groupedData[$date]['temp'] = $data['valeur'];
                    break;
                case 'hum':
                    $groupedData[$date]['hum'] = $data['valeur'];
                    break;
                case 'co2':
                    $groupedData[$date]['co2'] = $data['valeur'];
                    break;
            }
        }

        // Tri par date décroissante
        uasort($groupedData, function ($a, $b) {
            $dateA = new \DateTime($a['date']);
            $dateB = new \DateTime($b['date']);
            return $dateB <=> $dateA;
        });

        return array_values($groupedData);
    }



}
