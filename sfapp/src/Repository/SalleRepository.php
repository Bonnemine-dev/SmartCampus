<?php

namespace App\Repository;

use App\Config\EtatExperimentation;
use App\Config\EtatSA;
use App\Entity\Salle;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @class SalleRepository
 * Repository pour gérer les opérations de base de données liées aux entités Salle.
 * @extends ServiceEntityRepository<Salle>
 *
 * @method Salle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Salle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Salle[]    findAll()
 * @method Salle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SalleRepository extends ServiceEntityRepository
{
    /**
     * Constructeur de SalleRepository.
     * Initialise le repository avec le manager d'entités Doctrine.
     * @param ManagerRegistry $registry Le gestionnaire de l'entité Doctrine.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Salle::class);
    }

    /**
     * Crée une requête commune pour le repository.
     * Construit un QueryBuilder pour les salles avec les détails nécessaires pour les expérimentations.
     * @return \Doctrine\ORM\QueryBuilder Le QueryBuilder avec la requête construite.
     */
    public function requeteCommune(): \Doctrine\ORM\QueryBuilder
    {
        return $this->createQueryBuilder('salle')
            ->select('batiment.id as id_batiment, salle.nom as nom_salle, sa.nom as nom_sa, experimentation.datedemande, salle.etage, salle.numero, salle.orientation, salle.nb_fenetres, salle.nb_ordis, experimentation.dateinstallation, experimentation.datedesinstallation,
            CASE
                WHEN experimentation.etat = :etat_demande_installation THEN 0
                WHEN experimentation.etat = :etat_installee THEN 1
                WHEN experimentation.etat = :etat_demandeRetrait THEN 2
                WHEN experimentation.etat = :etat_retiree THEN 3
                ELSE 4
            END AS etat')
            ->leftJoin('App\Entity\Experimentation', 'experimentation', 'WITH', 'salle.id = experimentation.Salles')
            ->leftJoin('App\Entity\SA', 'sa', 'WITH', 'sa.id = experimentation.SA')
            ->leftJoin('App\Entity\Batiment', 'batiment', 'WITH', 'salle.batiment = batiment.id')
            ->orderBy('salle.nom', 'ASC')
            ->setParameter('etat_demande_installation', EtatExperimentation::demandeInstallation)
            ->setParameter('etat_installee', EtatExperimentation::installee)
            ->setParameter('etat_demandeRetrait', EtatExperimentation::demandeRetrait)
            ->setParameter('etat_retiree', EtatExperimentation::retiree);
    }

    /**
     * Recherche les salles et leurs expérimentations associées selon les critères spécifiés.
     *  Peut filtrer par batiment et/ou nom de salle.
     * @param int|null $batiment L'identifiant du bâtiment (filtre optionnel).
     * @param string|null $salle Le nom de la salle (filtre optionnel).
     * @return array<int, array{
     * id_batiment: int,
     * nom_salle: string,
     * nom_sa: ?string,
     * datedemande: ?DateTime,
     * etage: int,
     * numero: int,
     * orientation: string,
     * nb_fenetres: int,
     * nb_ordis: int,
     * dateinstallation: ?DateTime,
     * datedesinstallation: ?DateTime,
     * etat: int
     * }> Liste des salles et leurs expérimentations correspondantes aux critères.
     */
    public function rechercheSallePlanExp(int $batiment = null, string $salle = null): array
    {
        // Requête pour filtrer les salles selon les critères spécifiés.
        $queryBuilder = $this->requeteCommune();
        $resultat = $this->triListeSalle($queryBuilder->getQuery()->getResult());

        if ($batiment !== null) {
            $resultat = array_filter($resultat, function ($item) use ($batiment) {
                return $item['id_batiment'] == $batiment;
            });
        }

        if (!empty($salle)) {
            $resultat = array_filter($resultat, function ($item) use ($salle) {
                return stripos($item['nom_salle'], $salle) !== false;
            });
        }

        // Exécutez la requête et retournez les résultats.
        return $resultat;
    }

    /**
     * Filtre les salles et leurs expérimentations selon des critères spécifiques.
     * Utilise des paramètres optionnels pour le filtrage par étage, orientation, nombre d'ordinateurs, et état du SA.
     * @param array<int, int> $etages Les étages à filtrer.
     * @param array<int, string> $orientation Les orientations à filtrer.
     * @param int|null $ordinateurs Le nombre d'ordinateurs à filtrer.
     * @param int|null $sa L'état du SA à filtrer.
     * @return array<int, array{
     *  id_batiment: int,
     *  nom_salle: string,
     *  nom_sa: ?string,
     *  datedemande: ?DateTime,
     *  etage: int,
     *  numero: int,
     *  orientation: string,
     *  nb_fenetres: int,
     *  nb_ordis: int,
     *  dateinstallation: ?DateTime,
     *  datedesinstallation: ?DateTime,
     *  etat: int
     *  }> Liste des salles filtrées avec les détails des expérimentations.
     */
    public function filtrerSallePlanExp(array $etages = null, array $orientation = null, int $ordinateurs = null, int $sa = null): array
    {
        // Requête pour filtrer les salles selon les critères spécifiés.
        $queryBuilder = $this->requeteCommune();

        $resultat = $this->triListeSalle($queryBuilder->getQuery()->getResult());

        if (!empty($etages)) {
            $resultat = array_filter($resultat, function ($item) use ($etages) {
                return in_array($item['etage'], $etages);
            });
        }

        // Filtrage par orientation
        if (!empty($orientation)) {
            $resultat = array_filter($resultat, function ($item) use ($orientation) {
                return in_array($item['orientation'], $orientation);
            });
        }

        if ($ordinateurs !== null) {
            $resultat = array_filter($resultat, function ($item) use ($ordinateurs) {
                if ($ordinateurs === 0) {
                    return $item['nb_ordis'] == 0;
                } elseif ($ordinateurs === 1) {
                    return $item['nb_ordis'] > 0;
                }
                return true;
            });
        }

        if ($sa !== null) {
            $resultat = array_filter($resultat, function ($item) use ($sa) {
                switch ($sa) {
                    case 0:
                        return $item['etat'] == 3 || $item['etat'] == 4;
                    case 1:
                        return $item['etat'] == 1 || $item['etat'] == 2;
                    case 2:
                        return $item['etat'] == 0;
                    default:
                        return true;
                }
            });
        }


        // Exécutez la requête et retournez les résultats.
        return $resultat;
    }

    /**
     * Récupère l'entité Salle correspondant au nom donné.
     * Recherche la salle par son nom.
     * @param string $salle Le nom de la salle.
     * @return Salle|null L'entité Salle correspondante ou null si non trouvée.
     */
    public function nomSalleId(string $salle): ?Salle
    {
        return $this->findOneBy(['nom' => $salle]);
    }

    /*
     * Tris des salles pour enlever les expérimentations inutiles
     */
    /**
     * Trie une liste de salles et élimine les doublons.
     *  Utilisé pour organiser et filtrer les salles et leurs expérimentations.
     * @param array<int, array{
          * id_batiment: int,
          * nom_salle: string,
          * nom_sa: ?string,
          * datedemande: ?DateTime,
          * etage: int,
          * numero: int,
          * orientation: string,
          * nb_fenetres: int,
          * nb_ordis: int,
          * dateinstallation: ?DateTime,
          * datedesinstallation: ?DateTime,
          * etat: int
          * }> $salle Liste des salles à trier.
     *
     * @return array<int, array{
     * id_batiment: int,
     * nom_salle: string,
     * nom_sa: ?string,
     * datedemande: ?DateTime,
     * etage: int,
     * numero: int,
     * orientation: string,
     * nb_fenetres: int,
     * nb_ordis: int,
     * dateinstallation: ?DateTime,
     * datedesinstallation: ?DateTime,
     * etat: int
     * }> Liste triée des salles avec expérimentations.
     */
    public function triListeSalle(array $salle): array
    {
        usort($salle, function($a, $b) {
            // Tri par nom_salle
            if ($a['nom_salle'] != $b['nom_salle']) {
                return $a['nom_salle'] <=> $b['nom_salle'];
            }

            // Si nom_salle est identique, tri par datedemande en ordre décroissant
            return $b['datedemande'] <=> $a['datedemande'];
        });


        $len = count($salle);
        if(count($salle)>2){
            $nom_salle = $salle[0]['nom_salle'];
            for($i = 0 ; $i < $len-1 ; $i ++)
            {
                if($nom_salle == $salle[$i+1]['nom_salle'])
                {
                    unset($salle[$i+1]);
                }else{
                    $nom_salle = $salle[$i+1]['nom_salle'];
                }
            }
        }
        return $salle;
    }

    /**
     * Recherche les informations SA associées à une salle donnée.
     * Trouve le SA et son état pour une salle spécifique.
     * @param string $nomsalle Le nom de la salle pour laquelle rechercher le SA.
     * @return array{
     * nom_sa: string,
     * etat_sa: EtatSA
     * } Résultat contenant le nom du SA et son état.
     */
    public function SAAssocie(string $nomsalle): array
    {
        $queryBuilder = $this->createQueryBuilder('salle')
            ->select('sa.nom as nom_sa, sa.etat as etat_sa')
            ->join('App\Entity\Experimentation', 'experimentation', 'WITH', 'salle.id = experimentation.Salles')
            ->join('App\Entity\SA', 'sa', 'WITH', 'experimentation.SA = sa.id')
            ->where('salle.nom = :nomSalle')
            ->andWhere('experimentation.etat IN (:etats)')
            ->setParameter('nomSalle', $nomsalle)
            ->setParameter('etats', [1, 2]);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
