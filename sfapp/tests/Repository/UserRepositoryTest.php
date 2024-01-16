<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserRepositoryTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    private $managerRegistry;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        self::bootKernel();

        // Récupérer l'EntityManager
        $this->entityManager = self::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
    }

    public function testUpgradePassword(): void
    {
        // Créer un utilisateur avec un mot de passe déjà hashé
        $user = new User();
        $user->setUsername('testuser');
        $user->setPassword('hashed_password'); // Remplacez 'hashed_password' par le mot de passe déjà hashé

        // Persistez l'utilisateur dans la base de données
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Récupérer le repository
        $userRepository = $this->entityManager->getRepository(User::class);

        // Appeler la méthode à tester pour mettre à jour le mot de passe
        $newHashedPassword = 'new_hashed_password'; // Remplacez 'new_hashed_password' par le nouveau mot de passe hashé
        $userRepository->upgradePassword($user, $newHashedPassword);

        // Récupérer à nouveau l'utilisateur de la base de données
        $updatedUser = $userRepository->find($user->getId());

        // Assurez-vous que le mot de passe a été mis à jour correctement
        // Assurez-vous que le mot de passe a été mis à jour correctement
        $this->assertInstanceOf(PasswordAuthenticatedUserInterface::class, $updatedUser);
        $this->assertTrue(password_verify('new_hashed_password', $updatedUser->getPassword()));


        // Nettoyer après le test en supprimant l'utilisateur de la base de données
        $this->entityManager->remove($updatedUser);
        $this->entityManager->flush();
    }

    /**
     * Teste la méthode de recherche d'utilisateur par nom d'utilisateur.
     */
    public function testFindByUsername(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);

        // Appeler la méthode à tester
        $foundUsers = $userRepository->findByUsername('chargemission');

        // Assurer que des utilisateurs sont trouvés
        $this->assertIsArray($foundUsers);
        $this->assertNotEmpty($foundUsers);

        // Vérifier que le premier élément du tableau est un tableau contenant les infos de l'utilisateur
        $foundUser = $foundUsers[0];

        // Facultatif : Assurez-vous que le nom d'utilisateur correspond à celui que vous avez cherché
        $this->assertEquals('chargemission', $foundUser->getUsername());
    }

    /**
     * Teste la méthode qui récupère les recommandations pour l'été.
     */
    public function testRecommandationsEte(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);

        // Appeler la méthode à tester avec des données spécifiques
        $recommandations = $userRepository->recommandationsEte([
            'co2' => 450,
            'temp' => 25,
            'hum' => 60,
        ]);

        // Assurer que les recommandations sont correctes
        $this->assertIsArray($recommandations);
    }

    public function testRecommandationsPrintemps(): void
    {

        // Récupérer le repository
        $userRepository = $this->entityManager->getRepository(User::class);

        // Appeler la méthode à tester avec des données spécifiques pour le printemps
        $recommandations = $userRepository->recommandationsPrintemps([
            'co2' => 450,
            'temp' => 18,
            'hum' => 70,
        ]);

        // Assurez-vous que les recommandations sont correctes (ajustez cela en fonction de votre implémentation)
        $this->assertIsArray($recommandations);
        $this->assertNotEmpty($recommandations);
    }

    /**
     * Teste la méthode recommandationsAutomne.
     */
    public function testRecommandationsAutomne(): void
    {

        // Récupérer le repository
        $userRepository = $this->entityManager->getRepository(User::class);

        // Appeler la méthode à tester avec des données spécifiques pour l'automne
        $recommandations = $userRepository->recommandationsAutomne([
            'co2' => 450,
            'temp' => 12,
            'hum' => 65,
        ]);

        // Assurez-vous que les recommandations sont correctes (ajustez cela en fonction de votre implémentation)
        $this->assertIsArray($recommandations);
        $this->assertNotEmpty($recommandations);
    }

    /**
     * Teste la méthode recommandationsHiver.
     */
    public function testRecommandationsHiver(): void
    {

        // Récupérer le repository
        $userRepository = $this->entityManager->getRepository(User::class);

        $recommandations = $userRepository->recommandationsHiver([
            'co2' => 450,
            'temp' => 5,
            'hum' => 60,
        ]);

        $this->assertIsArray($recommandations);
        $this->assertNotEmpty($recommandations);
    }

    public function testRecommandationsSalles()
    {
        $votreClasse = new UserRepository($this->managerRegistry);

        // Test pour la saison d'hiver
        $hiverDate = '2024-01-01 12:00:00';
        $hiverRecommandations = $votreClasse->recommandationsSalles([
            'co2' => 450,
            'temp' => 5,
            'hum' => 60,
        ], $hiverDate);
        $this->assertNotEmpty($hiverRecommandations);

        // Test pour la saison de printemps
        $printempsDate = '2024-04-01 12:00:00';
        $printempsRecommandations = $votreClasse->recommandationsSalles([
            'co2' => 450,
            'temp' => 5,
            'hum' => 60,
        ], $printempsDate);
        $this->assertNotEmpty($printempsRecommandations);

        // Test pour la saison d'été
        $eteDate = '2024-07-01 12:00:00';
        $eteRecommandations = $votreClasse->recommandationsSalles([
            'co2' => 450,
            'temp' => 5,
            'hum' => 60,
        ], $eteDate);
        $this->assertNotEmpty($eteRecommandations);

        // Test pour la saison d'automne
        $automneDate = '2024-10-01 12:00:00';
        $automneRecommandations = $votreClasse->recommandationsSalles([
            'co2' => 450,
            'temp' => 5,
            'hum' => 60,
        ], $automneDate);
        $this->assertNotEmpty($automneRecommandations);

    }

    public function testIntervallesTempSaison()
    {
        $user = new UserRepository($this->managerRegistry);

        // Test pour la saison d'hiver
        $hiverDate = '2024-01-01 12:00:00';
        $hiverIntervallesTemp = $user->intervallesTempSaison($hiverDate);
        $this->assertNotEmpty($hiverIntervallesTemp);

        // Test pour la saison de printemps
        $printempsDate = '2024-04-01 12:00:00';
        $printempsIntervallesTemp = $user->intervallesTempSaison($printempsDate);
        $this->assertNotEmpty($printempsIntervallesTemp);

        // Test pour la saison d'été
        $eteDate = '2024-01-01 12:00:00';
        $eteIntervallesTemp = $user->intervallesTempSaison($eteDate);
        $this->assertNotEmpty($eteIntervallesTemp);

        // Test pour la saison d'hiver'
        $automneDate = '2024-04-01 12:00:00';
        $automneIntervallesTemp = $user->intervallesTempSaison($automneDate);
        $this->assertNotEmpty($automneIntervallesTemp);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // Nettoyer après chaque test
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
