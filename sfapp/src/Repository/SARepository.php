<?php

namespace App\Repository;

use App\Config\EtatExperimentation;
use App\Config\EtatSA;
use App\Entity\Experimentation;
use App\Entity\SA;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @class SARepository
 *  Repository pour gérer les opérations de base de données liées aux entités SA (Système d'Automatisation).
 * @extends ServiceEntityRepository<SA>
 *
 * @method SA|null find($id, $lockMode = null, $lockVersion = null)
 * @method SA|null findOneBy(array $criteria, array $orderBy = null)
 * @method SA[]    findAll()
 * @method SA[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SARepository extends ServiceEntityRepository
{
    /**
     * Constructeur de SARepository.
     * Initialise le repository avec le manager d'entités Doctrine.
     * @param ManagerRegistry $registry Le gestionnaire de l'entité Doctrine.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SA::class);
    }

    /**
     * Compte le nombre de SA (Système d'Automatisation) disponibles sans expérimentation.
     * @return int Le nombre de SA disponibles.
     */
    public function compteSASansExperimentation(): int
    {
        // Requête pour compter les SA sans expérimentation.
        return $this->createQueryBuilder('sa')
            ->select('count(sa.id)')
            ->where('sa.disponible = 1')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Sélectionne le premier SA disponible et le marque comme utilisé.
     * @return SA|null Retourne l'entité SA disponible ou null si aucun n'est disponible.
     */
    public function saNonUtiliser(): ?sa
    {
        // Requête pour sélectionner un SA disponible.
        $sa = $this->findOneBy(['disponible' => 1]);
        $sa->setDisponible(false);
        return $sa;
    }

    /**
     * Met à jour l'état d'un SA pour le marquer comme disponible.
     * @param SA $sa L'entité SA à mettre à jour.
     * @return void
     */
    public function suppressionExp(SA $sa): void
    {
        // Mettre à jour l'état du SA.
        $sa->setDisponible(true);
    }

    /**
     * Trie et nettoie une liste de SA pour enlever les éléments inutiles.
     * @param array<int, array{
     * sa_nom: string,
     * salle_nom: string,
     * sa_etat: EtatSA,
     * exp_etat: EtatExperimentation
     * }> $resultat La liste des SA à trier.
     *
     * @return array<int, array{
     * sa_nom: string,
     * salle_nom: string,
     * sa_etat: EtatSA,
     * exp_etat: EtatExperimentation
     * }> La liste triée des SA.
     */
    public function trierSA(array $resultat): array
    {
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

    /**
     * Récupère la liste de tous les SA avec les détails des expérimentations associées.
     * @return array<int, array{
     *  sa_nom: string,
     *  salle_nom: string,
     *  sa_etat: EtatSA,
     *  exp_etat: EtatExperimentation
     *  }> Liste de tous les SA avec des informations détaillées.
     */
    public function toutLesSA(): array
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

    /**
     * Filtre les SA selon l'état et la localisation spécifiés.
     * @param array<int, int>|null $etat Les états des SA à filtrer.
     * @param array<int, string>|null $localisation Les localisations à filtrer.
     * @return array<int, array{
     * sa_nom: string,
     * salle_nom: string,
     * sa_etat: EtatSA,
     * exp_etat: EtatExperimentation
     * }> Liste filtrée des SA.
     */
    public function filtrerSAGestionSA(array $etat = null, array $localisation = null): array
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

    /**
     * Recherche les SA contenant une chaîne de caractères spécifique dans leur nom ou celui de leur salle associée.
     * @param string|null $contient_ce_string La chaîne de caractères à rechercher.
     * @return array<int, array{
     *  sa_nom: string,
     *  salle_nom: string,
     *  sa_etat: EtatSA,
     *  exp_etat: EtatExperimentation
     *  }> Liste des SA correspondants à la recherche.
     */
    public function rechercheSA(string $contient_ce_string = null): array
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

    /**
     * Ajoute un nouveau SA avec les informations spécifiées.
     * @param string|null $nom Le nom du nouveau SA.
     * @return void
     */
    public function ajoutSA(string $nom = null): void
    {
        $sa = new SA();
        $sa->setEtat(EtatSA::eteint);
        $sa->setNom($nom);
        $sa->setNumero(0);
        $sa->setDisponible(true);

        // Obtenez le gestionnaire d'entités et persistez l'entité
        $entityManager = $this->getEntityManager();
        $entityManager->persist($sa);
        $entityManager->flush();
    }

    /**
     * Vérifie l'existence d'un SA selon son nom.
     * @param string|null $nom Le nom du SA à vérifier.
     * @return SA|null L'entité SA si elle existe, sinon null.
     */
    public function existeDeja(string $nom = null): ?SA
    {
        return $this->findOneBy(['nom' => $nom]);
    }

    /**
     * Supprime un SA spécifié par son nom.
     * @param string $nomsa Le nom du SA à supprimer.
     * @return bool True si la suppression est réussie, false sinon.
     */
    public function supprimerSA(string $nomsa): bool
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

    /**
     * Change l'état d'un SA associé à une salle donnée.
     * @param string $nomsalle Le nom de la salle associée au SA.
     * @param EtatSA $etat Le nouvel état à attribuer au SA.
     * @return void
     */
    public function changerEtatSA(string $nomsalle, EtatSA $etat) : void
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

    /**
     * Trouve les salles associées à un SA donné.
     * @param string $nomsa Le nom du SA.
     * @return array<string, string>|null Liste des salles associées ou null si aucune salle n'est associée.
     */
    public function salle_associe_sa(string $nomsa): ?array
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('salle.nom')
            ->from('App\Entity\Salle', 'salle')
            ->join('App\Entity\Experimentation', 'experimentation', 'WITH', 'salle.id = experimentation.Salles')
            ->join('App\Entity\SA', 'sa', 'WITH', 'experimentation.SA = sa.id')
            ->where('sa.nom = :nomsa')
            ->andWhere($queryBuilder->expr()->in('experimentation.etat', [1, 2]))
            ->setParameter('nomsa', $nomsa);

        $result = $queryBuilder->getQuery()->getResult();

        // Si le résultat est vide, retourner null
        if (empty($result)) {
            return null;
        }

        // Sinon, retourner le premier élément du résultat sous forme de tableau
        return $result;
    }

    /**
     * Met à jour l'état des SA en fonction de données spécifiques d'expérimentation.
     * @param array $listeSallesAvecDonnees Les données d'expérimentation pour chaque salle.
     * @return void
     */
    public function sa_eteint_probleme(string $nomsalle, array $data): void {
        if ($data['date_de_capture'] < date('Y-m-d H:i:s', strtotime('-10 minutes'))) {
            $this->changerEtatSA($nomsalle, EtatSA::eteint);
        }
        elseif ($data['co2'] == null or $data['temp'] == null or $data['hum'] == null or $data['co2'] < 300 or $data['co2'] > 5000 or $data['temp'] < 0 or $data['temp'] > 50 or $data['hum'] < 0 or $data['hum'] > 100) {
            $this->changerEtatSA($nomsalle, EtatSA::probleme);
        } else {
            $this->changerEtatSA($nomsalle, EtatSA::marche);
        }
    }

}