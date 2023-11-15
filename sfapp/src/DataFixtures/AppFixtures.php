<?php

namespace App\DataFixtures;

use App\Entity\Batiment;
use App\Entity\Salle;
use App\Entity\Experimentation;
use App\Entity\SA;
use DateTime;
use DateTimeInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class AppFixtures extends Fixture
{

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }
    public function load(ObjectManager $manager): void
    {
        $batiment = new Batiment();
        $batiment->setNom('D');
        $batiment->setDescription('Bâtiment D - Département informatique');
        $manager->persist($batiment);

        for ($etage=0; $etage < 4; $etage++) {
            for ($numero=1; $numero < 8; $numero++) {
                $salle = new Salle();
                $salle->setEtage($etage);
                $salle->setNumero($numero);
                $salle->setOrientation($numero%2==0?'sud':'nord');
                $salle->setNbFenetres($this->faker->numberBetween(2,6));
                $salle->setNbOrdis($this->faker->randomElement([true,false])?0:$this->faker->numberBetween(10,20));
                $salle->setBatiment($batiment);
                $salle->setNom($batiment->getNom().$salle->getEtage().'0'.$salle->getNumero());
                $manager->persist($salle);
            }
        }
            for ($i=0; $i < 15; $i++){
                $sa = new SA();
                $sa->setEtat($this->faker->randomElement(['Actif','Eteint','En_panne','En_reparation']));
                $number = $this->faker->bothify('####');
                $sa->setNumero($number);
                $sa->setNom('SA-'. $number);
                $manager->persist($sa);
            }
                $manager->flush();
                $salles = $manager->getRepository(Salle::class)->findAll();
                echo "Nombre total de salles : " . count($salles) . PHP_EOL;
                $dixsalles = array_rand($salles,10);

                $sas = $manager->getRepository(Sa::class)->findAll();
                $dixsas = array_rand($sas,10);

                for ($i=0; $i < 10; $i++) { 
                $exp = new Experimentation();
                $dateTimeNow = new DateTime($dateTime = 'now');
                $exp->setDatedemande($this->faker->dateTimeBetween('-7 week', '-1 week'));
                $exp->setDateinstallation($this->faker->randomElement([true,false])?null:$dateTimeNow);
                $exp->setSalle($salles[$dixsalles[$i]]);
                $exp->setSA($sas[$dixsas[$i]]);
                $manager->persist($exp);
                }
        $manager->flush();
    }
}