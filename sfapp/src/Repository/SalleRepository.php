<?php

namespace App\Repository;

use App\Config\EtatExperimentation;
use App\Config\EtatSA;
use App\Entity\Salle;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Salle>
 *
 * @method Salle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Salle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Salle[]    findAll()
 * @method Salle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SalleRepository extends ServiceEntityRepository
{
    // Le constructeur initialise le repository avec le manager d'entités et l'entité associée.
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Salle::class);
    }

    /*
     * Requête commune pour le repository
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

    /*
     * Fonction de recherche pour les expérimentations
     */
    /**
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
     * }>
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
     * Filtre les salles selon les critères spécifiés.
     * @param array<int, int> $etages
     * @param array<int, string> $orientation
     * @param int|null $ordinateurs
     * @param int|null $sa
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
     *  }>
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
     * Récupère l'entité Salle en fonction du nom de la salle.
     *
     * @param string $salle
     * @return Salle|null
     */
    public function nomSalleId(string $salle): ?Salle
    {
        return $this->findOneBy(['nom' => $salle]);
    }

    /*
     * Tris des salles pour enlever les expérimentations inutiles
     */
    /**
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
          * }> $salle
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
     * }>
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

    /*
     * Recherche les SA associés à une salle
     */
    /**
     * @return array{
     * nom_sa: string,
     * etat_sa: EtatSA
     * }
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
