<?php

namespace App\Tests\Repository;

use App\Entity\Salle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SalleRepositoryTest extends KernelTestCase
{
    private  $entityManager;
    private  $SalleRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->SalleRepository = $this->entityManager->getRepository(Salle::class);
    }

    public function testnomSalleId(): void
    {
        $resultat = $this->SalleRepository->nomSalleId('francis');

        $this->assertSame($resultat, null);
    }

    public function testrechercheSallePlanExpSaansArgument(): void
    {
        $resultat = $this->SalleRepository->rechercheSallePlanExp();

        $this->assertSame($resultat[0]['nom_salle'], 'F001');
    }

    public function testrechercheSallePlanExpAvecArgument(): void
    {
        $resultat = $this->SalleRepository->rechercheSallePlanExp(null,"F001");

        $this->assertSame($resultat[0]['nom_salle'], 'F001');
    }

    public function testfiltrerSallePlanExpSaansArgument(): void
    {
        $resultat = $this->SalleRepository->filtrerSallePlanExp();

        $this->assertSame($resultat[0]['nom_salle'], 'F001');
    }

    public function testfiltrerSallePlanExpAvecArgument(): void
    {
        $resultat = $this->SalleRepository->filtrerSallePlanExp([],[],1,null);

        $this->assertSame($resultat[0]['nom_salle'], 'F001');
    }

    public function testtriListeSalle():void
    {
        $salle = $this->SalleRepository->rechercheSallePlanExp();
        $resultat = $this->SalleRepository->triListeSalle($salle);


        $this->assertSame($resultat[0]['nom_salle'], 'F001');
    }

    public function testSAAssocie():void
    {
        $resultat = $this->SalleRepository->SAAssocie('F007');


        $this->assertSame($resultat['nom_sa'], 'ESPtest-015');
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->SalleRepository = null;
    }
}
