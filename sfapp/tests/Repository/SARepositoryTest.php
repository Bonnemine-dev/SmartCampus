<?php

namespace App\Tests\Repository;

use App\Config\EtatExperimentation;
use App\Config\EtatSA;
use App\Entity\Experimentation;
use App\Entity\SA;
use App\Repository\SARepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SARepositoryTest extends KernelTestCase
{
    private  $entityManager;
    private  $SARepository;
    private  $ExperimentationRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->SARepository = $this->entityManager->getRepository(SA::class);
        $this->ExperimentationRepository = $this->entityManager->getRepository(Experimentation::class);
    }

    public function testcompteSASansExperimentation(): void
    {
        $result = $this->SARepository->compteSASansExperimentation();

        $this->assertSame($result, 2);
    }

    public function testsaNonUtiliser(): void
    {
        $result = $this->SARepository->saNonUtiliser();

        $this->assertSame($result->getNom(), 'ESPtest-9999');
    }

    public function testsuppressionExp(): void
    {
        $sa = $this->SARepository->findoneby(['disponible' => 0]);
        $this->SARepository->suppressionExp($sa);

        $this->assertSame($sa->isDisponible(), true);
    }

    public function testtrierSA(): void
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

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
        $resultat = $this->SARepository->trierSA($resultat);


        $attendu = [["sa_nom" => "ESPtest-001",
                "salle_nom" => 'G205',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-002",
                "salle_nom" => 'G206',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::demandeRetrait],
            ["sa_nom" => "ESPtest-003",
                "salle_nom" => 'G207',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-004",
                "salle_nom" => 'G204',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-005",
                "salle_nom" => 'G203',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-006",
                "salle_nom" => 'G303',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-007",
                "salle_nom" => 'G304',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-008",
                "salle_nom" => 'F101',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-009",
                "salle_nom" => 'G109',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-010", 
                "salle_nom" => 'G106',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-011",
                "salle_nom" => 'G001',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-012",
                "salle_nom" => 'G002',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-013",
                "salle_nom" => 'G004',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            [],
            ["sa_nom" => "ESPtest-014",
                "salle_nom" => 'F004',
                "sa_etat" => EtatSA::eteint,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-015",
                "salle_nom" => 'F007',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-016",
                "salle_nom" => 'F201',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-017",
                "salle_nom" => 'F307',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-018",
                "salle_nom" => 'F005',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::demandeRetrait],
            ["sa_nom" => "ESPtest-98765",
                "salle_nom" => 'F004',
                "sa_etat" => EtatSA::eteint,
                "exp_etat" => EtatExperimentation::demandeInstallation],
            ["sa_nom" => "ESPtest-9999",
                "salle_nom" => null,
                "sa_etat" => EtatSA::eteint,
                "exp_etat" => null,]];

        unset($attendu[13]);

        $this->assertSame($resultat, $attendu);
    }

    public function testtoutLesSA(): void
    {
        $resultat = $this->SARepository->toutLesSA();

        $attendu = [["sa_nom" => "ESPtest-001",
            "salle_nom" => 'G205',
            "sa_etat" => EtatSA::marche,
            "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-002",
                "salle_nom" => 'G206',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::demandeRetrait],
            ["sa_nom" => "ESPtest-003",
                "salle_nom" => 'G207',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-004",
                "salle_nom" => 'G204',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-005",
                "salle_nom" => 'G203',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-006",
                "salle_nom" => 'G303',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-007",
                "salle_nom" => 'G304',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-008",
                "salle_nom" => 'F101',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-009",
                "salle_nom" => 'G109',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-010",
                "salle_nom" => 'G106',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-011",
                "salle_nom" => 'G001',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-012",
                "salle_nom" => 'G002',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-013",
                "salle_nom" => 'G004',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            [],
            ["sa_nom" => "ESPtest-014",
                "salle_nom" => 'F004',
                "sa_etat" => EtatSA::eteint,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-015",
                "salle_nom" => 'F007',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-016",
                "salle_nom" => 'F201',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-017",
                "salle_nom" => 'F307',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::installee],
            ["sa_nom" => "ESPtest-018",
                "salle_nom" => 'F005',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::demandeRetrait],
            ["sa_nom" => "ESPtest-98765",
                "salle_nom" => 'F004',
                "sa_etat" => EtatSA::eteint,
                "exp_etat" => EtatExperimentation::demandeInstallation],
            ["sa_nom" => "ESPtest-9999",
                "salle_nom" => null,
                "sa_etat" => EtatSA::eteint,
                "exp_etat" => null,]];

        unset($attendu[13]);



        $this->assertSame($resultat, $attendu);
    }

    public function testfiltrerSAGestionSASansArgument() : void
    {
        $resultat = $this->SARepository->filtrerSAGestionSA([],[]);

        $attendu = ["sa_nom" => "ESPtest-001",
            "salle_nom" => 'G205',
            "sa_etat" => EtatSA::marche,
            "exp_etat" => EtatExperimentation::installee];


        $this->assertSame($resultat[0], $attendu);
    }

    public function testfiltrerSAGestionSAavecArgumentEtat() : void
    {
        $resultat = $this->SARepository->filtrerSAGestionSA([1],[]);

        $attendu = ["sa_nom" => "ESPtest-001",
            "salle_nom" => 'G205',
            "sa_etat" => EtatSA::marche,
            "exp_etat" => EtatExperimentation::installee];


        $this->assertSame($resultat[0], $attendu);
    }

    public function testrechercheSASansArgument() : void
    {
        $resultat = $this->SARepository->rechercheSA('');

        $attendu = ["sa_nom" => "ESPtest-002",
                "salle_nom" => 'G206',
                "sa_etat" => EtatSA::marche,
                "exp_etat" => EtatExperimentation::demandeRetrait];


        $this->assertSame($resultat[1], $attendu);
    }

    public function testrechercheSAAvecArgument() : void
    {
        $resultat = $this->SARepository->rechercheSA('ESPtest-001');

        $attendu = [["sa_nom" => "ESPtest-001",
            "salle_nom" => 'G205',
            "sa_etat" => EtatSA::marche,
            "exp_etat" => EtatExperimentation::installee]];

        $this->assertSame($resultat, $attendu);
    }

    public function testajoutSA() : void
    {
        $avant = $this->SARepository->compteSASansExperimentation();
        $this->SARepository->ajoutSA('ESPtest-150');
        $apres = $this->SARepository->compteSASansExperimentation();



        $this->assertSame($avant, $apres-1);
    }

    public function testexisteDeja() : void
    {
        $resultat = $this->SARepository->existeDeja('ESPtest-151');



        $this->assertSame($resultat, null);
    }

    public function testsupprimerSA() : void
    {
        $avant = $this->SARepository->compteSASansExperimentation();
        $this->SARepository->supprimerSA('ESPtest-150');
        $apres = $this->SARepository->compteSASansExperimentation();



        $this->assertSame($avant, $apres+1);
    }

    public function testsalle_associe_sa() : void
    {
        $salle = $this->SARepository->salle_associe_sa('ESPtest-001');

        $this->assertSame($salle[0]['nom'] ,'G205');
    }

    public function testchangerEtatSA() : void
    {
        $this->SARepository->changerEtatSA('G205',EtatSA::probleme);
        $SA = $this->SARepository->existeDeja('ESPtest-001');


        $this->assertSame($SA->getEtat(), EtatSA::probleme);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->SARepository = null;
    }
}
