<?php

namespace App\Repository;

use App\Config\EtatExperimentation;
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
    // Références aux autres repositories nécessaires.
    private $salleRepository;
    private $saRepository;

    // Le constructeur initialise le repository avec le manager d'entités et l'entité associée,
    // ainsi que les repositories des entités Salle et SA.
    public function __construct(ManagerRegistry $registry , SalleRepository $salleRepository,SARepository $saRepository)
    {
        parent::__construct($registry, Experimentation::class);
        $this->salleRepository = $salleRepository;
        $this->saRepository = $saRepository;
    }

    /*
     * Ajoute une experimentation pour la salle de nom $salle
     */
    public function ajouterExperimentation($salle)
    {
        // Définir le fuseau horaire sur Paris
        date_default_timezone_set('Europe/Paris');

        // Créez une nouvelle instance de l'entité Experimentation
        $experimentation = new Experimentation();
        $id = $this->salleRepository->nomSalleId($salle);
        $experimentation->setSalle($id);

        $dateDemande = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $experimentation->setDatedemande($dateDemande);
        $experimentation->setEtat(EtatExperimentation::demandeInstallation);

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
            WHERE s.nom = :nomSalle
            AND e.etat BETWEEN :etat1 AND :etat2'
        )->setParameter('nomSalle', $nomSalle)
            ->setParameter('etat1', EtatExperimentation::demandeInstallation)
            ->setParameter('etat2', EtatExperimentation::demandeRetrait);

        // Exécuter la requête
        $resultat = $query->getResult();

        // Retourner true si une expérimentation est trouvée, sinon false
        return !empty($resultat);
    }

    /*
     * Vérifie si il existe des experimentation à installer
     */
    public function trouveExperimentationsSansDateInstallation()
    {
        return $this->createQueryBuilder('experimentation')
            ->where('experimentation.dateinstallation IS NULL')
            ->orderBy('experimentation.datedemande', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /*
     * Supprime une experimentation pour la salle de nom $salle
     */
    public function supprimerExperimentation($salle)
    {
        $idSalle = $this->salleRepository->nomSalleId($salle);
        $Exp = $this->findOneBy(['Salle' => $idSalle]);
        $Exp->setEtat(EtatExperimentation::demandeRetrait);

        $entityManager = $this->getEntityManager();
        $entityManager->persist($Exp);
        $entityManager->flush();
    }

    public function trouveExperimentationsNonRetirer()
    {
        return $this->createQueryBuilder('experimentation')
            ->where('experimentation.etat = 0 or experimentation.etat = 1 or experimentation.etat = 2')
            ->getQuery()
            ->getResult();
    }
}
