<?php

namespace App\Service;

class JsonDataHandling
{
    public function extraireDerniereDonneeSalle($dataArray, $nomsalle)
    {
        $derniereDonnee = [
            'hum' => null,
            'temp' => null,
            'co2' => null,
            'date_de_capture' => null
        ];

        $dates = [
            'hum' => new \DateTime('0000-00-00'),
            'temp' => new \DateTime('0000-00-00'),
            'co2' => new \DateTime('0000-00-00')
        ];

        foreach ($dataArray as $donnees) {
            // Vérifier si la salle correspond à celle spécifiée
            if ($donnees['localisation'] === $nomsalle) {
                $dateCapture = new \DateTime($donnees['dateCapture']);
                switch ($donnees['nom']) {
                    case 'hum':
                        if ($dateCapture > $dates['hum']) {
                            $derniereDonnee['hum'] = $donnees['valeur'];
                            $dates['hum'] = $dateCapture;
                        }
                        break;
                    case 'temp':
                        if ($dateCapture > $dates['temp']) {
                            $derniereDonnee['temp'] = $donnees['valeur'];
                            $dates['temp'] = $dateCapture;
                        }
                        break;
                    case 'co2':
                        if ($dateCapture > $dates['co2']) {
                            $derniereDonnee['co2'] = $donnees['valeur'];
                            $dates['co2'] = $dateCapture;
                        }
                        break;
                    default:
                        break;
                }
            }
        }

        // Mettre à jour la date de capture la plus récente
        $dateInitiale = new \DateTime('0000-00-00');
        if ($dates['hum'] > $dateInitiale || $dates['temp'] > $dateInitiale || $dates['co2'] > $dateInitiale) {
            // Au moins une des dates a été mise à jour, définit la date de capture la plus récente
            $derniereDonnee['date_de_capture'] = max($dates['hum'], $dates['temp'], $dates['co2'])->format('Y-m-d H:i:s');
        } else {
            // Aucune date n'a été mise à jour, définit 'date_de_capture' à null
            $derniereDonnee['date_de_capture'] = null;
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

        // Fonction de comparaison pour trier les données par date décroissante
        uasort($groupedData, function ($a, $b) {
            $dateA = new \DateTime($a['date']);
            $dateB = new \DateTime($b['date']);
            return $dateB <=> $dateA; // Utilise l'opérateur 'spaceship' pour la comparaison
        });

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
        uasort($groupedData, function ($a, $b) {
            $dateA = new \DateTime($a['date']);
            $dateB = new \DateTime($b['date']);
            return $dateB <=> $dateA; // Utilise l'opérateur 'spaceship' pour la comparaison
        });

        return array_values($groupedData);
    }
}
