<?php

namespace App\Service;

use App\Repository\ExperimentationRepository;
use App\Repository\SalleRepository;
use App\Repository\SARepository;
use DateTime;
use GuzzleHttp\Client;
use App\Config\EtatExperimentation;
use App\Config\EtatSA;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Classe JsonDataHandling pour gérer le traitement des données JSON.
 * Cette classe est responsable de la récupération, du traitement, et de la fourniture
 * de données JSON en provenance de différentes sources, notamment des bases de données
 * et des API externes.
 */
class JsonDataHandling
{

    /**
     * @var ExperimentationRepository
     * Référentiel pour accéder aux données des expérimentations.
     */
    private ExperimentationRepository $experimentationRepository;

    /**
     * @var SARepository
     * Référentiel pour accéder aux SA des salles.
     */
    private SARepository $saRepository;

    /**
     * @var array
     * Tableau contenant des informations sur les salles.
     */
    private array $salles;

    /**
     * Constructeur de la classe JsonDataHandling.
     * Initialise les référentiels nécessaires et prépare le tableau des salles.
     *
     * @param ManagerRegistry $managerRegistry Le gestionnaire de registre pour les entités Doctrine.
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->experimentationRepository = new ExperimentationRepository($managerRegistry, new SalleRepository($managerRegistry), new SARepository($managerRegistry));
        $this->saRepository = new SARepository($managerRegistry);
        $this->salles = [
            "D205" => ["nomSA" => "ESP-001", "idSA" => 1, "dbname" => "sae34bdk1eq1", "username" => "k1eq1"],
            "D206" => ["nomSA" => "ESP-002", "idSA" => 2, "dbname" => "sae34bdk1eq2", "username" => "k1eq2"],
            "D207" => ["nomSA" => "ESP-003", "idSA" => 3, "dbname" => "sae34bdk1eq3", "username" => "k1eq3"],
            "D204" => ["nomSA" => "ESP-004", "idSA" => 4, "dbname" => "sae34bdk2eq1", "username" => "k2eq1"],
            "D203" => ["nomSA" => "ESP-005", "idSA" => 5, "dbname" => "sae34bdk2eq2", "username" => "k2eq2"],
            "D303" => ["nomSA" => "ESP-006", "idSA" => 6, "dbname" => "sae34bdk2eq3", "username" => "k2eq3"],
            "D304" => ["nomSA" => "ESP-007", "idSA" => 7, "dbname" => "sae34bdl1eq1", "username" => "l1eq1"],
            "C101" => ["nomSA" => "ESP-008", "idSA" => 8, "dbname" => "sae34bdl1eq2", "username" => "l1eq2"],
            "D109" => ["nomSA" => "ESP-009", "idSA" => 9, "dbname" => "sae34bdl1eq3", "username" => "l1eq3"],
            "D106" => ["nomSA" => "ESP-010", "idSA" => 10, "dbname" => "sae34bdl2eq1", "username" => "l2eq1"],
            "D001" => ["nomSA" => "ESP-011", "idSA" => 11, "dbname" => "sae34bdl2eq2", "username" => "l2eq2"],
            "D002" => ["nomSA" => "ESP-012", "idSA" => 12, "dbname" => "sae34bdl2eq3", "username" => "l2eq3"],
            "D004" => ["nomSA" => "ESP-013", "idSA" => 13, "dbname" => "sae34bdm1eq1", "username" => "m1eq1"],
            "C004" => ["nomSA" => "ESP-014", "idSA" => 14, "dbname" => "sae34bdm1eq2", "username" => "m1eq2"],
            "C007" => ["nomSA" => "ESP-015", "idSA" => 15, "dbname" => "sae34bdm1eq3", "username" => "m1eq3"],
            "D201" => ["nomSA" => "ESP-016", "idSA" => 16, "dbname" => "sae34bdm2eq1", "username" => "m2eq1"],
            "D307" => ["nomSA" => "ESP-017", "idSA" => 17, "dbname" => "sae34bdm2eq2", "username" => "m2eq2"],
            "C005" => ["nomSA" => "ESP-018", "idSA" => 18, "dbname" => "sae34bdm2eq3", "username" => "m2eq3"]
        ];
    }

    /**
     * Récupère les informations des salles.
     * Cette méthode filtre et retourne les informations pertinentes des salles.
     *
     * @return array Tableau contenant les informations des salles.
     */
    public function getSalles(): array
    {
        $listeSalles = [];
        $listeExperimentations = $this->experimentationRepository->enleveExperimentationsInutiles($this->experimentationRepository->requeteCommune()->getQuery()->getResult());
        foreach ($listeExperimentations as $experimentation) {
            $nomSalle = $experimentation['nom'];
            foreach ($this->salles as $nomSalle2 => $infoSalle) {
                if ($nomSalle === $nomSalle2) {
                    $listeSalles[$nomSalle] = $infoSalle;
                }
            }
        }
        return $listeSalles;
    }

