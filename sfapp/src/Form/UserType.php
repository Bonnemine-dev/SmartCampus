<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
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
