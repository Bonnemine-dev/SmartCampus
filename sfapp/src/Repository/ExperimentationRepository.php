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
    public function __construct(ManagerRegistry $registry, SalleRepository $salleRepository, SARepository $saRepository)
    {
        parent::__construct($registry, Experimentation::class);
        $this->salleRepository = $salleRepository;
        $this->saRepository = $saRepository;
    }

    /*
     * Ajoute une experimentation pour la salle de nom $salle
     */
    public function ajouterExperimentation($salle): void
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
    public function estExistante($nomSalle): bool
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


    /*
     * Vérifie si il existe des experimentation à installer
     */
    public function trouveExperimentationDemandeInstallation()
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
    public function findOneByPasRetiree($salle)
    {
        $idSalle = $this->salleRepository->nomSalleId($salle);
        return $this->createQueryBuilder('experimentation')
            ->where('experimentation.etat != 3 and experimentation.Salles =' . $idSalle->getId())
            ->getQuery()
            ->getResult();
    }

    /*
     * Supprime une experimentation pour la salle de nom $salle
     */
    public function supprimerExperimentation($salle): array
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
    private function supprimerExperimentationDemandeInstallation($exp, $idSalle): void
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
    private function retireExperimentationInstallee($exp): void
    {
        $exp->setEtat(EtatExperimentation::demandeRetrait);
        $exp->setDatedemande(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        $this->persisteEtVide($exp);
    }

    /*
     * Supprime du repository
     */
    private function supprimerEtVide($entity): void
    {
        $em = $this->getEntityManager();
        $em->remove($entity);
        $em->flush();
    }

    /*
     * Persiste dans le repository
     */
    private function persisteEtVide($entity): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }

    /*
     * Fonction de filtrage pour la liste des salles en cours d'analyse
     */
    public function filtreExperimentationAnalyse($etages = null, $orientation = null, $ordinateurs = null, $sa = null)
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
    private function requeteCommune(): \Doctrine\ORM\QueryBuilder
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
    private function conditionsSA($sa)
    {
        switch ($sa) {
            case 0:
                return 'experimentation.datedemande IS NULL AND experimentation.dateinstallation IS NULL';
            case 1:
                return 'experimentation.datedemande IS NOT NULL AND experimentation.dateinstallation IS NOT NULL';
            case 2:
                return 'experimentation.datedemande IS NOT NULL AND experimentation.dateinstallation IS NULL';
        }
    }

    /*
     * Supprime les expérimentations inutiles
     */
    private function enleveExperimentationsInutiles($exp)
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
    public function rechercheExperimentationAnalyse($batiment = null, $salle = null)
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
     * Liste les salles avec leurs dernières données
     */
    public function listerSallesAvecDonnees(array $dataArray, array $verif): array
    {
        $salles = [];

        foreach ($dataArray as $item) {
            $salle = $item['localisation'];
            if ($this->existeSalle($salle, $verif)) {
                $this->majDonneesSalle($salles, $item, $salle);
            }
        }

        return $salles;
    }

    /*
     * Vérifie si une salle existe
     */
    private function existeSalle($salle, $verif): bool
    {
        foreach ($verif as $veri){
            if ($veri['nom_salle'] === $salle) {
                return true;
            }
        }
        return false;
    }

    /*
     * Mets à jour le tableau de données pour mettre les dernières récupérées
     */
    private function majDonneesSalle(&$salles, $item, $salle): void
    {
        $index = array_search($salle, array_column($salles, 'localisation'));

        if ($index === false || $item['dateCapture'] >= $salles[$index]['dateCapture']) {
            $this->ajouteOuMajDonnees($salles, $index, $item, $salle);
        }
    }

    /*
     * Ajoute si la salle n'existe pas dans les données et mets à jouer
     */
    private function ajouteOuMajDonnees(&$salles, $index, $item, $salle): void
    {
        if ($index === false) {
            $salles[] = [
                'localisation' => $salle,
                'co2' => null,
                'hum' => null,
                'temp' => null,
                'dateCapture' => null,
            ];
            $index = count($salles) - 1; // L'index de la nouvelle salle
        }

        // Remplir les valeurs correspondantes avec les dernières données
        switch ($item['nom']) {
            case 'co2':
                $salles[$index]['co2'] = $item['valeur'];
                break;
            case 'hum':
                $salles[$index]['hum'] = $item['valeur'];
                break;
            case 'temp':
                $salles[$index]['temp'] = $item['valeur'];
                break;
        }
        $salles[$index]['dateCapture'] = $item['dateCapture'];
    }

    /*
     * Fait la moyenne de toutes les dernières valeurs des salles
     */
    public function moyennesDonnees(array $dataArray): array
    {
        $salles = $this->listerSallesAvecDonnees($dataArray, $this->salleRepository->listerSalles());
        return $this->calculsMoyennes($salles);
    }

    /*
     * Fait les calculs des moyennes
     */
    private function calculsMoyennes($salles): array
    {
        $tempValues = $humValues = $co2Values = [];

        foreach ($salles as $data) {
            $tempValues[] = $data['temp'];
            $humValues[] = $data['hum'];
            $co2Values[] = $data['co2'];
        }

        return [
            'temp_moy' => $this->moyenne($tempValues),
            'hum_moy' => $this->moyenne($humValues),
            'co2_moy' => $this->moyenne($co2Values)
        ];
    }

    /*
     * Fait le calcul général pour une moyenne
     */
    private function moyenne($values): float|int|null
    {
        return count($values) > 0 ? array_sum($values) / count($values) : null;
    }


    /*
     * Modifie l'etat du experimentation à $etat pour la salle de nom $salle
     */
    public function modifierEtat($etat, $salle): void
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
    private function MajEtatEtPersist($exp, $etat): void
    {
        $exp->setEtat($etat);
        if ($etat == EtatExperimentation::installee) {
            $exp->setDateinstallation(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        }
        else if ($etat == EtatExperimentation::retiree) {
            $exp->setDatedesinstallation(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        }
        if ($etat === EtatExperimentation::retiree) {
            $exp->getSA()->setDisponible(1);
        }

        $this->getEntityManager()->persist($exp);
        $this->getEntityManager()->flush();
    }

    /*
     * Tri les expérimentations par salle et par date de capture dans l'ordre décroissant et supprime les doublons
     */
    public function triExperimentation($exp)
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
    public function etatExperimentation($salle) : EtatExperimentation
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
    public function listerLesIntervallesArchives($nomSalle)
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
    public function extraireDateInstallExpActuelle($nomSalle)
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
    public function etatExp($nomSalle)
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
    public function trouveExperimentationsParNomSalle($nomSalle)
    {
        $queryBuilder = $this->createQueryBuilder('exp')
            ->join('exp.Salles', 'salle')
            ->where('salle.nom = :nomSalle')
            ->setParameter('nomSalle', $nomSalle);

        return $queryBuilder->getQuery()->getResult();
    }

}
