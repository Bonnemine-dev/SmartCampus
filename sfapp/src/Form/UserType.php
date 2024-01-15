<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Classe UserType pour créer un formulaire de gestion des mots de passe des utilisateurs.
 * Cette classe étend AbstractType et définit la structure du formulaire pour permettre à un utilisateur
 * de changer son mot de passe.
 */
class UserType extends AbstractType
{
    /**
     * Construit le formulaire pour la modification du mot de passe de l'utilisateur.
     * Ajoute des champs pour l'ancien mot de passe, le nouveau mot de passe, la confirmation du nouveau mot de passe, et un bouton de soumission.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire.
     * @param array $options Les options pour le constructeur de formulaire.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('MDP', PasswordType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Mot de passe actuel'
                ],
                'label' => false,
            ])
            ->add('PlainPassword', PasswordType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Nouveau mot de passe'
                ],
                'label' => false,
            ])
            ->add('verif', PasswordType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Confirmer votre mot de passe'
                ],
                'label' => false,
            ])
            // Ajout d'un champ 'submit' de type SubmitType avec des options spécifiques.
            ->add('submit', SubmitType::class, [
                'label' => 'Modifier'
            ]);
    }
}
