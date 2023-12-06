<?php

namespace App\Repository;

use App\Config\EtatSA;
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
            ->where('sa.disponible = 1')
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Fonction qui retourne le premier SA disponible
    public function saNonUtiliser():?sa
    {
        // Requête pour sélectionner un SA disponible.
        $sa = $this->findOneBy(['disponible' => 1]);
        $sa->setDisponible(0);
        return $sa;
    }

    //Fonction qui change l'etat du SA a disponible (si le sa est enlever de l'experimentation par exemple)
    public function suppressionExp($sa)
    {
        // Mettre à jour l'état du SA.
        $sa->setDisponible(1);
    }

    public function toutLesSA()
    {
        $queryBuilder = $this->createQueryBuilder('sa')
            ->select('sa.nom as nom_sa, sa.disponible');

        // Exécutez la requête et retournez les résultats.
        return $queryBuilder->getQuery()->getResult();
    }

    public function rechercheSA($nom = null)
    {
        // Requête pour sélectionner les salles en fonction des critères spécifiés.
        $queryBuilder = $this->createQueryBuilder('sa')
            ->select('sa.nom, sa.etat, sa.numero,sa.disponible')
            ->orderBy('sa.nom', 'ASC');

        if ($nom !== null) {
            $queryBuilder->andWhere('sa.nom = :nom')
                ->setParameter('nom', $nom);
        }

        // Exécutez la requête et retournez les résultats.
        return $queryBuilder->getQuery()->getResult();
    }

    public function ajoutSA($nom = null)
    {
        $sa = new SA();
        $sa->setEtat(EtatSA::eteint);
        $sa->setNom($nom);
        $sa->setNumero(0);
        $sa->setDisponible(1);

        // Obtenez le gestionnaire d'entités et persistez l'entité
        $entityManager = $this->getEntityManager();
        $entityManager->persist($sa);
        $entityManager->flush();
    }

    public function existeDeja($nom = null)
    {
        return $this->findOneBy(['nom' => $nom]);
    }
}
