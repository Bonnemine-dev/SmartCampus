<?php

namespace App\Repository;

use App\Config\EtatExperimentation;
use App\Entity\Experimentation;
use App\Entity\Salle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\SimpleArrayType;
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
        $experimentation->setSalles($id);

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
            JOIN e.Salles s
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
    public function trouveExperimentations()
    {
        $dql = '
        SELECT salle.nom as nom_salle, sa.nom as nom_sa, experimentation.datedemande, experimentation.dateinstallation,
               CASE
                   WHEN experimentation.etat = :etat_demande_installation THEN 0
                   WHEN experimentation.etat = :etat_installee THEN 1
                   WHEN experimentation.etat = :etat_demandeRetrait THEN 2
                   WHEN experimentation.etat = :etat_retiree THEN 4
                   ELSE 4
               END AS etat
        FROM App\Entity\Salle salle
        JOIN App\Entity\Experimentation experimentation WITH salle.id = experimentation.Salles
        JOIN App\Entity\SA sa WITH sa.id = experimentation.SA
        ORDER BY salle.nom , etat ASC
    ';
        $query = $this->getEntityManager()->createQuery($dql);

        $query->setParameter('etat_demande_installation', EtatExperimentation::demandeInstallation)
            ->setParameter('etat_installee', EtatExperimentation::installee)
            ->setParameter('etat_demandeRetrait', EtatExperimentation::demandeRetrait)
            ->setParameter('etat_retiree', EtatExperimentation::retiree);

        return $query->getResult();
    }


    /*
     * Supprime une experimentation pour la salle de nom $salle
     */
    public function supprimerExperimentation($salle)
    {
        $idSalle = $this->salleRepository->nomSalleId($salle);
        $Exp = $this->createQueryBuilder('experimentation')
            ->where('experimentation.etat != 3 and experimentation.Salles ='.$idSalle->getId())
            ->getQuery()
            ->getResult();
        $Exp = $Exp[0];
        $listeEtat = [$Exp->getEtat(),null];

        if($Exp->getEtat() == EtatExperimentation::demandeInstallation){
            $this->saRepository->suppressionExp($Exp->getSA());
            $entityManager = $this->getEntityManager();
            $entityManager->persist($Exp);
            $entityManager->flush();
            $queryBuilder = $this->createQueryBuilder('experimentation');
            $queryBuilder
                ->delete()
                ->where('experimentation.etat != 3 and experimentation.Salles = '.$idSalle->getId())
                ->getQuery()
                ->execute();

        }
        elseif($Exp->getEtat() == EtatExperimentation::installee){
            $Exp->setEtat(EtatExperimentation::demandeRetrait);
            $Exp->setDateinstallation(null);
            $dateDemande = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
            $Exp->setDatedemande($dateDemande);
            $entityManager = $this->getEntityManager();
            $entityManager->persist($Exp);
            $entityManager->flush();
        }
        $listeEtat[1] = $Exp->getEtat();
        return $listeEtat;
    }

    public function extraireLesExperimentations()
    {
        $entityManager = $this->getEntityManager();
        
        $query = $entityManager->createQuery('
            SELECT salle.nom, salle.etage, salle.numero, salle.orientation, salle.nb_fenetres, salle.nb_ordis, experimentation.datedemande, experimentation.dateinstallation, experimentation.etat, sa.etat as sa_etat
            FROM App\Entity\Salle salle JOIN App\Entity\Experimentation experimentation WITH salle.id = experimentation.Salles JOIN App\Entity\SA sa WITH experimentation.SA = sa.id
            WHERE experimentation.etat = 1 OR experimentation.etat = 2
        ');
    
        // Exécuter la requête
        // Retourner true si une expérimentation est trouvée, sinon false
        return $query->getResult();
    }

    public function filtrerextraireLesExperimentations($etages = null, $orientation = null, $ordinateurs = null, $sa = null)
    {
        // Requête pour filtrer les salles selon les critères spécifiés.
        $queryBuilder = $this->salleRepository->createQueryBuilder('salle')
            ->select('salle.nom, salle.etage, salle.numero, salle.orientation, salle.nb_fenetres, salle.nb_ordis, experimentation.datedemande, experimentation.dateinstallation, experimentation.etat, sa.etat as sa_etat,
                CASE
                    WHEN experimentation.etat = :etat_demande_installation THEN 0
                    WHEN experimentation.etat = :etat_installee THEN 1
                    WHEN experimentation.etat = :etat_demandeRetrait THEN 2
                    WHEN experimentation.etat = :etat_retiree THEN 4
                    ELSE 4
                END AS etat')
            ->leftJoin('App\Entity\Experimentation', 'experimentation', 'WITH', 'salle.id = experimentation.Salles')
            ->leftJoin('App\Entity\SA', 'sa', 'WITH', 'sa.id = experimentation.SA')
            ->orderBy('salle.nom', 'ASC');

        $queryBuilder->setParameter('etat_demande_installation', EtatExperimentation::demandeInstallation)
            ->setParameter('etat_installee', EtatExperimentation::installee)
            ->setParameter('etat_demandeRetrait', EtatExperimentation::demandeRetrait)
            ->setParameter('etat_retiree', EtatExperimentation::retiree);

        if (!empty($etages) && $etages !== null) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('salle.etage', ':etages'))
                ->setParameter('etages', $etages);
        }

        if (!empty($orientation) && $orientation !== null) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('salle.orientation', ':orientation'))
                ->setParameter('orientation', $orientation);
        }

        if ($ordinateurs !== null) {
            if ($ordinateurs === 0) {
                $queryBuilder->andWhere('salle.nb_ordis = 0');
            } elseif ($ordinateurs === 1) {
                $queryBuilder->andWhere('salle.nb_ordis > 0');
            }
        }

        if ($sa !== null) {
            switch ($sa) {
                case 0:
                    $queryBuilder->andWhere('experimentation.datedemande IS NULL AND experimentation.dateinstallation IS NULL');
                    break;
                case 1:
                    $queryBuilder->andWhere('experimentation.datedemande IS NOT NULL AND experimentation.dateinstallation IS NOT NULL');
                    break;
                case 2:
                    $queryBuilder->andWhere('experimentation.datedemande IS NOT NULL AND experimentation.dateinstallation IS NULL');
                    break;
            }
        }

        // Exécutez la requête et retournez les résultats.
        $exp = $queryBuilder->getQuery()->getResult();
        $len = count($exp);
        for ($i = 0; $i < $len; $i++) {
            if ($exp[$i]['sa_etat'] == null) {
                unset($exp[$i]);
            }
        }
        return $exp;
    }

    public function rechercheextraireLesExperimentations($batiment = null, $salle = null)
    {
        // Requête pour filtrer les salles selon les critères spécifiés.
        $queryBuilder = $this->salleRepository->createQueryBuilder('salle')
            ->select('salle.nom, salle.etage, salle.numero, salle.orientation, salle.nb_fenetres, salle.nb_ordis, experimentation.datedemande, experimentation.dateinstallation, experimentation.etat, sa.etat as sa_etat,
                CASE
                    WHEN experimentation.etat = :etat_demande_installation THEN 0
                    WHEN experimentation.etat = :etat_installee THEN 1
                    WHEN experimentation.etat = :etat_demandeRetrait THEN 2
                    WHEN experimentation.etat = :etat_retiree THEN 4
                    ELSE 4
                END AS etat')
            ->leftJoin('App\Entity\Experimentation', 'experimentation', 'WITH', 'salle.id = experimentation.Salles')
            ->leftJoin('App\Entity\SA', 'sa', 'WITH', 'sa.id = experimentation.SA')
            ->orderBy('salle.nom', 'ASC');

        $queryBuilder->setParameter('etat_demande_installation', EtatExperimentation::demandeInstallation)
            ->setParameter('etat_installee', EtatExperimentation::installee)
            ->setParameter('etat_demandeRetrait', EtatExperimentation::demandeRetrait)
            ->setParameter('etat_retiree', EtatExperimentation::retiree);

        if ($batiment !== null) {
            $queryBuilder->andWhere('salle.batiment = :batiment')
                ->setParameter('batiment', $batiment);
        }

        if (!empty($salle)) {
            $queryBuilder->andWhere('salle.nom LIKE :salle')
                ->setParameter('salle', '%' . $salle . '%');
        }

        // Exécutez la requête et retournez les résultats.
        $exp = $queryBuilder->getQuery()->getResult();
        $len = count($exp);
        for ($i = 0; $i < $len; $i++) {
            if ($exp[$i]['sa_etat'] == null) {
                unset($exp[$i]);
            }
        }
        return $exp;
    }

    public function listerSallesAvecDonnees(array $dataArray, array $verif): array
    {
        // Initialiser le tableau résultat
        $salles = [];

        // Parcourir les données
        foreach ($dataArray as $item) {
            $salle = $item['localisation'];
            $test = 0;
            foreach ($verif as $veri){
                if($veri['nom_salle'] == $salle){
                    $test = 1;
                }
            }
            if($test == 1 ){
                // Trouver la salle dans le tableau
                $index = array_search($salle, array_column($salles, 'localisation'));

                // Si la salle n'est pas déjà dans le tableau, l'ajouter
                if ($index === false or $item['dateCapture'] >= $salles[$index]['dateCapture']) {
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
            }
        }

        return $salles;
    }

    public function moyennesDonnees(array $dataArray): array
    {
        $listsalle = $this->salleRepository->listerSalles();
        $salles = $this->listerSallesAvecDonnees($dataArray,$listsalle);
        // Initialiser les tableaux pour stocker les valeurs de chaque type de mesure
        $tempValues = [];
        $humValues = [];
        $co2Values = [];

        // 2. Organiser les données par salle
        foreach ($salles as $data) {
            // 3. Stocker les valeurs dans les tableaux correspondants
            $tempValues[] = $data['temp'];
            $humValues[] = $data['hum'];
            $co2Values[] = $data['co2'];
        }

        // 4. Calculer la moyenne pour chaque type de mesure
        $temp_moy = count($tempValues) > 0 ? array_sum($tempValues) / count($tempValues) : null;
        $hum_moy = count($humValues) > 0 ? array_sum($humValues) / count($humValues) : null;
        $taux_carbone_moy = count($co2Values) > 0 ? array_sum($co2Values) / count($co2Values) : null;

        return [$temp_moy, $hum_moy, $taux_carbone_moy];
    }

    /*
     * Modifie l'etat du experimentation à $etat pour la salle de nom $salle
     */
    public function modifierEtat($etat, $salle) : void
    {
        $idSalle = $this->salleRepository->nomSalleId($salle);
        $Exp = $this->createQueryBuilder('experimentation')
            ->where('experimentation.etat != 3 and experimentation.Salles ='.$idSalle->getId())
            ->getQuery()
            ->getResult();
        $Exp = $Exp[0];
        $Exp->setEtat($etat);
        $entityManager = $this->getEntityManager();
        if($etat == EtatExperimentation::retiree){
            $Exp->getSA()->setDisponible(1);
            $Exp->setSA(null);
        }

        $entityManager->persist($Exp);
        $entityManager->flush();
    }

    public function trouveExperimentationsNonRetirer()
    {
        $queryBuilder = $this->createQueryBuilder('experimentation')
            ->select('sa.nom as nom_sa, salle.nom as nom_salle,
                CASE
                    WHEN experimentation.etat = :etat_demande_installation THEN 0
                    WHEN experimentation.etat = :etat_installee THEN 1
                    WHEN experimentation.etat = :etat_demandeRetrait THEN 2
                    WHEN experimentation.etat = :etat_retiree THEN 4
                    ELSE 4
                END AS etat')
            ->leftJoin('App\Entity\SA', 'sa', 'WITH', 'sa.id = experimentation.SA')
            ->leftJoin('App\Entity\Salle', 'salle', 'WITH', 'salle.id = experimentation.Salles')
            ->where('experimentation.etat != 3');

        $queryBuilder->setParameter('etat_demande_installation', EtatExperimentation::demandeInstallation);
        $queryBuilder->setParameter('etat_installee', EtatExperimentation::installee);
        $queryBuilder->setParameter('etat_demandeRetrait', EtatExperimentation::demandeRetrait);
        $queryBuilder->setParameter('etat_retiree', EtatExperimentation::retiree);


        // Exécutez la requête et retournez les résultats.
        return $queryBuilder->getQuery()->getResult();
    }

    /*
     * Récupère l'état d'une experimentation pour la salle de nom $salle
     */
    public function etatExperimentation($salle) : EtatExperimentation
    {
        $idSalle = $this->salleRepository->nomSalleId($salle);
        $Exp = $this->createQueryBuilder('experimentation')
            ->where('experimentation.etat != 3 and experimentation.Salles ='.$idSalle->getId())
            ->getQuery()
            ->getResult();
        if(count($Exp)==0){
            return EtatExperimentation::retiree;
        }
        $Exp = $Exp[0];
        return $Exp->getEtat();
    }

    public function triexperimentation($exp)
    {
        $len = count($exp);
        if(count($exp)>2){
            $salle = $exp[0]['nom_salle'];
            for($i = 0 ; $i < $len-1 ; $i ++)
            {
                if($salle == $exp[$i+1]['nom_salle'])
                {
                    unset($exp[$i+1]);
                }else{
                    $salle = $exp[$i+1]['nom_salle'];
                }
            }
        }
        return $exp;
    }


    /*
     * Fonctions qui retourne une liste des experimentations qui ont eu lieu dans une salle de nom $nomSalle
     */
    public function trouveExperimentationsParNomSalle($nomsalle)
    {
        $entityManager = $this->getEntityManager();

        // Requête pour trouver les expérimentations liées à une salle avec le nom donné
        $query = $entityManager->createQuery('
        SELECT exp
        FROM App\Entity\Experimentation exp
        JOIN exp.Salles salle
        WHERE salle.nom = :nomsalle
    ');

        $query->setParameter('nomsalle', $nomsalle);

        // Exécutez la requête et retournez la liste des expérimentations
        return $query->getResult();
    }
}
