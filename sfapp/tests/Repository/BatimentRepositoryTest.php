<?php

namespace App\Tests\Repository;

use App\Entity\Batiment;
use App\Repository\BatimentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BatimentRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?BatimentRepository $batimentRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->batimentRepository = $this->entityManager->getRepository(Batiment::class);
    }

    public function testTableauBatimentsNomID(): void
    {
        $result = $this->batimentRepository->tableauBatimentsNomID();
        foreach ($result as $key => $value) {
            if ($value != null) {
                $result[$key] = !null;
            }
        }
        $expectedResult = [
            'G' => !null,
            'F' => !null,
        ];

        $this->assertSame($expectedResult, $result);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->batimentRepository = null;
    }
}
