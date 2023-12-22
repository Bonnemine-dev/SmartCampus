<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
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

    public function recommandationsEte($donnees) {
        $co2_inf = $donnees['co2'] < 400;
        $co2_sup = $donnees['co2'] > 1000;
        $temp_inf = $donnees['temp'] < 18;
        $temp_sup = $donnees['temp'] > 28;
        $hum_inf = $donnees['hum'] < 40;
        $hum_sup = $donnees['hum'] > 70;

        // Définition des ensembles d'actions numérotées
        $actions1 = ['Ouvrir les portes et les fenêtres', 'Allumer les ventilateurs', 'Fermer les rideaux et les volets', 'Éteindre le chauffage'];
        $actions2 = ['Ouvrir les portes et les fenêtres', 'Allumer les ventilateurs'];
        $actions3 = ['Ouvrir les portes et les fenêtres', 'Éteindre le chauffage', 'Éteindre les ventilateurs'];
        $actions4 = ['Fermer les portes et les fenêtres', 'Allumer les ventilateurs', 'Fermer les rideaux et les volets', 'Éteindre le chauffage'];
        $actions5 = ['Fermer les portes et les fenêtres', 'Allumer le chauffage', 'Allumer les ventilateurs'];
        $actions6 = ['Fermer les portes et les fenêtres', 'Allumer le chauffage', 'Éteindre les ventilateurs'];
        $actions7 = ['Ouvrir les portes', 'Fermer les fenêtres', 'Allumer les ventilateurs', 'Fermer les rideaux et les volets', 'Éteindre le chauffage'];
        $actions8 = ['Ouvrir les portes et les fenêtres'];
        $actions9 = ['Fermer les portes et les fenêtres', 'Allumer les ventilateurs'];
        $actions10 = ['Fermer les portes et les fenêtres', 'Éteindre le chauffage', 'Éteindre les ventilateurs'];
        $actions11 = ['Fermer les portes et les fenêtres', 'Allumer le chauffage'];
        $actions12 = ['Fermer les portes et les fenêtres'];

        // Application des conditions
        if (($co2_sup and $temp_sup and $hum_sup) or ($co2_sup and $temp_sup and $hum_inf)) {
            return $actions1;
        }
        else if ($co2_sup and $temp_inf and $hum_sup) {
            return $actions2;
        }
        else if ($co2_sup and $temp_inf and $hum_inf) {
            return $actions3;
        }
        else if (($co2_inf and $temp_sup and $hum_sup) or ($co2_inf and $temp_sup and $hum_inf)) {
            return $actions4;
        }
        else if ($co2_inf and $temp_inf and $hum_sup) {
            return $actions5;
        }
        else if ($co2_inf and $temp_inf and $hum_inf) {
            return $actions6;
        }
        else if (($temp_sup and $hum_sup) or ($co2_sup and $temp_sup)) {
            return $actions1;
        }
        else if (($temp_inf and $hum_sup) or ($co2_sup and $hum_sup)) {
            return $actions2;
        }
        else if ($co2_sup and $hum_inf) {
            return $actions3;
        }
        else if ($co2_inf and $temp_sup) {
            return $actions4;
        }
        else if ($temp_inf and $hum_inf) {
            return $actions6;
        }
        else if ($temp_sup and $hum_inf) {
            return $actions7;
        }
        else if (($co2_sup and $temp_inf)) {
            return $actions8;
        }
        else if (($co2_inf and $hum_sup)) {
            return $actions9;
        }
        else if (($co2_inf and $hum_inf)) {
            return $actions10;
        }
        else if (($co2_inf and $temp_inf)) {
            return $actions11;
        }
        else if ($temp_sup) {
            return $actions1;
        }
        else if ($hum_sup) {
            return $actions2;
        }
        else if (($temp_inf) or ($co2_sup)) {
            return $actions8;
        }
        else if ($hum_inf) {
            return $actions10;
        }
        else if ($co2_inf) {
            return $actions12;
        }
        return [];
    }

    public function recommandationsAutomne($donnees) {
        $co2_inf = $donnees['co2'] < 400;
        $co2_sup = $donnees['co2'] > 1000;
        $temp_inf = $donnees['temp'] < 18;
        $temp_sup = $donnees['temp'] > 25;
        $hum_inf = $donnees['hum'] < 40;
        $hum_sup = $donnees['hum'] > 70;
    }

    public function recommandationsHiver($donnees) {
        $co2_inf = $donnees['co2'] < 400;
        $co2_sup = $donnees['co2'] > 1000;
        $temp_inf = $donnees['temp'] < 18;
        $temp_sup = $donnees['temp'] > 24;
        $hum_inf = $donnees['hum'] < 30;
        $hum_sup = $donnees['hum'] > 60;
    }

    public function recommandationsPrintemps($donnees) {
        $co2_inf = $donnees['co2'] < 400;
        $co2_sup = $donnees['co2'] > 1000;
        $temp_inf = $donnees['temp'] < 18;
        $temp_sup = $donnees['temp'] > 25;
        $hum_inf = $donnees['hum'] < 40;
        $hum_sup = $donnees['hum'] > 70;
    }

    public function rechercheUser($username): ?User
    {
        return $this->findOneBy(['username' => $username]);
    }

    public function recommandationsSalles($donnees) {
        $anneeActuelle = date("Y");
        if ($donnees['dateCapture'] >= $anneeActuelle . '-12-22 00:00:00' and $donnees['dateCapture'] < $anneeActuelle . '-03-20 00:00:00') {
            return $this->recommandationsHiver($donnees);
        }
        else if ($donnees['dateCapture'] >= $anneeActuelle . '-03-20 00:00:00' and $donnees['dateCapture'] < $anneeActuelle . '-06-20 00:00:00') {
            return $this->recommandationsPrintemps($donnees);
        }
        else if ($donnees['dateCapture'] >= $anneeActuelle . '-06-20 00:00:00' and $donnees['dateCapture'] < $anneeActuelle . '-09-23 00:00:00') {
            return $this->recommandationsEte($donnees);
        }
        else if ($donnees['dateCapture'] >= $anneeActuelle . '-09-23 00:00:00' and $donnees['dateCapture'] < $anneeActuelle . '-12-22 00:00:00') {
            return $this->recommandationsAutomne($donnees);
        }
    }

//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
