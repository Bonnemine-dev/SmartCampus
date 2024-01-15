<?php

namespace App\Repository;

use App\Entity\Batiment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Batiment>
 *
 * @method Batiment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Batiment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Batiment[]    findAll()
 * @method Batiment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BatimentRepository extends ServiceEntityRepository
{
    /**
     * Constructeur de la classe BatimentRepository.
     *
     * Initialise le repository avec le ManagerRegistry (gestionnaire d'entités) et l'entité Batiment.
     *
     * @param ManagerRegistry $registry Le gestionnaire de registre des entités.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Batiment::class);
    }

    /**
     * Retourne un tableau associatif de bâtiments.
     *
     * La méthode exécute une requête DQL pour récupérer les id et noms des bâtiments.
     * Elle transforme ensuite les résultats en un tableau associatif où les clés sont les noms des bâtiments
     * et les valeurs sont leurs identifiants.
     *
     * @return array<string, int> Un tableau associatif où les clés sont les noms des bâtiments et les valeurs sont leurs identifiants.
     */
    public function tableauBatimentsNomID(): array
    {
        // Construction de la requête DQL pour sélectionner les id et noms des bâtiments.
        $batiments = $this->createQueryBuilder('b')
            ->select('b.id', 'b.nom')
            ->getQuery()
            ->getResult();

        // Transformation du tableau de résultats en un tableau associatif ['Nom du bâtiment' => 'id'].
        $batimentsArray = [];
        foreach ($batiments as $batiment) {
            $batimentsArray[$batiment['nom']] = $batiment['id'];
        }

        // Retour du tableau résultant.
        return $batimentsArray;
    }
}
