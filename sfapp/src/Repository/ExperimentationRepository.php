<?php

namespace App\Repository;

use App\Config\EtatExperimentation;
use App\Config\EtatSA;
use App\Entity\Experimentation;
use App\Entity\Salle;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
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
    private SalleRepository $salleRepository;
    private SARepository $saRepository;

    // Le constructeur initialise le repository avec le manager d'entités et l'entité associée,
    // ainsi que les repositories des entités Salle et SA.
    public function __construct(ManagerRegistry $registry, SalleRepository $salleRepository, SARepository $saRepository)
    {
        parent::__construct($registry, Experimentation::class);
        $this->salleRepository = $salleRepository;
        $this->saRepository = $saRepository;
    }

    /*
     * Ajoute une experimentation pour la salle de nom $salle
     */
    public function ajouterExperimentation(string $salle): void
    {
        // Créer une nouvelle instance de l'entité Experimentation
        $experimentation = new Experimentation();

        // Récupérer l'ID de la salle et définir les salles de l'expérimentation
        $id = $this->salleRepository->nomSalleId($salle);
        $experimentation->setSalles($id);

        // Définir la date de demande avec le fuseau horaire de Paris
        $dateDemande = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $experimentation->setDatedemande($dateDemande);

        // Définir l'état et le SA de l'expérimentation
        $experimentation->setEtat(EtatExperimentation::demandeInstallation);
        $experimentation->setSA($this->saRepository->saNonUtiliser());

        // Persister et flush l'entité
        $this->getEntityManager()->persist($experimentation);
        $this->getEntityManager()->flush();
    }


    /*
     * Vérifie si une experimentation pour la salle nomSalle existe ou non
     */
    public function estExistante(string $nomSalle): bool
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->select('count(e.id)')
            ->join('e.Salles', 's')
            ->where('s.nom = :nomSalle')
            ->andWhere('e.etat BETWEEN :etat1 AND :etat2')
            ->setParameters([
                'nomSalle' => $nomSalle,
                'etat1' => EtatExperimentation::demandeInstallation,
                'etat2' => EtatExperimentation::demandeRetrait
            ]);

        return $queryBuilder->getQuery()->getSingleScalarResult() > 0;
    }


    /**
     * @return array<int, array{
     *     nom_salle: string,
     *     nom_sa: string,
     *     datedemande: DateTime,
     *     dateinstallation: DateTime,
     *     etat: int
     * }>
     */
    public function trouveExperimentationDemandeInstallation(): array
    {
        $queryBuilder = $this->createQueryBuilder('experimentation')
            ->select([
                'salle.nom AS nom_salle',
                'sa.nom AS nom_sa',
                'experimentation.datedemande',
                'experimentation.dateinstallation',
                'CASE WHEN experimentation.etat = :etat_demande_installation THEN 0
                  WHEN experimentation.etat = :etat_installee THEN 1
                  WHEN experimentation.etat = :etat_demandeRetrait THEN 2
                  WHEN experimentation.etat = :etat_retiree THEN 4
                  ELSE 4 END AS etat'
            ])
            ->join('experimentation.Salles', 'salle')
            ->join('experimentation.SA', 'sa')
            ->orderBy('salle.nom', 'ASC')
            ->setParameters([
                'etat_demande_installation' => EtatExperimentation::demandeInstallation,
                'etat_installee' => EtatExperimentation::installee,
                'etat_demandeRetrait' => EtatExperimentation::demandeRetrait,
                'etat_retiree' => EtatExperimentation::retiree
            ]);

        return $queryBuilder->getQuery()->getResult();
    }

    /*
     * Cherche les expérimentations qui ne sont pas retirées
     */
    /**
     * @return array<int, Experimentation>
     */
    public function findOneByPasRetiree(string $salle): array
    {
        $idSalle = $this->salleRepository->nomSalleId($salle);
        return $this->createQueryBuilder('experimentation')
            ->where('experimentation.etat != 3 and experimentation.Salles =' . $idSalle->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $salle
     * @return array<int, EtatExperimentation>
     */
    public function supprimerExperimentation(string $salle): array
    {
        $idSalle = $this->salleRepository->nomSalleId($salle);
        $exp = $this->findOneByPasRetiree($salle);
        $exp = $exp[0];
        $listeEtat = [$exp->getEtat(), null];

        $exp = $this->findOneBy(['Salles' => $idSalle, 'etat' => EtatExperimentation::demandeInstallation]);

        if ($exp) {
            $this->supprimerExperimentationDemandeInstallation($exp, $idSalle);
        } else {
            $exp = $this->findOneBy(['Salles' => $idSalle, 'etat' => EtatExperimentation::installee]);
            if ($exp) {
                $this->retireExperimentationInstallee($exp);
            }
        }

        $listeEtat[1] = $exp->getEtat();
        return $listeEtat;
    }

    /*
     * Retire une expérimentation qui était en demande d'installation
     */
    private function supprimerExperimentationDemandeInstallation(Experimentation $exp, Salle $idSalle): void
    {
        $this->saRepository->suppressionExp($exp->getSA());
        $this->supprimerEtVide($exp);
        $this->createQueryBuilder('experimentation')
            ->delete()
            ->where('experimentation.etat != 3 and experimentation.Salles = :idSalle')
            ->setParameter('idSalle', $idSalle->getId())
            ->getQuery()
            ->execute();
    }

    /*
     * Modifie une expérimentation qui était installée en demande de retrait
     */
    private function retireExperimentationInstallee(Experimentation $exp): void
    {
        $exp->setEtat(EtatExperimentation::demandeRetrait);
        $exp->setDatedemande(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        $this->persisteEtVide($exp);
    }

    /*
     * Supprime du repository
     */
    private function supprimerEtVide(Experimentation $entity): void
    {
        $em = $this->getEntityManager();
        $em->remove($entity);
        $em->flush();
    }

    /*
     * Persiste dans le repository
     */
    private function persisteEtVide(Experimentation $entity): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }

    /*
     * Fonction de filtrage pour la liste des salles en cours d'analyse
     */
    /**
     * @param array<int, int> $etages
     * @param array<int, string> $orientation
     * @param int|null $ordinateurs
     * @param int|null $sa
     * @return array<int, array{
     *   nom: string,
     *   etage: int,
     *   numero: int,
     *   orientation: string,
     *   nb_fenetres: int,
     *   nb_ordis: int,
     *   datedemande: DateTime,
     *   dateinstallation: DateTime,
     *   etat: EtatExperimentation,
     *   sa_etat: EtatSA
     *   }>
     */
    public function filtreExperimentationAnalyse(array $etages = null, array $orientation = null, int $ordinateurs = null, int $sa = null): array
    {
        $queryBuilder = $this->requeteCommune();

        if (!empty($etages) && $etages !== null) {
            $queryBuilder->andWhere('salle.etage IN (:etages)')
                ->setParameter('etages', $etages);
        }

        if (!empty($orientation) && $orientation !== null) {
            $queryBuilder->andWhere('salle.orientation IN (:orientation)')
                ->setParameter('orientation', $orientation);
        }

        if ($ordinateurs !== null) {
            $ordinateursCondition = $ordinateurs === 0 ? 'salle.nb_ordis = 0' : 'salle.nb_ordis > 0';
            $queryBuilder->andWhere($ordinateursCondition);
        }

        if ($sa !== null) {
            $saCondition = $this->conditionsSA($sa);
            $queryBuilder->andWhere($saCondition);
        }

        return $this->enleveExperimentationsInutiles($queryBuilder->getQuery()->getResult());
    }

    /*
     * Requête commune à la fonction de filtrage et de recherche (fonction qui récupère tout)
     */
    public function requeteCommune(): \Doctrine\ORM\QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('experimentation')
            ->select([
                'salle.nom',
                'salle.etage',
                'salle.numero',
                'salle.orientation',
                'salle.nb_fenetres',
                'salle.nb_ordis',
                'experimentation.datedemande',
                'experimentation.dateinstallation',
                'experimentation.etat',
                'sa.etat AS sa_etat'
            ])
            ->join('experimentation.Salles', 'salle')
            ->join('experimentation.SA', 'sa')
            ->where('experimentation.etat IN (:etats)')
            ->setParameter('etats', [EtatExperimentation::installee, EtatExperimentation::demandeRetrait]);

        return $queryBuilder;
    }

    /*
     * Selon le numéro de filtre pour les SA, ajoute une condition à la requête
     */
    private function conditionsSA(int $sa): string
    {
        switch ($sa) {
            case 0:
                return 'experimentation.datedemande IS NULL AND experimentation.dateinstallation IS NULL';
            case 1:
                return 'experimentation.datedemande IS NOT NULL AND experimentation.dateinstallation IS NOT NULL';
            case 2:
                return 'experimentation.datedemande IS NOT NULL AND experimentation.dateinstallation IS NULL';

        }
        return '';
    }

    /*
     * Supprime les expérimentations inutiles
     */
    /**
     * @param array<int, array{
     * nom: string,
     * etage: int,
     * numero: int,
     * orientation: string,
     * nb_fenetres: int,
     * nb_ordis: int,
     * datedemande: DateTime,
     * dateinstallation: DateTime,
     * etat: EtatExperimentation,
     * sa_etat: EtatSA
     * }> $exp
     *
     * @return array<int, array{
     *  nom: string,
     *  etage: int,
     *  numero: int,
     *  orientation: string,
     *  nb_fenetres: int,
     *  nb_ordis: int,
     *  datedemande: DateTime,
     *  dateinstallation: DateTime,
     *  etat: EtatExperimentation,
     *  sa_etat: EtatSA
     *  }>
     */
    public function enleveExperimentationsInutiles(array $exp): array
    {
        $len = count($exp);
        for ($i = 0; $i < $len; $i++) {
            if ($exp[$i]['sa_etat'] == null or $exp[$i]['etat'] == 4 or $exp[$i]['etat'] == EtatExperimentation::retiree or $exp[$i]['etat'] == EtatExperimentation::demandeInstallation) {
                unset($exp[$i]);
            }
        }

        return $exp;
    }

    /*
     * Fonction de recherche
     */
    /**
     * @param int|null $batiment
     * @param string|null $salle
     * @return array<int, array{
     *    nom: string,
     *    etage: int,
     *    numero: int,
     *    orientation: string,
     *    nb_fenetres: int,
     *    nb_ordis: int,
     *    datedemande: DateTime,
     *    dateinstallation: DateTime,
     *    etat: EtatExperimentation,
     *    sa_etat: EtatSA
     *    }>
     */
    public function rechercheExperimentationAnalyse(int $batiment = null, string $salle = null): array
    {
        $queryBuilder = $this->requeteCommune();

        if ($batiment !== null) {
            $queryBuilder->andWhere('salle.batiment = :batiment')
                ->setParameter('batiment', $batiment);
        }

        if ($salle !== null) {
            $queryBuilder->andWhere('salle.nom LIKE :salle')
                ->setParameter('salle', '%' . $salle . '%');
        }

        return $this->enleveExperimentationsInutiles($queryBuilder->getQuery()->getResult());
    }


    /*
     * Modifie l'etat du experimentation à $etat pour la salle de nom $salle
     */
    public function modifierEtat(EtatExperimentation $etat, string $salle): void
    {
        $exp = $this->findOneByPasRetiree($salle);
        $exp = $exp[0];

        if ($exp) {
            $this->MajEtatEtPersist($exp, $etat);
        }
    }

    /*
     * Mets à jour l'état et persiste dans le repository
     */
    private function MajEtatEtPersist(Experimentation $exp, EtatExperimentation $etat): void
    {
        $exp->setEtat($etat);
        if ($etat == EtatExperimentation::installee) {
            $exp->setDateinstallation(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        }
        else if ($etat == EtatExperimentation::retiree) {
            $exp->setDatedesinstallation(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        }
        if ($etat === EtatExperimentation::retiree) {
            $exp->getSA()->setDisponible(true);
        }

        $this->getEntityManager()->persist($exp);
        $this->getEntityManager()->flush();
    }

    /*
     * Tri les expérimentations par salle et par date de capture dans l'ordre décroissant et supprime les doublons
     */
    /**
     * @param array<int, array{
     * nom_salle: string,
     * nom_sa: string,
     * datedemande: DateTime,
     * dateinstallation: DateTime,
     * etat: int
     * }> $exp
     * @return array<int, array{
     * nom_salle: string,
     * nom_sa: string,
     * datedemande: DateTime,
     * dateinstallation: DateTime,
     * etat: int
     * }>
     */
    public function triExperimentation(array $exp): array
    {
        usort($exp, function($a, $b) {
            // Tri par nom_salle
            if ($a['nom_salle'] != $b['nom_salle']) {
                return $a['nom_salle'] <=> $b['nom_salle'];
            }

            // Si nom_salle est identique, tri par datedemande en ordre décroissant
            return $b['datedemande'] <=> $a['datedemande'];
        });

        $len = count($exp);
        if (count($exp) > 2) {
            $salle = $exp[0]['nom_salle'];
            for ($i = 0; $i < $len - 1; $i++) {
                if ($salle == $exp[$i + 1]['nom_salle']) {
                    unset($exp[$i + 1]);
                } else {
                    $salle = $exp[$i + 1]['nom_salle'];
                }
            }
        }
        return $exp;
    }

    /*
     * Récupère l'état d'une experimentation pour la salle de nom $salle
     */
    public function etatExperimentation(string $salle) : EtatExperimentation
    {
        $exp = $this->findOneByPasRetiree($salle);
        if (count($exp) == 0) {
            return EtatExperimentation::retiree;
        }
        $Exp = $exp[0];
        return $Exp->getEtat();
    }

    /*
     * Liste les expérimentations passées d'une salle
     */
    /**
     * @return array<int, array{
     * date_install: DateTime,
     * date_desinstall: DateTime
     * }>
     */
    public function listerLesIntervallesArchives(string $nomSalle): array
    {
        $queryBuilder = $this->createQueryBuilder('experimentation')
            ->select(['experimentation.dateinstallation AS date_install', 'experimentation.datedesinstallation AS date_desinstall'])
            ->join('experimentation.Salles', 'salle')
            ->where('salle.nom = :nomSalle')
            ->andWhere('experimentation.datedesinstallation IS NOT NULL')
            ->setParameter('nomSalle', $nomSalle);

        return $queryBuilder->getQuery()->getResult();
    }

    /*
     * Récupère l'expérimentation en cours d'une salle
     */
    /**
     * @return array<string, DateTime>
     */
    public function extraireDateInstallExpActuelle(string $nomSalle): array
    {
        return $this->createQueryBuilder('experimentation')
            ->select('experimentation.dateinstallation AS date_install')
            ->join('experimentation.Salles', 'salle')
            ->where('salle.nom = :nomSalle')
            ->andWhere('experimentation.etat IN (:etats)')
            ->setParameter('nomSalle', $nomSalle)
            ->setParameter('etats', [EtatExperimentation::installee, EtatExperimentation::demandeRetrait])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /*
     * Récupère l'état d'une expérimentation d'une salle
     */
    /**
     * @return array<int, array{
     * etat_exp: EtatExperimentation
     * }>
     */
    public function etatExp(string $nomSalle): array
    {
        return $this->createQueryBuilder('experimentation')
            ->select('experimentation.etat AS etat_exp')
            ->join('experimentation.Salles', 'salle')
            ->where('salle.nom = :nomSalle')
            ->setParameter('nomSalle', $nomSalle)
            ->getQuery()
            ->getResult();
    }


    /*
     * Fonctions qui retourne une liste des experimentations qui ont eu lieu dans une salle de nom $nomSalle
     */
    /**
     * @return array<int, Experimentation>
     */
    public function trouveExperimentationsParNomSalle(string $nomSalle): array
    {
        $queryBuilder = $this->createQueryBuilder('exp')
            ->join('exp.Salles', 'salle')
            ->where('salle.nom = :nomSalle')
            ->setParameter('nomSalle', $nomSalle);

        return $queryBuilder->getQuery()->getResult();
    }

}
