<?php

namespace App\Service;

class JsonDataHandling
{
    public function extraireDerniereDonneeSalle($dataArray, $nomsalle)
    {
        $derniereDonnee = ['hum' => null, 'temp' => null, 'co2' => null];

        foreach ($dataArray as $donnees) {
            // Vérifier si la salle correspond à celle spécifiée
            if ($donnees['localisation'] === $nomsalle) {
                switch ($donnees['nom']) {
                    case 'hum':
                        $derniereDonnee['hum'] = $donnees['valeur'];
                    case 'temp':
                        $derniereDonnee['temp'] = $donnees['valeur'];
                        break;
                    case 'co2':
                        $derniereDonnee['co2'] = $donnees['valeur'];
                        break;
                    default:
                        break;
                }
            }
        }

        return $derniereDonnee;
    }
    public function extraireToutesLesDonneeActuellesSalle($dataArray, $nomsalle, $date_install)
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
    return array_values($groupedData);
}


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
    return array_values($groupedData);
}
}
