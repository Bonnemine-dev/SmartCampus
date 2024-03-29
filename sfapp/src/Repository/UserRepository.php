<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @class UserRepository
 * Repository pour gérer les opérations de base de données liées aux entités User.
 * @extends ServiceEntityRepository<User>
 *
 * @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    private array $OUVRIR_PORTES_FENETRES = ['Ouvrir les portes et les fenêtres', 1];
    private array $ALLUMER_VENTILATEURS = ['Allumer les ventilateurs', 2];
    private array $FERMER_RIDEAUX_VOLETS = ['Fermer les rideaux et les volets', 3];
    private array $ETEINDRE_CHAUFFAGE = ['Éteindre le chauffage', 4];
    private array $ETEINDRE_VENTILATEURS = ['Éteindre les ventilateurs', 2];
    private array $FERMER_PORTES_FENETRES = ['Fermer les portes et les fenêtres', 1];
    private array $ALLUMER_CHAUFFAGE = ['Allumer le chauffage', 4];
    private array $OUVRIR_PORTES = ['Ouvrir les portes', 1];
    private array $FERMER_FENETRES = ['Fermer les fenêtres', 3];
    private array $FERMER_RIDEAUX = ['Fermer les rideaux', 3];

    /**
     * Constructeur de UserRepository.
     * Initialise le repository avec le manager d'entités Doctrine.
     * @param ManagerRegistry $registry Le gestionnaire de l'entité Doctrine.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Utilisé pour mettre à jour (rehash) le mot de passe de l'utilisateur automatiquement avec le temps.
     * @param PasswordAuthenticatedUserInterface $user L'utilisateur dont le mot de passe doit être mis à jour.
     * @param string $newHashedPassword Le nouveau mot de passe hashé.
     * @throws UnsupportedUserException Si l'instance de l'utilisateur n'est pas supportée.
     * @return void
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPlainPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Génère des recommandations d'action en fonction des conditions atmosphériques pour l'été.
     * @param array<string, string> $donnees Les données atmosphériques comme le CO2, la température et l'humidité.
     * @return array<int, string|array<int, string>> Les recommandations d'action basées sur les données fournies.
     */
    public function recommandationsEte(array $donnees): array
    {
        $co2_inf = $donnees['co2'] < 400;
        $co2_sup = $donnees['co2'] > 1000;
        $temp_inf = $donnees['temp'] < 24;
        $temp_sup = $donnees['temp'] > 28;
        $hum_inf = $donnees['hum'] < 40;
        $hum_sup = $donnees['hum'] > 70;

        // Définition des ensembles d'actions numérotées
        $actions1 = [$this->OUVRIR_PORTES_FENETRES, $this->ALLUMER_VENTILATEURS, $this->FERMER_RIDEAUX_VOLETS, $this->ETEINDRE_CHAUFFAGE];
        $actions2 = [$this->OUVRIR_PORTES_FENETRES, $this->ALLUMER_VENTILATEURS];
        $actions3 = [$this->OUVRIR_PORTES_FENETRES, $this->ETEINDRE_CHAUFFAGE, $this->ETEINDRE_VENTILATEURS];
        $actions4 = [$this->FERMER_PORTES_FENETRES, $this->ALLUMER_VENTILATEURS, $this->FERMER_RIDEAUX_VOLETS, $this->ETEINDRE_CHAUFFAGE];
        $actions5 = [$this->FERMER_PORTES_FENETRES, $this->ALLUMER_CHAUFFAGE, $this->ALLUMER_VENTILATEURS];
        $actions6 = [$this->FERMER_PORTES_FENETRES, $this->ALLUMER_CHAUFFAGE, $this->ETEINDRE_VENTILATEURS];
        $actions7 = [$this->OUVRIR_PORTES, $this->FERMER_FENETRES, $this->ALLUMER_VENTILATEURS, $this->FERMER_RIDEAUX_VOLETS, $this->ETEINDRE_CHAUFFAGE];
        $actions8 = [$this->OUVRIR_PORTES_FENETRES];
        $actions9 = [$this->FERMER_PORTES_FENETRES, $this->ALLUMER_VENTILATEURS];
        $actions10 = [$this->FERMER_PORTES_FENETRES, $this->ETEINDRE_CHAUFFAGE, $this->ETEINDRE_VENTILATEURS];
        $actions11 = [$this->FERMER_PORTES_FENETRES, $this->ALLUMER_CHAUFFAGE];
        $actions12 = [$this->FERMER_PORTES_FENETRES];

        // Application des conditions
        if ($co2_sup and $temp_sup and $hum_sup) {
            return ["Le taux de CO2 est trop élevé, la température est trop élevée et le taux d'humidité est trop élevé.", $actions1];
        }
        if ($co2_sup and $temp_sup and $hum_inf) {
            return ["Le taux de CO2 est trop élevé, la température est trop élevée et le taux d'humidité est trop faible.", $actions1];
        }
        if ($co2_sup and $temp_inf and $hum_sup) {
            return ["Le taux de CO2 est trop élevé, la température est trop faible et le taux d'humidité est trop élevé.", $actions2];
        }
        if ($co2_sup and $temp_inf and $hum_inf) {
            return ["Le taux de CO2 est trop élevé, la température et le taux d'humidité sont trop faibles.", $actions3];
        }
        if ($co2_inf and $temp_sup and $hum_sup) {
            return ["Le taux de CO2 est trop faible, la température est trop élevée et le taux d'humidité est trop élevé.", $actions4];
        }
        if ($co2_inf and $temp_sup and $hum_inf) {
            return ["Le taux de CO2 est trop faible, la température est trop élevée et le taux d'humidité est trop faible.", $actions4];
        }
        if ($co2_inf and $temp_inf and $hum_sup) {
            return ["Le taux de CO2 est trop faible, la température est trop faible et le taux d'humidité est trop élevé.", $actions5];
        }
        if ($co2_inf and $temp_inf and $hum_inf) {
            return ["Le taux de CO2, la température et le taux d'humidité sont tous trop faibles.", $actions6];
        }
        if ($temp_sup and $hum_sup) {
            return ["La température et le taux d'humidité sont trop élevés.", $actions1];
        }
        if ($co2_sup and $temp_sup) {
            return ["Le taux de CO2 est trop élevé et la température est trop élevée.", $actions1];
        }
        if ($temp_inf and $hum_sup) {
            return ["La température est trop faible et le taux d'humidité est trop élevé.", $actions2];
        }
        if ($co2_sup and $hum_sup) {
            return ["Le taux de CO2 et le taux d'humidité sont trop élevés.", $actions2];
        }
        if ($co2_sup and $hum_inf) {
            return ["Le taux de CO2 est trop élevé et le taux d'humidité est trop faible.", $actions3];
        }
        if ($co2_inf and $temp_sup) {
            return ["Le taux de CO2 est trop faible et la température est trop élevée.", $actions4];
        }
        if ($temp_inf and $hum_inf) {
            return ["La température et le taux d'humidité sont trop faibles.", $actions6];
        }
        if ($temp_sup and $hum_inf) {
            return ["La température est trop élevée et le taux d'humidité est trop faible.", $actions7];
        }
        if ($co2_sup and $temp_inf) {
            return ["Le taux de CO2 est trop élevé et la température est trop faible.", $actions8];
        }
        if ($co2_inf and $hum_sup) {
            return ["Le taux de CO2 est trop faible et le taux d'humidité est trop élevé.", $actions9];
        }
        if ($co2_inf and $hum_inf) {
            return ["Le taux de CO2 est trop faible et le taux d'humidité est trop faible.", $actions10];
        }
        if ($co2_inf and $temp_inf) {
            return ["Le taux de CO2 et la température sont trop faibles.", $actions11];
        }
        if ($temp_sup) {
            return ["La température est trop élevée.", $actions1];
        }
        if ($hum_sup) {
            return ["Le taux d'humidité est trop élevé.", $actions2];
        }
        if ($temp_inf) {
            return ["La température est trop faible.", $actions8];
        }
        if ($co2_sup) {
            return ["Le taux de CO2 est trop élevé.", $actions8];
        }
        if ($hum_inf) {
            return ["Le taux d'humidité est trop faible.", $actions10];
        }
        if ($co2_inf) {
            return ["Le taux de CO2 est trop faible.", $actions12];
        }
        return ['Bonne ambiance', 'Pas de recommandations pour la salle'];
    }

    /**
     * Génère des recommandations d'action en fonction des conditions atmosphériques pour l'automne.
     * @param array<string, string> $donnees Les données atmosphériques comme le CO2, la température et l'humidité.
     * @return array<int, string|array<int, string>> Les recommandations d'action basées sur les données fournies.
     */
    public function recommandationsAutomne(array $donnees): array
    {
        $co2_inf = $donnees['co2'] < 400;
        $co2_sup = $donnees['co2'] > 1000;
        $temp_inf = $donnees['temp'] < 19;
        $temp_sup = $donnees['temp'] > 23;
        $hum_inf = $donnees['hum'] < 40;
        $hum_sup = $donnees['hum'] > 70;

        // Définition des ensembles d'actions numérotées
        $actions1 = [$this->OUVRIR_PORTES_FENETRES, $this->ALLUMER_VENTILATEURS, $this->FERMER_RIDEAUX];
        $actions2 = [$this->OUVRIR_PORTES_FENETRES, $this->ALLUMER_VENTILATEURS];
        $actions3 = [$this->FERMER_PORTES_FENETRES, $this->ALLUMER_VENTILATEURS, $this->FERMER_RIDEAUX];
        $actions4 = [$this->FERMER_PORTES_FENETRES, $this->ALLUMER_CHAUFFAGE, $this->ALLUMER_VENTILATEURS];
        $actions5 = [$this->ALLUMER_CHAUFFAGE, $this->ALLUMER_VENTILATEURS];
        $actions6 = [$this->OUVRIR_PORTES_FENETRES];
        $actions7 = [$this->FERMER_PORTES_FENETRES, $this->ALLUMER_VENTILATEURS];
        $actions8 = [$this->FERMER_PORTES_FENETRES, $this->ALLUMER_CHAUFFAGE];
        $actions9 = [$this->FERMER_PORTES_FENETRES];
        $actions10 = [$this->ALLUMER_CHAUFFAGE];


        if ($co2_sup and $temp_sup and $hum_sup) {
            return ["Le taux de CO2, la température et le taux d'humidité sont tous trop élevés.", $actions1];
        }
        if ($co2_sup and $temp_sup and $hum_inf) {
            return ["Le taux de CO2 et la température sont trop élevés, mais le taux d'humidité est trop faible.", $actions1];
        }
        if ($co2_sup and $temp_inf and $hum_sup) {
            return ["Le taux de CO2 est trop élevé, la température est trop faible et le taux d'humidité est trop élevé.", $actions2];
        }
        if ($co2_sup and $temp_inf and $hum_inf) {
            return ["Le taux de CO2 est trop élevé, la température et le taux d'humidité sont trop faibles.", $actions2];
        }
        if ($co2_inf and $temp_sup and $hum_sup) {
            return ["Le taux de CO2 est trop faible, la température et le taux d'humidité sont trop élevés.", $actions3];
        }
        if ($co2_inf and $temp_sup and $hum_inf) {
            return ["Le taux de CO2 est trop faible, la température est trop élevée et le taux d'humidité est trop faible.", $actions3];
        }
        if ($co2_inf and $temp_inf and $hum_sup) {
            return ["Le taux de CO2 est trop faible, la température est trop faible et le taux d'humidité est trop élevé.", $actions4];
        }
        if ($co2_inf and $temp_inf and $hum_inf) {
            return ["Le taux de CO2, la température et le taux d'humidité sont tous trop faibles.", $actions4];
        }
        if ($temp_sup and $hum_sup) {
            return ["La température et le taux d'humidité sont trop élevés.", $actions1];
        }
        if ($temp_sup and $hum_inf) {
            return ["La température est trop élevée et le taux d'humidité est trop faible.", $actions1];
        }
        if ($co2_sup and $temp_sup) {
            return ["Le taux de CO2 et la température sont trop élevés.", $actions1];
        }
        if ($co2_sup and $hum_sup) {
            return ["Le taux de CO2 et le taux d'humidité sont trop élevés.", $actions2];
        }
        if ($co2_sup and $hum_inf) {
            return ["Le taux de CO2 est trop élevé et le taux d'humidité est trop faible.", $actions2];
        }
        if ($co2_inf and $temp_sup) {
            return ["Le taux de CO2 est trop faible et la température est trop élevée.", $actions3];
        }
        if ($temp_inf and $hum_sup) {
            return ["La température est trop faible et le taux d'humidité est trop élevé.", $actions5];
        }
        if ($temp_inf and $hum_inf) {
            return ["La température et le taux d'humidité sont trop faibles.", $actions5];
        }
        if ($co2_sup and $temp_inf) {
            return ["Le taux de CO2 est trop élevé et la température est trop faible.", $actions6];
        }
        if ($co2_inf and $hum_sup) {
            return ["Le taux de CO2 est trop faible et le taux d'humidité est trop élevé.", $actions7];
        }
        if ($co2_inf and $hum_inf) {
            return ["Le taux de CO2 est trop faible et le taux d'humidité est trop faible.", $actions7];
        }
        if ($co2_inf and $temp_inf) {
            return ["Le taux de CO2 et la température sont trop faibles.", $actions8];
        }
        if ($temp_sup) {
            return ["La température est trop élevée.", $actions1];
        }
        if ($hum_sup) {
            return ["Le taux d'humidité est trop élevé.", $actions2];
        }
        if ($hum_inf) {
            return ["Le taux d'humidité est trop faible.", $actions2];
        }
        if ($co2_sup) {
            return ["Le taux de CO2 est trop élevé.", $actions6];
        }
        if ($temp_inf) {
            return ["La température est trop faible.", $actions9];
        }
        if ($co2_inf) {
            return ["Le taux de CO2 est trop faible.", $actions10];
        }
        return ['Bonne ambiance', 'Pas de recommandations pour la salle'];
    }

    /**
     * Génère des recommandations d'action en fonction des conditions atmosphériques pour l'hiver.
     * @param array<string, string> $donnees Les données atmosphériques comme le CO2, la température et l'humidité.
     * @return array<int, string|array<int, string>> Les recommandations d'action basées sur les données fournies.
     */
    public function recommandationsHiver(array $donnees): array
    {
        $co2_inf = $donnees['co2'] < 400;
        $co2_sup = $donnees['co2'] > 1000;
        $temp_inf = $donnees['temp'] < 18;
        $temp_sup = $donnees['temp'] > 22;
        $hum_inf = $donnees['hum'] < 40;
        $hum_sup = $donnees['hum'] > 70;

        $actions1 = [$this->OUVRIR_PORTES_FENETRES, $this->ETEINDRE_CHAUFFAGE, $this->ALLUMER_VENTILATEURS];
        $actions2 = [$this->OUVRIR_PORTES_FENETRES, $this->ALLUMER_VENTILATEURS];
        $actions3 = [$this->FERMER_PORTES_FENETRES, $this->ETEINDRE_CHAUFFAGE, $this->ALLUMER_VENTILATEURS];
        $actions4 = [$this->FERMER_PORTES_FENETRES, $this->ALLUMER_CHAUFFAGE, $this->ALLUMER_VENTILATEURS];
        $actions5 = [$this->ALLUMER_CHAUFFAGE, $this->ALLUMER_VENTILATEURS];
        $actions6 = [$this->OUVRIR_PORTES_FENETRES, $this->ETEINDRE_CHAUFFAGE];
        $actions7 = [$this->OUVRIR_PORTES_FENETRES];
        $actions8 = [$this->FERMER_PORTES_FENETRES, $this->ALLUMER_VENTILATEURS];
        $actions9 = [$this->FERMER_PORTES_FENETRES, $this->ETEINDRE_CHAUFFAGE];
        $actions10 = [$this->FERMER_PORTES_FENETRES, $this->ALLUMER_CHAUFFAGE];
        $actions12 = [$this->FERMER_PORTES_FENETRES];
        $actions14 = [$this->ALLUMER_CHAUFFAGE];
        $actions15 = [$this->ETEINDRE_CHAUFFAGE, $this->OUVRIR_PORTES];

        if ($co2_sup and $temp_sup and $hum_sup) {
            return ["CO2, température et humidité sont tous supérieurs aux seuils.", $actions1];
        }
        if ($co2_sup and $temp_sup and $hum_inf) {
            return ["CO2 et température sont supérieurs aux seuils, mais l'humidité est inférieure au seuil.", $actions1];
        }
        if ($co2_sup and $temp_inf and $hum_sup) {
            return ["CO2 et humidité sont supérieurs aux seuils, mais la température est inférieure au seuil.", $actions2];
        }
        if ($co2_sup and $temp_inf and $hum_inf) {
            return ["CO2 est supérieur au seuil, mais la température et l'humidité sont inférieures aux seuils.", $actions2];
        }
        if ($co2_inf and $temp_sup and $hum_sup) {
            return ["Température et humidité sont supérieures aux seuils, mais CO2 est inférieur au seuil.", $actions3];
        }
        if ($co2_inf and $temp_sup and $hum_inf) {
            return ["Température est supérieure au seuil, mais CO2 et humidité sont inférieurs aux seuils.", $actions3];
        }
        if ($co2_inf and $temp_inf and $hum_sup) {
            return ["Humidité est supérieure au seuil, mais CO2 et température sont inférieurs aux seuils.", $actions4];
        }
        if ($co2_inf and $temp_inf and $hum_inf) {
            return ["CO2, température et humidité sont tous inférieurs aux seuils.", $actions4];
        }
        if ($temp_sup and $hum_sup) {
            return ["Température et humidité sont supérieures aux seuils.", $actions1];
        }
        if ($temp_sup and $hum_inf) {
            return ["Température est supérieure au seuil, mais humidité est inférieure au seuil.", $actions1];
        }
        if ($temp_inf and $hum_sup) {
            return ["Humidité est supérieure au seuil, mais température est inférieure au seuil.", $actions5];
        }
        if ($temp_inf and $hum_inf) {
            return ["Température et humidité sont inférieures aux seuils.", $actions5];
        }
        if ($co2_sup and $hum_sup) {
            return ["CO2 et humidité sont supérieurs aux seuils.", $actions2];
        }
        if ($co2_sup and $hum_inf) {
            return ["CO2 est supérieur au seuil, mais humidité est inférieure au seuil.", $actions2];
        }
        if ($co2_sup and $temp_sup) {
            return ["CO2 et température sont supérieurs aux seuils.", $actions6];
        }
        if ($co2_sup and $temp_inf) {
            return ["CO2 est supérieur au seuil, mais température est inférieure au seuil.", $actions7];
        }
        if ($co2_inf and $hum_sup) {
            return ["Humidité est supérieure au seuil, mais CO2 est inférieur au seuil.", $actions8];
        }
        if ($co2_inf and $hum_inf) {
            return ["CO2 et humidité sont inférieurs aux seuils.", $actions8];
        }
        if ($co2_inf and $temp_sup) {
            return ["Température est supérieure au seuil, mais CO2 est inférieur au seuil.", $actions9];
        }
        if ($co2_inf and $temp_inf) {
            return ["CO2 et température sont inférieurs aux seuils.", $actions10];
        }
        if ($temp_sup) {
            return ["Température est supérieure au seuil.", $actions15];
        }
        if ($hum_sup) {
            return ["Humidité est supérieure au seuil.", $actions2];
        }
        if ($hum_inf) {
            return ["Humidité est inférieure au seuil.", $actions2];
        }
        if ($co2_sup) {
            return ["CO2 est supérieur au seuil.", $actions7];
        }
        if ($temp_inf) {
            return ["Température est inférieure au seuil.", $actions14];
        }
        if ($co2_inf) {
            return ["CO2 est inférieur au seuil.", $actions12];
        }

        return ['Bonne ambiance', 'Pas de recommandations pour la salle'];
    }

    /**
     * Génère des recommandations d'action en fonction des conditions atmosphériques pour le printemps.
     * @param array<string, string> $donnees Les données atmosphériques comme le CO2, la température et l'humidité.
     * @return array<int, string|array<int, string>> Les recommandations d'action basées sur les données fournies.
     */
    public function recommandationsPrintemps(array $donnees): array
    {
        $co2_inf = $donnees['co2'] < 400;
        $co2_sup = $donnees['co2'] > 1000;
        $temp_inf = $donnees['temp'] < 20;
        $temp_sup = $donnees['temp'] > 24;
        $hum_inf = $donnees['hum'] < 40;
        $hum_sup = $donnees['hum'] > 70;

        $actions1 = [$this->OUVRIR_PORTES_FENETRES, $this->ALLUMER_VENTILATEURS, $this->FERMER_RIDEAUX_VOLETS];
        $actions2 = [$this->OUVRIR_PORTES_FENETRES, $this->ALLUMER_VENTILATEURS];
        $actions3 = [$this->FERMER_PORTES_FENETRES, $this->ALLUMER_VENTILATEURS, $this->FERMER_RIDEAUX_VOLETS];
        $actions4 = [$this->FERMER_PORTES_FENETRES, $this->ALLUMER_VENTILATEURS, $this->ALLUMER_CHAUFFAGE];
        $actions5 = [$this->ALLUMER_CHAUFFAGE, $this->ALLUMER_VENTILATEURS];
        $actions6 = [$this->OUVRIR_PORTES_FENETRES];
        $actions7 = [$this->FERMER_PORTES_FENETRES, $this->ALLUMER_VENTILATEURS];
        $actions8 = [$this->FERMER_PORTES_FENETRES, $this->ALLUMER_CHAUFFAGE];
        $actions9 = [$this->FERMER_PORTES_FENETRES];
        $actions10 = [$this->ALLUMER_CHAUFFAGE];


        if ($co2_sup and $temp_sup and $hum_sup) {
            return ["CO2, température, et humidité sont tous trop élevés.", $actions1];
        }
        if ($co2_sup and $temp_sup and $hum_inf) {
            return ["CO2 et température sont trop élevés, humidité trop faible.", $actions1];
        }
        if ($co2_sup and $temp_inf and $hum_sup) {
            return ["CO2 et humidité sont trop élevés, température trop faible.", $actions2];
        }
        if ($co2_sup and $temp_inf and $hum_inf) {
            return ["CO2 est trop élevé, température et humidité sont trop faibles.", $actions2];
        }
        if ($co2_inf and $temp_sup and $hum_sup) {
            return ["Température et humidité sont trop élevées, CO2 trop faible.", $actions3];
        }
        if ($co2_inf and $temp_sup and $hum_inf) {
            return ["Température est trop élevée, CO2 et humidité sont trop faibles.", $actions3];
        }
        if ($co2_inf and $temp_inf and $hum_sup) {
            return ["Humidité est trop élevée, CO2 et température sont trop faibles.", $actions4];
        }
        if ($co2_inf and $temp_inf and $hum_inf) {
            return ["CO2, température, et humidité sont tous trop faibles.", $actions4];
        }
        if ($temp_sup and $hum_sup) {
            return ["Température et humidité sont trop élevées.", $actions1];
        }
        if ($temp_sup and $hum_inf) {
            return ["Température est trop élevée, humidité trop faible.", $actions1];
        }
        if ($co2_sup and $temp_sup) {
            return ["CO2 et température sont trop élevés.", $actions1];
        }
        if ($co2_sup and $hum_sup) {
            return ["CO2 et humidité sont trop élevés.", $actions2];
        }
        if ($co2_sup and $hum_inf) {
            return ["CO2 est trop élevé, humidité trop faible.", $actions2];
        }
        if ($co2_inf and $temp_sup) {
            return ["Température est trop élevée, CO2 trop faible.", $actions3];
        }
        if ($temp_inf and $hum_sup) {
            return ["Température est trop faible, humidité trop élevée.", $actions5];
        }
        if ($temp_inf and $hum_inf) {
            return ["Température et humidité sont trop faibles.", $actions5];
        }
        if ($co2_sup and $temp_inf) {
            return ["CO2 est trop élevé, température trop faible.", $actions6];
        }
        if ($co2_inf and $hum_sup) {
            return ["Humidité est trop élevée, CO2 trop faible.", $actions7];
        }
        if ($co2_inf and $hum_inf) {
            return ["CO2 et humidité sont trop faibles.", $actions7];
        }
        if ($co2_inf and $temp_inf) {
            return ["CO2 et température sont trop faibles.", $actions8];
        }
        if ($temp_sup) {
            return ["Température est trop élevée.", $actions1];
        }
        if ($hum_sup) {
            return ["Humidité est trop élevée.", $actions2];
        }
        if ($hum_inf) {
            return ["Humidité est trop faible.", $actions2];
        }
        if ($co2_sup) {
            return ["CO2 est trop élevé.", $actions6];
        }
        if ($temp_inf) {
            return ["Température est trop faible.", $actions9];
        }
        if ($co2_inf) {
            return ["CO2 est trop faible.", $actions10];
        }

        return ['Bonne ambiance', 'Pas de recommandations pour la salle'];
    }

    /**
     * Recherche un utilisateur par son nom d'utilisateur.
     * @param string $username Le nom d'utilisateur à rechercher.
     * @return User|null L'utilisateur trouvé ou null si aucun utilisateur n'a été trouvé.
     */
    public function rechercheUser(string $username): ?User
    {
        return $this->findOneBy(['username' => $username]);
    }

    /**
     * Fournit des recommandations pour les salles en fonction de la saison et des données atmosphériques.
     * @param array<string, string> $donnees Les données atmosphériques.
     * @param string $date La date actuelle pour déterminer la saison.
     * @return array<int, string|array<int, string>> Les recommandations d'action pour la saison donnée.
     */
    public function recommandationsSalles(array $donnees, string $date): array
    {
        $anneeActuelle = date("Y");
        if (($date >= ($anneeActuelle - 1) . '-12-22 00:00:00' and $date < $anneeActuelle . '-03-20 00:00:00') ||
            ($date >= $anneeActuelle . '-12-22 00:00:00' and $date <= $anneeActuelle . '-12-31 23:59:59')) {
            return $this->recommandationsHiver($donnees);
        }
        else if ($date >= $anneeActuelle . '-03-20 00:00:00' and $date < $anneeActuelle . '-06-20 00:00:00') {
            return $this->recommandationsPrintemps($donnees);
        }
        else if ($date >= $anneeActuelle . '-06-20 00:00:00' and $date < $anneeActuelle . '-09-23 00:00:00') {
            return $this->recommandationsEte($donnees);
        }
        else if ($date >= $anneeActuelle . '-09-23 00:00:00' and $date < $anneeActuelle . '-12-22 00:00:00') {
            return $this->recommandationsAutomne($donnees);
        }
        return [];
    }

    /**
     * Fournit les intervalles de température recommandés pour chaque saison.
     * @param string $date La date actuelle pour déterminer la saison.
     * @return array<int, int> Les intervalles de température pour la saison donnée.
     */
    public function intervallesTempSaison(string $date): array
    {
        $anneeActuelle = date("Y");
        if (($date >= ($anneeActuelle - 1) . '-12-22 00:00:00' and $date < $anneeActuelle . '-03-20 00:00:00') ||
            ($date >= $anneeActuelle . '-12-22 00:00:00' and $date <= $anneeActuelle . '-12-31 23:59:59')) {
            return [16, 18, 22, 24];
        }
        else if ($date >= $anneeActuelle . '-03-20 00:00:00' and $date < $anneeActuelle . '-06-20 00:00:00') {
            return [18, 20, 24, 26];
        }
        else if ($date >= $anneeActuelle . '-06-20 00:00:00' and $date < $anneeActuelle . '-09-23 00:00:00') {
            return [24, 26, 28, 30];
        }
        else if ($date >= $anneeActuelle . '-09-23 00:00:00' and $date < $anneeActuelle . '-12-22 00:00:00') {
            return [17, 19, 23, 25];
        }
        return [];
    }

}
