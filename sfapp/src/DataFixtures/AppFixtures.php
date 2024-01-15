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

/**
 * Classe AppFixtures pour charger les données initiales dans la base de données.
 * Cette classe utilise le bundle DoctrineFixtures pour créer des données de test,
 * notamment des bâtiments, des salles, des systèmes d'acquisitions, etc.
 */
class AppFixtures extends Fixture
{
    /**
     * @var Generator
     * Générateur Faker pour créer des données aléatoires.
     */
    private Generator $faker;

    /**
     * @var SalleRepository
     * Référentiel pour accéder aux données des salles.
     */
    private SalleRepository $salleRepository;

    /**
     * @var SARepository
     * Référentiel pour accéder aux données des systèmes d'acquisitions (SA).
     */
    private SARepository $saRepository;


    /**
     * Constructeur de la classe AppFixtures.
     * Initialise le générateur Faker et les référentiels nécessaires.
     *
     * @param SalleRepository $salleRepository Référentiel pour les salles.
     * @param SARepository $saRepository Référentiel pour les SA.
     */
    public function __construct(SalleRepository $salleRepository, SARepository $saRepository)
    {
        $this->faker = Factory::create('fr_FR');
        $this->salleRepository = $salleRepository;
        $this->saRepository = $saRepository;
    }

    /**
     * Méthode principale pour charger les données de test dans la base de données.
     * Cette méthode crée des bâtiments, des salles, des SA, des utilisateurs, etc.
     *
     * @param ObjectManager $manager Le gestionnaire d'entité Doctrine.
     */
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
        $batiment->setNom('C');
        $batiment->setDescription('Bâtiment C - Département réseaux télécoms');
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
            $sa->setNom('ESP-'. $number);
            $manager->persist($sa);
        }
        // Exécution des opérations d'écriture dans la base de données.
        $manager->flush();

        // Affichage du nombre total de salles générées.
        $salles = $manager->getRepository(Salle::class)->findAll();
        echo "Nombre total de salles : " . count($salles) . PHP_EOL;

        // Affichage du nombre total de SA générées.
        $sas = $manager->getRepository(SA::class)->findAll();
        echo "Nombre total de SA : " . count($sas) . PHP_EOL;

        // Création des 17 expérimentations avec des dates de demande et d'installation aléatoires.
        $expArray = [ ["D205", "ESP-001"], ["D206", "ESP-002"], ["D207", "ESP-003"], ["D204", "ESP-004"], ["D203", "ESP-005"], ["D303", "ESP-006"], ["D304", "ESP-007"], ["C101", "ESP-008"], ["D109", "ESP-009"], ["D106", "ESP-010"], ["D001", "ESP-011"], ["D002", "ESP-012"], ["D004", "ESP-013"], ["C004", "ESP-014"], ["C007", "ESP-015"], ["D201", "ESP-016"], ["D307", "ESP-017"], ["C005", "ESP-018"] ];

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