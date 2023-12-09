<?php

namespace App\Repository;

use App\Config\EtatSA;
use App\Entity\SA;
use App\Repository\ExperimentationRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;

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
    public function saNonUtiliser(): ?sa
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
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery('
            SELECT sa.nom as sa_nom, salle.nom as salle_nom, sa.etat as sa_etat
            FROM App\Entity\SA sa
            LEFT JOIN App\Entity\Experimentation experimentation WITH sa.id = experimentation.SA
            LEFT JOIN App\Entity\Salle salle WITH experimentation.Salles = salle.id
        ');
        // Exécuter la requête
        $resultat = $query->getResult();

        // Retourner true si une expérimentation est trouvée, sinon false
        return $resultat;
    }

    public function rechercheSA($contient_ce_string = null)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery('
            SELECT sa.nom as sa_nom, salle.nom as salle_nom, sa.etat as sa_etat
            FROM App\Entity\SA sa
            LEFT JOIN App\Entity\Experimentation experimentation WITH sa.id = experimentation.SA
            LEFT JOIN App\Entity\Salle salle WITH experimentation.Salles = salle.id
            WHERE sa.nom LIKE CONCAT(\'%\', :contient_ce_string, \'%\') 
            OR salle.nom LIKE CONCAT(\'%\', :contient_ce_string, \'%\')
        ');
        // Exécuter la requête
        $query->setParameter('contient_ce_string', $contient_ce_string);
        $resultat = $query->getResult();

        // Retourner true si une expérimentation est trouvée, sinon false
        return $resultat;
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

    public function supprimerSA($nomsa)
    {
        $sa = $this->findOneBy(['nom' => $nomsa]);
        if ($sa) 
        {
            $entityManager = $this->getEntityManager();
            // Supprimer l'objet SA
            $entityManager->remove($sa);
            // Exécuter les changements dans la base de données
            $entityManager->flush();
            // Retourner true pour indiquer que la suppression a réussi
            return true;
        } else 
        {
            return false;
        }
    }
}
