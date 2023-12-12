<?php

namespace App\Repository;

use App\Config\EtatExperimentation;
use App\Config\EtatSA;
use App\Entity\Experimentation;
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
            SELECT sa.nom as sa_nom, salle.nom as salle_nom, sa.etat as sa_etat, experimentation.etat as exp_etat
            FROM App\Entity\SA sa
            LEFT JOIN App\Entity\Experimentation experimentation WITH sa.id = experimentation.SA
            LEFT JOIN App\Entity\Salle salle WITH experimentation.Salles = salle.id
            ORDER BY sa_nom
        ');
        // Exécuter la requête
        $resultat = $query->getResult();
        foreach ($resultat as &$exp) {
            if ($exp['exp_etat'] == EtatExperimentation::retiree) {
                $exp['salle_nom'] = null;
            }
        }

        $len = count($resultat);
        for($i = 0;$i<$len;$i++){
            $nom_sa = $resultat[$i]['sa_nom'];
            $nombre_occurrences = 0;
            foreach ($resultat as $element) {
                if ($element['sa_nom'] === $nom_sa) {
                    $nombre_occurrences++;
                }
            }

            if($resultat[$i]['exp_etat'] == EtatExperimentation::retiree and $resultat[$i]['salle_nom'] == null and $nombre_occurrences >= 2){
                unset($resultat[$i]);
            }
        }

        // Retourner true si une expérimentation est trouvée, sinon false
        return $resultat;
    }
    public function filtrerSAGestionSA($etat = null, $localisation = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder
            ->select('sa.nom as sa_nom', 'salle.nom as salle_nom', 'sa.etat as sa_etat', 'experimentation.etat as exp_etat')
            ->from('App\Entity\SA', 'sa')
            ->leftJoin('sa.experimentations', 'experimentation')
            ->leftJoin('experimentation.Salles', 'salle');

       
            if (!empty($etat) && $etat !== null) {
                foreach ($etat as $one) {
                    $queryBuilder->andWhere($queryBuilder->expr()->in('sa.etat', ':etat'));
                    switch ($one) {
                        case 0:
                            $queryBuilder->setParameter('etat', EtatSA::eteint);
                            break;
                        case 1:
                            $queryBuilder->setParameter('etat', EtatSA::marche);
                            break;
                        case 2:
                            $queryBuilder->setParameter('etat', EtatSA::probleme);
                            break;
                        default:
                            break;
                    }
                }
            }

        $exp = $queryBuilder->getQuery()->getResult();
        // Exécuter la requête
        foreach ($exp as &$one_exp) {
            if ($one_exp['exp_etat'] == EtatExperimentation::retiree) {
                $one_exp['salle_nom'] = null;
            }
        }

        $len = count($exp);
        for($i = 0;$i<$len;$i++){
            $nom_sa = $exp[$i]['sa_nom'];
            $nombre_occurrences = 0;
            foreach ($exp as $element) {
                if ($element['sa_nom'] === $nom_sa) {
                    $nombre_occurrences++;
                }
            }

            if($exp[$i]['exp_etat'] == EtatExperimentation::retiree and $exp[$i]['salle_nom'] == null and $nombre_occurrences >= 2){
                unset($exp[$i]);
            }
        }

        $exp = array_values($exp);

            if (count($localisation) === 1 && $localisation !== null) {
                if ($localisation[0] === 'salle') {
                    // Si $localisation est true, ajouter la condition pour salle.nom IS NOT NULL
                    $len = count($exp);
                    for($i = 0;$i<$len;$i++){
                        if($exp[$i]['exp_etat'] == EtatExperimentation::retiree or $exp[$i]['salle_nom'] == null){
                            unset($exp[$i]);
                        }
                    }
                } elseif ($localisation[0] === 'stock') {
                    // Si $localisation est false, ajouter la condition pour salle.nom IS NULL
                    $len = count($exp);
                    for($i = 0;$i<$len;$i++){
                        if($exp[$i]['exp_etat'] == EtatExperimentation::installee or $exp[$i]['exp_etat'] == EtatExperimentation::demandeInstallation or $exp[$i]['exp_etat'] == EtatExperimentation::demandeRetrait or $exp[$i]['sa_etat'] != EtatSA::eteint){
                            unset($exp[$i]);
                        }
                    }
                }
            }
            if ($localisation == null and $etat == null) {
                $exp = $this->toutLesSA();
            }

        return $exp;
    }
    public function rechercheSA($contient_ce_string = null)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery('
            SELECT sa.nom as sa_nom, salle.nom as salle_nom, sa.etat as sa_etat, experimentation.etat as exp_etat
            FROM App\Entity\SA sa
            LEFT JOIN App\Entity\Experimentation experimentation WITH sa.id = experimentation.SA
            LEFT JOIN App\Entity\Salle salle WITH experimentation.Salles = salle.id
            WHERE sa.nom LIKE CONCAT(\'%\', :contient_ce_string, \'%\') 
            OR salle.nom LIKE CONCAT(\'%\', :contient_ce_string, \'%\')
        ');
        // Exécuter la requête
        $query->setParameter('contient_ce_string', $contient_ce_string);
        $resultat = $query->getResult();

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
        if ($sa) {
            $entityManager = $this->getEntityManager();
            // Supprimer l'objet SA
            $entityManager->remove($sa);
            // Exécuter les changements dans la base de données
            $entityManager->flush();
            // Retourner true pour indiquer que la suppression a réussi
            return true;
        } else {
            return false;
        }
    }

    public function trisa($sa)
    {
        foreach ($sa as &$un_sa)
        {
            if ($un_sa['exp_etat'] == EtatExperimentation::retiree) {
                $un_sa['salle_nom'] = null;
            }
        }

        return $sa;

    }

    public function changerEtatSA($nomsalle, $etat) : void
    {
        $entityManager = $this->getEntityManager();
        $experimentationsRepository = $entityManager->getRepository(Experimentation::class);

        // Rechercher toutes les expérimentations liées à la salle avec le nom donné
        $experimentations = $experimentationsRepository->trouveExperimentationsParNomSalle($nomsalle);

        foreach ($experimentations as $experimentation) {
            $sa = $experimentation->getSA();

            if ($sa !== null) {
                $sa->setEtat($etat);
                $entityManager->persist($sa);
            }
        }

        // Appliquer les changements dans la base de données
        $entityManager->flush();
    }

    public function salle_associe_sa($nomsa){
        $dql = '
        SELECT salle.nom
        FROM App\Entity\Salle salle
        JOIN App\Entity\Experimentation experimentation WITH salle.id = experimentation.Salles
        JOIN App\Entity\SA sa WITH experimentation.SA = sa.id
        WHERE sa.nom = :nomsa 
        AND experimentation.etat IN (1,2)
    ';

        $query = $this->getEntityManager()->createQuery($dql);

        $query->setParameter('nomsa', $nomsa);

        return $query->getOneOrNullResult();
    }
}