    /**
     * Récupère les données de capture pour une salle donnée.
     *
     * @param string $nomsalle Le nom de la salle.
     * @param string $type Le type de données à récupérer (temp, hum, etc.).
     * @return mixed Les données de capture sous forme de tableau ou autre format.
     */
    public function getCaptureData(string $nomsalle, string $type): mixed
    {
        $nomSA = $this->salles[$nomsalle]['nomSA'];
        $dbname = $this->salles[$nomsalle]['dbname'];

        $client = new Client();
        $response = $client->request('GET', 'https://sae34.k8s.iut-larochelle.fr/api/captures', [
            'query' => [
                'nom' => $type,
                'nomsa' => $nomSA,
                'page' => 1
            ],
            'headers' => [
                'accept' => 'application/json',
                'dbname' => $dbname,
                'username' => 'l2eq2',
                'userpass' => 'wiqnyt-fuqgyc-7vUhby'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Récupère une quantité limitée de données de capture pour une salle et un type de mesure spécifiques.
     *
     * @param string $nomsalle Nom de la salle.
     * @param string $type Type de données à récupérer.
     * @param int $count Nombre de données à récupérer.
     * @return mixed Les données de capture sous forme de tableau ou autre format.
     */
    public function getCaptureDataLimited(string $nomsalle, string $type, int $count): mixed
    {
        $nomSA = $this->salles[$nomsalle]['nomSA'];
        $dbname = $this->salles[$nomsalle]['dbname'];

        $client = new Client();
        $response = $client->request('GET', 'https://sae34.k8s.iut-larochelle.fr/api/captures/last', [
            'query' => [
                'nom' => $type,
                'nomsa' => $nomSA,
                'limit' => $count,
                'page' => 1
            ],
            'headers' => [
                'accept' => 'application/json',
                'dbname' => $dbname,
                'username' => 'l2eq2',
                'userpass' => 'wiqnyt-fuqgyc-7vUhby'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Récupère les données de capture pour une salle et un type de mesure sur un intervalle de temps donné.
     *
     * @param string $nomsalle Nom de la salle.
     * @param string $type Type de données à récupérer.
     * @param string $date1 Date de début de l'intervalle.
     * @param string $date2 Date de fin de l'intervalle.
     * @return mixed Les données de capture sous forme de tableau ou autre format.
     */
    public function getCaptureDataInterval(string $nomsalle, string $type, string $date1, string $date2): mixed
    {
        $dbname = $this->salles[$nomsalle]['dbname'];

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
                'dbname' => $dbname,
                'username' => 'l2eq2',
                'userpass' => 'wiqnyt-fuqgyc-7vUhby'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Calcule et retourne la moyenne des valeurs de capture pour un type de mesure donné.
     *
     * @param string $type Type de données pour lequel calculer la moyenne (température, humidité, etc.).
     * @return float La moyenne calculée pour le type de données spécifié.
     */
    public function getMoyenneParType(string $type): float
    {
        $somme = 0;
        $count = 0;

        foreach ($this->salles as $nomSalle => $infoSalle) {
            $donnee = $this->getCaptureDataLimited($nomSalle, $type, 1);

            //dump($donnee);

            if (!empty($donnee) && isset($donnee[0]['valeur']) && $donnee[0]['valeur'] !== "" && $donnee[0]['valeur'] > 0 && $donnee[0]['valeur'] < 5000 && $donnee[0]['valeur'] !== null)
            {
                //dump($donnee[0]['valeur']);
                $somme += floatval($donnee[0]['valeur']);
                $count++;
            }
        }

        $moyenneGlobale = $somme / $count;

        if ($type === "hum" || $type === "temp")
        {
            $moyenneGlobale = round($moyenneGlobale, 1);
        }
        else
        {
            $moyenneGlobale = round($moyenneGlobale);
        }
        return $moyenneGlobale;
    }

    /**
     * Extrait et retourne les dernières données de capture pour une salle donnée.
     *
     * @param string $nomsalle Nom de la salle pour laquelle extraire les données.
     * @return array<string, string> Tableau associatif des dernières données de capture pour la salle.
     */
    public function extraireDerniereDonneeSalle(string $nomsalle): array
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

                $dateCapture = $donnees[0]['dateCapture'];

                // Vérification du format de la date
                if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $dateCapture)) {
                    $dateCaptureObj = new \DateTime($dateCapture);

                    if ($derniereDonnee['date_de_capture'] === null || $dateCaptureObj > new \DateTime($derniereDonnee['date_de_capture'])) {
                        $derniereDonnee['date_de_capture'] = $dateCaptureObj->format('Y-m-d H:i:s');
                    }
                }
                else {
                    $derniereDonnee['date_de_capture'] = (new \DateTime())->format('Y-m-d H:i:s');
                }
            }
        }

        $this->saRepository->sa_eteint_probleme($nomsalle, $derniereDonnee);

        return $derniereDonnee;
    }

    /**
     * Extrait et retourne toutes les données de capture actuelles pour une salle donnée.
     *
     * @param string $nomsalle Nom de la salle.
     * @param array<string, DateTime> $date_install Date d'installation pour le filtre des données.
     * @return array<int, array{
     *     date: string,
     *     temp: string,
     *     hum: string,
     *     co2: string
     * }> Tableau des données de capture pour la salle.
     */
    public function extraireToutesLesDonneeActuellesSalle(string $nomsalle, array $date_install): array
    {
        $dateInstallString = $date_install['date_install']->format('Y-m-d');
        $dateActuelle = new \DateTime();
        $dateActuelleString = $dateActuelle->format('Y-m-d');

        $types = ['hum', 'temp', 'co2'];
        $groupedData = [];

        foreach ($types as $type) {
            $data = $this->getCaptureDataInterval($nomsalle, $type, $dateInstallString, $dateActuelleString);

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

    /**
     * Extrait et retourne les données de capture pour une salle donnée sur un intervalle de temps spécifique.
     *
     * @param string $nomsalle Nom de la salle.
     * @param DateTime $date_install Date de début de l'intervalle.
     * @param DateTime $date_desinstall Date de fin de l'intervalle.
     * @return array<int, array{
     *     date: string,
     *     temp: string,
     *     hum: string,
     *     co2: string
     * }> Tableau des données de capture pour la salle et l'intervalle spécifiés.
     */
    public function extraireDonneeSurIntervalle(string $nomsalle, DateTime $date_install, DateTime $date_desinstall): array
    {
        $dateInstallString = $date_install->format('Y-m-d');
        $dateDesinstallString = $date_desinstall->format('Y-m-d');

        $types = ['hum', 'temp', 'co2']; // Types de données à récupérer
        $groupedData = [];

        foreach ($types as $type) {
            // Remplacez cette partie par l'appel à votre API
            $data = $this->getCaptureDataInterval($nomsalle, $type, $dateInstallString, $dateDesinstallString);

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

}
