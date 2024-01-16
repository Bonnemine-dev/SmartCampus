<?php

namespace App\Tests\Repository;

use App\Config\EtatExperimentation;
use App\Config\EtatSA;
use App\Entity\Experimentation;
use App\Entity\SA;
use App\Entity\Salle;
use App\Repository\ExperimentationRepository;
use App\Repository\SalleRepository;
use App\Repository\SARepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ExperimentationRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;

    private ExperimentationRepository $experimentationRepository;

    private SalleRepository $salleRepository;

    private SARepository $saRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->experimentationRepository = $this->entityManager->getRepository(Experimentation::class);
        $this->salleRepository = $this->entityManager->getRepository(Salle::class);
        $this->saRepository = $this->entityManager->getRepository(SA::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testAjouterExperimentation(): void
    {
        $salle = $this->salleRepository->findOneBy(['nom' => 'F001']);
        $this->saRepository->ajoutSA('ESPtest-98765');

        $this->entityManager->persist($salle);
        $this->entityManager->flush();

        $this->experimentationRepository->ajouterExperimentation($salle->getNom());

        $experimentations = $this->experimentationRepository->findOneBy(['Salles' => $salle]);

        $this->assertInstanceOf(Experimentation::class, $experimentations);
        $this->assertEquals($salle, $experimentations->getSalles());
    }

    public function testEstExistante(): void
    {
        $salleName = 'F005';
        $this->assertTrue($this->experimentationRepository->estExistante($salleName));
        $salleName = 'F002';
        $this->assertFalse($this->experimentationRepository->estExistante($salleName));
    }

    public function testTrouveExperimentationDemandeInstallation(): void
    {

        // Call the method being tested
        $result = $this->experimentationRepository->trouveExperimentationDemandeInstallation();

        $this->assertCount(20, $result);
        $this->assertEquals('F001', $result[0]['nom_salle']);
        $this->assertEquals(EtatExperimentation::demandeInstallation->value, $result[0]['etat']);
    }

    public function testSupprimerExperimentation(): void
    {
        $salleName = 'F005';

        // Call the method being tested
        $result = $this->experimentationRepository->supprimerExperimentation($salleName);

        $this->assertTrue($this->experimentationRepository->estExistante($salleName));
        $this->assertCount(2, $result);
        $this->assertEquals(EtatExperimentation::installee, $result[0]);
        $this->assertEquals(EtatExperimentation::demandeRetrait, $result[1]);

        $salleName = 'F001';

        // Call the method being tested
        $result = $this->experimentationRepository->supprimerExperimentation($salleName);

        $this->assertFalse($this->experimentationRepository->estExistante($salleName));
        $this->assertCount(2, $result);
        $this->assertEquals(EtatExperimentation::demandeInstallation, $result[0]);
        $this->assertEquals(EtatExperimentation::demandeInstallation, $result[1]);
    }

    public function testFiltreExperimentationAnalyse(): void
    {

        // Call the method being tested with filters
        $result = $this->experimentationRepository->filtreExperimentationAnalyse([0], ['nord']);

        $this->assertCount(3, $result);
        $this->assertEquals('G001', $result[0]['nom']);
        $this->assertEquals(0, $result[0]['etage']);
        $this->assertEquals('nord', $result[0]['orientation']);
        $this->assertEquals(EtatExperimentation::installee, $result[0]['etat']);
        $this->assertEquals(EtatSA::marche, $result[0]['sa_etat']);
    }

    public function testRechercheExperimentationAnalyse(): void
    {
        // Call the method being tested with search criteria
        $result = $this->experimentationRepository->rechercheExperimentationAnalyse(salle:'F005');

        $this->assertCount(1, $result);
        $this->assertEquals('F005', $result[0]['nom']);
        $this->assertEquals(0, $result[0]['etage']);
        $this->assertEquals('nord', $result[0]['orientation']);
        $this->assertEquals(EtatExperimentation::demandeRetrait, $result[0]['etat']);
        $this->assertEquals(EtatSA::marche, $result[0]['sa_etat']);
    }

    public function testModifierEtat(): void
    {
        $salle = $this->salleRepository->findOneBy(['nom' => 'G206']);
        $experimentation = $this->experimentationRepository->findOneBy(['Salles' => $salle]);
        // Call the method being tested
        $this->experimentationRepository->modifierEtat(EtatExperimentation::demandeRetrait, 'G206');

        $this->assertEquals(EtatExperimentation::demandeRetrait, $experimentation->getEtat());
    }

    public function testTriExperimentation(): void
    {
        $exp = new Experimentation();
        $exp->setEtat(EtatExperimentation::demandeInstallation);
        $exp->setSalles($this->salleRepository->findOneBy(['nom' => 'F004']));
        $exp->setSA($this->saRepository->findOneBy(['nom' => 'ESPtest-98765']));
        $exp->setDatedemande(new \DateTime('2021-05-01'));
        $exp->setDateinstallation(new \DateTime('2021-05-01'));
        $this->entityManager->persist($exp);
        $this->entityManager->flush();
        $result = $this->experimentationRepository->triExperimentation($this->experimentationRepository->trouveExperimentationDemandeInstallation());

        // Assertions for the sorted result
        $this->assertCount(18, $result);
        $this->assertEquals('F004', $result[0]['nom_salle']);
    }

    public function testEtatExperimentation(): void
    {
        $result = $this->experimentationRepository->etatExperimentation('F005');
        // Assertions for the result
        $this->assertEquals($result, $this->experimentationRepository->findOneBy(['Salles' => $this->salleRepository->findOneBy(['nom' => 'F005'])])->getEtat());
    }

    public function testListerLesIntervallesArchives(): void
    {

        // Call the method being tested
        $result = $this->experimentationRepository->listerLesIntervallesArchives('F005');

        // Assertions for the result
        $this->assertCount(0, $result);
    }

    public function testExtraireDateInstallExpActuelle(): void
    {
        // Call the method being tested
        $result = $this->experimentationRepository->extraireDateInstallExpActuelle('F005');

        // Assertions for the result
        $this->assertInstanceOf(\DateTime::class, $result['date_install']);
    }

    public function testEtatExp(): void
    {

        // Call the method being tested
        $result = $this->experimentationRepository->etatExp('F005');

        // Assertions for the result
        $this->assertCount(1, $result);
        $this->assertEquals(EtatExperimentation::demandeRetrait, $result[0]['etat_exp']);
    }

    public function testTrouveExperimentationsParNomSalle(): void
    {

        // Call the method being tested
        $result = $this->experimentationRepository->trouveExperimentationsParNomSalle('F005');

        // Assertions for the result
        $this->assertCount(1, $result);
        $this->assertInstanceOf(Experimentation::class, $result[0]);
        $this->assertEquals('F005', $result[0]->getSalles()->getNom());
    }
}
