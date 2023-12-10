<?php

namespace App\Repository;

use App\Config\EtatExperimentation;
use App\Entity\Experimentation;
use App\Entity\Salle;
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

    public function listerSalles()
    {
        // Requête pour sélectionner les salles avec des informations supplémentaires de l'expérimentation.
        $dql = '
        SELECT salle.nom as nom_salle, salle.etage, salle.numero, salle.orientation, salle.nb_fenetres, salle.nb_ordis,
               experimentation.datedemande, experimentation.dateinstallation,
               CASE
                   WHEN experimentation.etat = :etat_demande_installation THEN 0
                   WHEN experimentation.etat = :etat_installee THEN 1
                   WHEN experimentation.etat = :etat_demandeRetrait THEN 2
                   WHEN experimentation.etat = :etat_retiree THEN 3
                   ELSE 4
               END AS etat
        FROM App\Entity\Salle salle
        LEFT JOIN App\Entity\Experimentation experimentation WITH salle.id = experimentation.Salles
        ORDER BY salle.nom ASC
    ';

        $query = $this->getEntityManager()->createQuery($dql);

        $query->setParameter('etat_demande_installation', EtatExperimentation::demandeInstallation)
            ->setParameter('etat_installee', EtatExperimentation::installee)
            ->setParameter('etat_demandeRetrait', EtatExperimentation::demandeRetrait)
            ->setParameter('etat_retiree', EtatExperimentation::retiree);

        return $query->getResult();
    }

    public function rechercheSallePlanExp($batiment = null, $salle = null)
    {
        // Requête pour filtrer les salles selon les critères spécifiés.
        $queryBuilder = $this->createQueryBuilder('salle')
            ->select('salle.nom as nom_salle, sa.nom as nom_sa, experimentation.datedemande, salle.etage, salle.numero, salle.orientation, salle.nb_fenetres, salle.nb_ordis, experimentation.dateinstallation,
                CASE
                    WHEN experimentation.etat = :etat_demande_installation THEN 0
                    WHEN experimentation.etat = :etat_installee THEN 1
                    WHEN experimentation.etat = :etat_demandeRetrait THEN 2
                    WHEN experimentation.etat = :etat_retiree THEN 4
                    ELSE 4
                END AS etat')
            ->leftJoin('App\Entity\Experimentation', 'experimentation', 'WITH', 'salle.id = experimentation.Salles')
            ->leftJoin('App\Entity\SA', 'sa', 'WITH', 'sa.id = experimentation.SA')
            ->orderBy('salle.nom', 'ASC');

        $queryBuilder->setParameter('etat_demande_installation', EtatExperimentation::demandeInstallation)
            ->setParameter('etat_installee', EtatExperimentation::installee)
            ->setParameter('etat_demandeRetrait', EtatExperimentation::demandeRetrait)
            ->setParameter('etat_retiree', EtatExperimentation::retiree);

        if ($batiment !== null) {
            $queryBuilder->andWhere('salle.batiment = :batiment')
                ->setParameter('batiment', $batiment);
        }

        if (!empty($salle)) {
            $queryBuilder->andWhere('salle.nom LIKE :salle')
                ->setParameter('salle', '%' . $salle . '%');
        }

        // Exécutez la requête et retournez les résultats.
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Filtre les salles selon les critères spécifiés.
     *
     * @param array|null $etages
     * @param array|null $orientation
     * @param int|null $ordinateurs
     * @param int|null $sa
     * @return array
     */
    public function filtrerSallePlanExp($etages = null, $orientation = null, $ordinateurs = null, $sa = null)
    {
        // Requête pour filtrer les salles selon les critères spécifiés.
        $queryBuilder = $this->createQueryBuilder('salle')
            ->select('salle.nom as nom_salle, sa.nom as nom_sa, experimentation.datedemande, salle.etage, salle.numero, salle.orientation, salle.nb_fenetres, salle.nb_ordis, experimentation.dateinstallation,
                CASE
                    WHEN experimentation.etat = :etat_demande_installation THEN 0
                    WHEN experimentation.etat = :etat_installee THEN 1
                    WHEN experimentation.etat = :etat_demandeRetrait THEN 2
                    WHEN experimentation.etat = :etat_retiree THEN 4
                    ELSE 4
                END AS etat')
            ->leftJoin('App\Entity\Experimentation', 'experimentation', 'WITH', 'salle.id = experimentation.Salles')
            ->leftJoin('App\Entity\SA', 'sa', 'WITH', 'sa.id = experimentation.SA')
            ->orderBy('salle.nom', 'ASC');

        $queryBuilder->setParameter('etat_demande_installation', EtatExperimentation::demandeInstallation)
            ->setParameter('etat_installee', EtatExperimentation::installee)
            ->setParameter('etat_demandeRetrait', EtatExperimentation::demandeRetrait)
            ->setParameter('etat_retiree', EtatExperimentation::retiree);

        if (!empty($etages) && $etages !== null) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('salle.etage', ':etages'))
                ->setParameter('etages', $etages);
        }

        if (!empty($orientation) && $orientation !== null) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('salle.orientation', ':orientation'))
                ->setParameter('orientation', $orientation);
        }

        if ($ordinateurs !== null) {
            if ($ordinateurs === 0) {
                $queryBuilder->andWhere('salle.nb_ordis = 0');
            } elseif ($ordinateurs === 1) {
                $queryBuilder->andWhere('salle.nb_ordis > 0');
            }
        }

        if ($sa !== null) {
            switch ($sa) {
                case 0:
                    $queryBuilder->andWhere('experimentation.datedemande IS NULL AND experimentation.dateinstallation IS NULL');
                    break;
                case 1:
                    $queryBuilder->andWhere('experimentation.datedemande IS NOT NULL AND experimentation.dateinstallation IS NOT NULL');
                    break;
                case 2:
                    $queryBuilder->andWhere('experimentation.datedemande IS NOT NULL AND experimentation.dateinstallation IS NULL');
                    break;
            }
        }

        // Exécutez la requête et retournez les résultats.
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Récupère l'entité Salle en fonction du nom de la salle.
     *
     * @param string $salle
     * @return Salle|null
     */
    public function nomSalleId($salle): ?Salle
    {
        return $this->findOneBy(['nom' => $salle]);
    }

    public function triListeSalle($salle)
    {
        $len = count($salle);
        if(count($salle)>2){
            $nom_salle = $salle[0]['nom_salle'];
            for($i = 0 ; $i < $len-1 ; $i ++)
            {
                if($nom_salle == $salle[$i+1]['nom_salle'])
                {
                    unset($salle[$i]);
                }else{
                    $nom_salle = $salle[$i+1]['nom_salle'];
                }
            }
        }
        return $salle;
    }
}
