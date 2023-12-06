<?php

namespace App\DataFixtures;

use App\Config\EtatExperimentation;
use App\Config\EtatSA;
use App\Entity\Batiment;
use App\Entity\Salle;
use App\Entity\SA;
use App\Entity\Experimentation;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class AppFixtures extends Fixture
{

    private Generator $faker;

    // Initialisation du générateur Faker dans le constructeur.
    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }
    // Méthode principale pour charger les données de test dans la base de données.
    public function load(ObjectManager $manager): void
    {
        // Création d'un bâtiment avec des salles pour chaque étage et numéro de salle.
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

        // Création de 15 systèmes d'acquisitions (SA) avec des numéros générés aléatoirement.
        for ($i=0; $i < 15; $i++){
            $sa = new SA();
            $sa->setEtat(EtatSA::eteint);
            $sa->setDisponible(true);
            $number = $this->faker->bothify('####');
            $sa->setNumero($number);
            $sa->setNom('SA-'. $number);
            $manager->persist($sa);
        }
        // Exécution des opérations d'écriture dans la base de données.
        $manager->flush();
        // Affichage du nombre total de salles générées.
        $salles = $manager->getRepository(Salle::class)->findAll();
        echo "Nombre total de salles : " . count($salles) . PHP_EOL;
        // Sélection aléatoire de 10 salles et 10 SA pour créer des expérimentations.
        $dixsalles = array_rand($salles,10);

        $sas = $manager->getRepository(Sa::class)->findAll();
        $dixsas = array_rand($sas,10);

        // Création de 10 expérimentations avec des dates de demande et d'installation aléatoires.
        /*for ($i=0; $i < 10; $i++) {
            $exp = new Experimentation();
            $dateTimeNow = new DateTime($dateTime = 'now');
            $exp->setDatedemande($this->faker->dateTimeBetween('-7 week', '-1 week'));
            $exp->setDateinstallation($this->faker->randomElement([true,false])?null:$dateTimeNow);
            $sas[$dixsas[$i]]->setDisponible(false);//Rend le sa indisponible
            if($exp->getDateinstallation() != null)
            {   
                $exp->setEtat($this->faker->randomElement([EtatExperimentation::installee,EtatExperimentation::demandeRetrait,EtatExperimentation::retiree]));//met l'etat de façon aléatoire sur les 3 autres etats possible
                if($exp->getEtat() == EtatExperimentation::retiree)$sas[$dixsas[$i]]->setDisponible(true);
                else if($exp->getEtat() == EtatExperimentation::installee || $exp->getEtat() == EtatExperimentation::demandeRetrait)$sas[$dixsas[$i]]->setEtat($this->faker->randomElement([true,false])?EtatSA::marche:EtatSA::probleme);
            }
            else
            {
                $exp->setEtat(EtatExperimentation::demandeInstallation);//met l'etat sur demmande
            }
            $exp->setSalle($salles[$dixsalles[$i]]);
            $exp->setSA($sas[$dixsas[$i]]);
            $manager->persist($exp);
        }*/

        // Exécution des opérations d'écriture dans la base de données.
        $manager->flush();
    }
}
//0->eteint;1->marche;2->probleme                                etatSA
//0->demandeInstallation;1->installe;2demandeRetrait;3->retiree  etatExperimentation