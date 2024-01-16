<?php

namespace App\DataFixtures;

use App\Config\EtatExperimentation;
use App\Config\EtatSA;
use App\Entity\Batiment;
use App\Entity\User;
use App\Entity\Salle;
use App\Entity\SA;
use App\Entity\Experimentation;
use App\Repository\SalleRepository;
use App\Repository\SARepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class AppFixturesTest extends Fixture 
{
    private Generator $faker;
    private SalleRepository $salleRepository;
    private SARepository $saRepository;

    // Initialisation du générateur Faker dans le constructeur.
    public function __construct(SalleRepository $salleRepository, SARepository $saRepository)
    {
        $this->faker = Factory::create('fr_FR');
        $this->salleRepository = $salleRepository;
        $this->saRepository = $saRepository;
    }
    // Méthode principale pour charger les données de test dans la base de données.
    public function load(ObjectManager $manager): void
    {
        // Création d'un bâtiment avec des salles pour chaque étage et numéro de salle.
        $batiment = new Batiment();
        $batiment->setNom('G');
        $batiment->setDescription('Bâtiment G - Département informatique');
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

        // Ajout de la salle D109
        $salle = new Salle();
        $salle->setEtage(1);
        $salle->setNumero(9);
        $salle->setOrientation('sud');
        $salle->setNbFenetres(2);
        $salle->setNbOrdis(0);
        $salle->setBatiment($batiment);
        $salle->setNom($batiment->getNom().$salle->getEtage().'0'.$salle->getNumero());
        $manager->persist($salle);

        // Création du batiment C et de ses salles associées
        $batiment = new Batiment();
        $batiment->setNom('F');
        $batiment->setDescription('Bâtiment F - Département réseaux télécoms');
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

        // Création de 18 systèmes d'acquisitions (SA) avec des numéros générés aléatoirement.
        for ($i=1; $i <= 18; $i++){
            $sa = new SA();
            $sa->setEtat(EtatSA::eteint);
            $sa->setDisponible(true);
            $number = sprintf('%03d', $i);
            $sa->setNumero($i);
            $sa->setNom('ESPtest-'. $number);
            $manager->persist($sa);
        }
        // Exécution des opérations d'écriture dans la base de données.
        $manager->flush();

        // Affichage du nombre total de salles générées.
        $salles = $manager->getRepository(Salle::class)->findAll();
        echo "Nombre total de salles : " . count($salles) . PHP_EOL;

        foreach ($salles as $salle) 
        {
            echo $salle->getNom() . PHP_EOL;
        }

        // Affichage du nombre total de SA générées.
        $sas = $manager->getRepository(SA::class)->findAll();
        echo "Nombre total de SA : " . count($sas) . PHP_EOL;

        foreach ($sas as $sa) 
        {
            echo $sa->getNom() . PHP_EOL;
        }

        // Création des 17 expérimentations avec des dates de demande et d'installation aléatoires.
        $expArray = [ ["G205", "ESPtest-001"], ["G206", "ESPtest-002"], ["G207", "ESPtest-003"], ["G204", "ESPtest-004"], ["G203", "ESPtest-005"], ["G303", "ESPtest-006"], ["G304", "ESPtest-007"], ["F101", "ESPtest-008"], ["G109", "ESPtest-009"], ["G106", "ESPtest-010"], ["G001", "ESPtest-011"], ["G002", "ESPtest-012"], ["G004", "ESPtest-013"], ["F004", "ESPtest-014"], ["F007", "ESPtest-015"], ["F201", "ESPtest-016"], ["F307", "ESPtest-017"], ["F005", "ESPtest-018"] ];

        foreach ($expArray as $experimentation) {
            $salle = $this->salleRepository->findOneBy(['nom' => $experimentation[0]]);
            $sa = $this->saRepository->findOneBy(['nom' => $experimentation[1]]);
            $sa->setEtat(EtatSA::marche);
            $sa->setDisponible(false);

            echo "Salle : " . $salle->getNom() . " - SA : " . $sa->getNom() . PHP_EOL;

            $exp = new Experimentation();
            $exp->setDatedemande($this->faker->dateTimeBetween('-7 week', '-1 week'));
            $exp->setDateinstallation($this->faker->dateTimeBetween('-7 week', '-1 week'));
            $exp->setEtat(EtatExperimentation::installee);
            $exp->setSalles($salle);
            $exp->setSA($sa);
            $manager->persist($exp);
        }

        // Exécution des opérations d'écriture dans la base de données.
        $manager->flush();

        $user = new User();
        $user->setUsername('technicien');
        $user->setRoles(['ROLE_TECHNICIEN']);
        $user->setPlainPassword('technicien');

        $manager->persist($user);
        $manager->flush();

        $user = new User();
        $user->setUsername('chargemission');
        $user->setRoles(['ROLE_CHARGEMISSION']);
        $user->setPlainPassword('chargemission');

        $manager->persist($user);
        $manager->flush();
    }
}

//0->eteint;1->marche;2->probleme                                etatSA
//0->demandeInstallation;1->installe;2demandeRetrait;3->retiree  etatExperimentation