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
    // Le constructeur initialise le repository avec le manager d'entités et l'entité associée.
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SA::class);
    }

    public function compteSASansExperimentation()
    {
        // Requête pour compter les SA sans expérimentation.
        return $this->createQueryBuilder('sa')
            ->select('count(sa.id)')
            ->leftJoin(Experimentation::class, 'experimentation', 'WITH', 'sa.id = experimentation.SA')
            ->where('experimentation.id IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Fonction qui retourne le premier SA disponible
    public function saNonUtiliser():?sa
    {
        // Requête pour sélectionner un SA disponible.
        $sa = $this->findOneBy(['etat' => 'Disponible']);
        $sa->setEtat('En_preparation');
        return $sa;
    }

    //Fonction qui change l'etat du SA a disponible (si le sa est enlever de l'experimentation par exemple)
    public function suppressionExp($sa)
    {
        // Mettre à jour l'état du SA.
        $sa->setEtat('Disponible');
    }

    public function toutLesSA()
    {
        // Requête pour récupérer tous les SA.
        return $this->createQueryBuilder('sa')
            ->select('sa')
            ->getQuery()
            ->getResult();
    }

}
