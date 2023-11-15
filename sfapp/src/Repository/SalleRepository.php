<?php

namespace App\Repository;

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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Salle::class);
    }

    public function listerSalles()
    {
        $queryBuilder = $this->createQueryBuilder('salle')
            ->select('salle.nom, salle.etage, salle.numero, salle.orientation, salle.nb_fenetres, salle.nb_ordis, experimentation.datedemande, experimentation.dateinstallation')
            ->leftJoin(Experimentation::class, 'experimentation', 'WITH', 'salle.id = experimentation.Salle')
            ->orderBy('salle.nom', 'ASC');
        return $queryBuilder->getQuery()->getResult();
    }

    public function rechercheSallePlanExp($batiment = null, $salle = null)
    {
        $queryBuilder = $this->createQueryBuilder('salle')
            ->select('salle.nom, salle.etage, salle.numero, salle.orientation, salle.nb_fenetres, salle.nb_ordis, experimentation.datedemande, experimentation.dateinstallation')
            ->leftJoin(Experimentation::class, 'experimentation', 'WITH', 'salle.id = experimentation.Salle')
            ->orderBy('salle.nom', 'ASC');

        if ($batiment !== null) {
            $queryBuilder->andWhere('salle.batiment = :batiment')
                ->setParameter('batiment', $batiment);
        }

        if (!empty($salle)) {
            $queryBuilder->andWhere('salle.nom LIKE :salle')
                ->setParameter('salle', '%' . $salle . '%');
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function filtrerSallePlanExp($etages = null, $orientation = null, $ordinateurs = null, $sa = null)
    {
        $queryBuilder = $this->createQueryBuilder('salle')
            ->select('salle')
            ->leftJoin(Experimentation::class, 'experimentation', 'WITH', 'salle.id = experimentation.Salle')
            ->orderBy('salle.nom', 'ASC');

        $queryBuilder->select('salle.nom, salle.etage, salle.numero, salle.orientation, salle.nb_fenetres, salle.nb_ordis, experimentation.datedemande, experimentation.dateinstallation');

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

        return $queryBuilder->getQuery()->getResult();
    }

    public function nomsalletoid($salle): ?Salle
    {
        return $this->findOneBy(['nom' => $salle]);
    }



//    /**
//     * @return Salle[] Returns an array of Salle objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Salle
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
