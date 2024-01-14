<?php

namespace App\Controller;

use App\Repository\SARepository;
use App\Service\JsonDataHandling;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @class APIController
 * Contrôleur pour gérer les requêtes API liées aux données capturées.
 * @extends AbstractController
 */
class APIController extends AbstractController
{
    private JsonDataHandling $jsonDataHandling;

    /**
     * Constructeur de APIController.
     * Initialise le contrôleur avec le service de gestion des données JSON.
     * @param JsonDataHandling $jsonDataHandling Le service de gestion des données JSON.
     */
    public function __construct(JsonDataHandling $jsonDataHandling)
    {
        $this->jsonDataHandling = $jsonDataHandling;
    }

    /**
     * Gère les requêtes API pour récupérer les données capturées.
     * Cette méthode peut filtrer les données par nom de salle et type.
     * @param string|null $nomsalle Le nom de la salle (filtre optionnel).
     * @param string|null $type Le type de capture (filtre optionnel).
     * @return Response La réponse JSON contenant les données capturées.
     * @Route('/api/captures/{nomsalle?}/{type?}', name: 'app_api')
     */
    #[Route('/api/captures/{nomsalle?}/{type?}', name: 'app_api')]
    public function index(string $nomsalle = null, string $type = null): Response
    {
        $data = $this->jsonDataHandling->getCaptureData($nomsalle, $type);
        return new JsonResponse($data);
    }

    /**
     * Gère les requêtes API pour récupérer un nombre limité de données capturées.
     * Peut filtrer par type, nom de salle et limite le nombre de résultats.
     * @param string|null $type Le type de capture (filtre optionnel).
     * @param string|null $nomsalle Le nom de la salle (filtre optionnel).
     * @param int|null $count Le nombre maximum de résultats à retourner (filtre optionnel).
     * @return Response La réponse JSON contenant les données capturées limitées.
     * @Route('/api/captures/{nomsalle?}/{type?}/{count?}', name: 'app_api_limit')
     */
    #[Route('/api/captures/{nomsalle?}/{type?}/{count?}', name: 'app_api_limit')]
    public function limit(string $type = null, string $nomsalle = null, int $count = null): Response
    {
        $data = $this->jsonDataHandling->getCaptureDataLimited($nomsalle, $type, $count);
        return new JsonResponse($data);
    }

    /**
     * Gère les requêtes API pour récupérer les données capturées dans un intervalle de temps.
     * Peut filtrer par type, nom de salle et les dates de début et de fin.
     * @param string $type Le type de capture (obligatoire).
     * @param string $date1 La date de début de l'intervalle (obligatoire).
     * @param string $date2 La date de fin de l'intervalle (obligatoire).
     * @param string|null $nomsalle Le nom de la salle (filtre optionnel).
     * @return Response La réponse JSON contenant les données capturées dans l'intervalle.
     * @Route('/api/captures/interval/{nomsalle?}/{type}/{date1}/{date2}', name: 'app_api_interval')
     */
    #[Route('/api/captures/interval/{nomsalle?}/{type}/{date1}/{date2}', name: 'app_api_interval')]
    public function interval(string $type, string $date1, string $date2, string $nomsalle = null): Response
    {
        $data = $this->jsonDataHandling->getCaptureDataInterval($nomsalle, $type, $date1, $date2);
        return new JsonResponse($data);
    }

    #[Route('/api/captures/moyenne/par/type/{type?}', name: 'app_moyenne_type')]
    public function moyenne(string $type, CacheInterface $cache): Response
    {
        // Clé de cache unique pour chaque type
        $cacheKey = 'moyenne_par_type_' . $type;

        // Vérifier si les données sont en cache
        $data = $cache->get($cacheKey, function (ItemInterface $item) use ($type) {
            // Expiration du cache au bout de 5 minutes (intervale de récupération des données par les SA)
            $item->expiresAfter(350);
            // Récupérer les données si elles ne sont pas en cache
            return $this->jsonDataHandling->getMoyenneParType($type);
        });

        return new JsonResponse($data);
    }

    #[Route('/api/captures/dernieres/donnees/salle/{nomsalle?}', name: 'app_derniere_donnees_salle')]
    public function derniereDonneesSalle(string $nomsalle, CacheInterface $cache): Response
    {
        // Clé de cache unique pour chaque salle
        $cacheKey = 'dernieres_donnees_salle_' . $nomsalle;

        // Vérifier si les données sont en cache
        $data = $cache->get($cacheKey, function (ItemInterface $item) use ($nomsalle) {
            // Expiration du cache au bout de 5 minutes (intervale de récupération des données par les SA)
            $item->expiresAfter(350);
            // Récupérer les données si elles ne sont pas en cache
            return $this->jsonDataHandling->extraireDerniereDonneeSalle($nomsalle);
        });

        return new JsonResponse($data);
    }


}
