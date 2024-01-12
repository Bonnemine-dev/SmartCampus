<?php

namespace App\EntityListener;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Classe UserListener pour gérer les événements liés aux entités User.
 * Cette classe écoute les événements de cycle de vie Doctrine pour les entités User,
 * permettant de hacher le mot de passe avant la persistance et la mise à jour.
 */
class UserListener
{
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * Constructeur de UserListener.
     * Initialise le hacheur de mot de passe.
     *
     * @param UserPasswordHasherInterface $passwordHasher Le service de hachage de mot de passe.
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    /**
     * Méthode appelée avant la persistance d'un utilisateur.
     * Hache le mot de passe de l'utilisateur avant de le sauvegarder.
     *
     * @param User $user L'entité utilisateur à traiter.
     */
    public function prePersist(User $user): void
    {
        $this->encodePassword($user);
    }

    /**
     * Méthode appelée avant la mise à jour d'un utilisateur.
     * Hache le mot de passe de l'utilisateur avant de le sauvegarder.
     *
     * @param User $user L'entité utilisateur à traiter.
     */
    public function preUpdate(User $user): void
    {
        $this->encodePassword($user);
    }

    /**
     * Hache le mot de passe de l'utilisateur.
     * Si l'utilisateur a un plainPassword, le hache et le définit comme son mot de passe,
     * puis efface le plainPassword pour des raisons de sécurité.
     *
     * @param User $user L'entité utilisateur dont le mot de passe doit être haché.
     */
    public function encodePassword(User $user)
    {
        if($user->getPlainPassword() == null){
            return;
        }
        $user->setPassword($this->passwordHasher->hashPassword(
            $user,
            $user->getPlainPassword(),
        ));

        $user->setPlainPassword(null);
    }
}