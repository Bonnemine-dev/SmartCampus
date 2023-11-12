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

    public function listerSallesAvecLeurExperimentation($batiment = null, $salle = null)
    {
        {
            $queryBuilder = $this->createQueryBuilder('salle')
                ->select('salle.nom, salle.etage, salle.numero, salle.orientation, salle.nb_fenetres, salle.nb_ordis, experimentation.datedemande, experimentation.dateinstallation')
                ->leftJoin(Experimentation::class, 'experimentation', 'WITH', 'salle.id = experimentation.Salle')
                ->orderBy('salle.nom', 'ASC');
                if ($batiment !== null && $batiment !== '') {
                    $queryBuilder->andWhere('salle.batiment = :batiment')
                        ->setParameter('batiment', $batiment);
                }
                if ($salle !== null) {
                    $queryBuilder->andWhere('salle.nom LIKE :salle')
                        ->setParameter('salle', '%' . $salle . '%');
                }
                return $queryBuilder->getQuery()->getResult();
        }
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
