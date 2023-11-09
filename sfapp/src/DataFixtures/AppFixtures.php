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
        for ($i=0; $i < 1; $i++) { 
            $batiment = new Batiment();
            $batiment->setNom('D');
            $batiment->setDescription($this->faker->text(300));
            $manager->persist($batiment);
            for ($i=0; $i < 20; $i++) { 
                $sa = new SA();
                $sa->setEtat($this->faker->randomElement(['Actif','Eteint','En_panne','En_reparation']));
                $number = $this->faker->bothify('####');
                $sa->setNumero($number);
                $sa->setNom('SA-'. $number);
                $manager->persist($sa);

                $salle = new Salle();
                $salle->setEtage($this->faker->numberBetween(0,3));
                $salle->setNumero($this->faker->numberBetween(1,7));
                $salle->setOrientation($this->faker->randomElement([true,true,true,false])?null:$this->faker->randomElement(['Nord','Est','Ouest','Sud']));
                $salle->setNbFenetres($this->faker->randomElement([true,true,true,false])?null:$this->faker->numberBetween(1,6));
                $salle->setNbOrdis($this->faker->randomElement([true,true,true,false])?null:$this->faker->numberBetween(0,20));
                $salle->setBatiment($batiment);
                $salle->setNom($batiment->getNom().$salle->getEtage().'0'.$salle->getNumero());
                $manager->persist($salle);
                if($this->faker->numberBetween(0,100) <= 25){
                $exp = new Experimentation();
                $dateTimeNow = new DateTime($dateTime = 'now');
                $exp->setDatedemande($this->faker->dateTimeBetween('-7 week', '-1 week'));
                $exp->setDateinstallation($this->faker->randomElement([true,false])?null:$dateTimeNow);
                $exp->setSalle($salle);
                $exp->setSA($sa);
                $manager->persist($exp);
                }
            }
        }
        $manager->flush();
    }
}
