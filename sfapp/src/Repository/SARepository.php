<?php

namespace App\Repository;

use App\Config\EtatExperimentation;
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

    /*
     * Compte le nombre de SA sans expérimentation
     */
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

    /*
     * Enlève les SA inutiles
     */
    public function trierSA($resultat) {
        foreach ($resultat as &$exp) {
            if ($exp['exp_etat'] == EtatExperimentation::retiree) {
                $exp['salle_nom'] = null;
            }
        }

        $resultat = array_values($resultat);
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
        return $resultat;
    }

    /*
     * Liste tous les SA
     */
    public function toutLesSA()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder
            ->select([
                'sa.nom as sa_nom',
                'salle.nom as salle_nom',
                'sa.etat as sa_etat',
                'experimentation.etat as exp_etat'
            ])
            ->from('App\Entity\SA', 'sa')
            ->leftJoin('App\Entity\Experimentation', 'experimentation', 'WITH', 'sa.id = experimentation.SA')
            ->leftJoin('App\Entity\Salle', 'salle', 'WITH', 'experimentation.Salles = salle.id')
            ->orderBy('sa.nom', 'ASC');

        // Exécuter la requête
        $resultat = $queryBuilder->getQuery()->getResult();

        $resultat = $this->trierSA($resultat);

        // Retourner true si une expérimentation est trouvée, sinon false
        return $resultat;
    }

    /*
     * Fonction de filtre des SA
     */
    public function filtrerSAGestionSA($etat = null, $localisation = null)
    {

        $exp = $this->toutLesSA();

        if (!empty($etat) && $etat !== null) {
            $etatsFiltres = array_map(function ($one) {
                switch ($one) {
                    case 0:
                        return EtatSA::eteint;
                    case 1:
                        return EtatSA::marche;
                    case 2:
                        return EtatSA::probleme;
                    default:
                        return null;
                }
            }, $etat);

            $exp = array_filter($exp, function ($item) use ($etatsFiltres) {
                return in_array($item['sa_etat'], $etatsFiltres);
            });
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

        return $exp;
    }

    /*
     * Fonction de recherche des SA
     */
    public function rechercheSA($contient_ce_string = null)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('sa.nom as sa_nom', 'salle.nom as salle_nom', 'sa.etat as sa_etat', 'experimentation.etat as exp_etat')
            ->from('App\Entity\SA', 'sa')
            ->leftJoin('App\Entity\Experimentation', 'experimentation', 'WITH', 'sa.id = experimentation.SA')
            ->leftJoin('App\Entity\Salle', 'salle', 'WITH', 'experimentation.Salles = salle.id')
            ->where(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->like('sa.nom', ':contient_ce_string'),
                    $queryBuilder->expr()->like('salle.nom', ':contient_ce_string')
                )
            )
            ->setParameter('contient_ce_string', '%' . $contient_ce_string . '%');

        $resultat = $queryBuilder->getQuery()->getResult();
        $resultat = $this->trierSA($resultat);

        return $resultat;
    }

    /*
     * Ajouter un SA
     */
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

    /*
     * Vérifie s'il y a déjà un SA existant selon le nom
     */
    public function existeDeja($nom = null)
    {
        return $this->findOneBy(['nom' => $nom]);
    }

    /*
     * Supprimer un SA
     */
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

    /*
     * Change l'état du SA
     */
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

    /*
     * Regarde les salles associés aux SA
     */
    public function salle_associe_sa($nomsa){
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('salle.nom')
            ->from('App\Entity\Salle', 'salle')
            ->join('App\Entity\Experimentation', 'experimentation', 'WITH', 'salle.id = experimentation.Salles')
            ->join('App\Entity\SA', 'sa', 'WITH', 'experimentation.SA = sa.id')
            ->where('sa.nom = :nomsa')
            ->andWhere($queryBuilder->expr()->in('experimentation.etat', [1, 2]))
            ->setParameter('nomsa', $nomsa);

        return $queryBuilder->getQuery()->getOneOrNullResult();

    }
}