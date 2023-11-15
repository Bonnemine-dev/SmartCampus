<?php

namespace App\Repository;

use App\Entity\Experimentation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Experimentation>
 *
 * @method Experimentation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Experimentation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Experimentation[]    findAll()
 * @method Experimentation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperimentationRepository extends ServiceEntityRepository
{
    private $salleRepository;
    private $saRepository;
    public function __construct(ManagerRegistry $registry , SalleRepository $salleRepository,SARepository $saRepository)
    {
        parent::__construct($registry, Experimentation::class);
        $this->salleRepository = $salleRepository;
        $this->saRepository = $saRepository;
    }

    public function ajouterExperimentation($salle)
    {
        // Créez une nouvelle instance de l'entité Experimentation
        $experimentation = new Experimentation();
        $id = $this->salleRepository->nomsalletoid($salle);
        $experimentation->setSalle($id);
        $dateDemande = new \DateTime();
        $experimentation->setDatedemande($dateDemande);
        $sa = $this->saRepository->saNonUtiliser();
        $experimentation->setSA($sa);

        // Obtenez le gestionnaire d'entités et persistez l'entité
        $entityManager = $this->getEntityManager();
        $entityManager->persist($experimentation);
        $entityManager->flush();
    }

    /*
     * Vérifie si une experimentation pour la salle nomSalle existe ou non
     */
    public function verifierExperimentation($nomSalle): bool
    {
        // Récupérer le gestionnaire d'entités
        $entityManager = $this->getEntityManager();

        // Créer une requête pour vérifier l'existence d'une expérimentation avec la salle donnée
        $query = $entityManager->createQuery(
            'SELECT e
            FROM App\Entity\Experimentation e
            JOIN e.Salle s
            WHERE s.nom = :nomSalle'
        )->setParameter('nomSalle', $nomSalle);

        // Exécuter la requête
        $resultat = $query->getResult();

        // Retourner true si une expérimentation est trouvée, sinon false
        return !empty($resultat);
    }

    public function findExperimentationsWithNullDateInstallation()
    {
        return $this->createQueryBuilder('experimentation')
            ->where('experimentation.dateinstallation IS NULL')
            ->orderBy('experimentation.datedemande', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function supprimerExperimentation($salle)
    {
        $idSalle = $this->salleRepository->nomsalletoid($salle);
        $Exp = $this->findOneBy(['Salle' => $idSalle]);
        $this->saRepository->supresionExp($Exp->getSA());

        // Get the entity manager and perform the delete operation

        $queryBuilder = $this->createQueryBuilder('experimentation');
        $queryBuilder
            ->delete()
            ->where('experimentation.Salle = '.$idSalle->getId())
            ->getQuery()
            ->execute();
    }

//    /**
//     * @return Experimentation[] Returns an array of Experimentation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Experimentation
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
