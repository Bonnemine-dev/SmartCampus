<?php

namespace App\Repository;

use App\Entity\Experimentation;
use App\Entity\SA;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SA>
 *
 * @method SA|null find($id, $lockMode = null, $lockVersion = null)
 * @method SA|null findOneBy(array $criteria, array $orderBy = null)
 * @method SA[]    findAll()
 * @method SA[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SARepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SA::class);
    }

    public function compteSASansExperimentation()
    {
        return $this->createQueryBuilder('sa')
            ->select('count(sa.id)')
            ->leftJoin(Experimentation::class, 'experimentation', 'WITH', 'sa.id = experimentation.SA')
            ->where('experimentation.id IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function saNonUtiliser():?sa
    {
        $sa = $this->findOneBy(['etat' => 'Disponible']);
        $sa->setEtat('En_preparation');
        return $sa;
    }


//    /**
//     * @return SA[] Returns an array of SA objects
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

//    public function findOneBySomeField($value): ?SA
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
