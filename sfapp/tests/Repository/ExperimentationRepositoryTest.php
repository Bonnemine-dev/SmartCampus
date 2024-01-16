<?php

namespace App\Tests\Repository;

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
    private $entityManager;

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

        // Clean up the database after each test
        $this->cleanDatabase();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    private function cleanDatabase(): void
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\Experimentation')->execute();
        // Add more delete queries for other entities if needed
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
}
