<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RechercheSalleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Ajout d'un champ 'batiment' de type ChoiceType avec des options spécifiques.
        $builder
            ->add('batiment', ChoiceType::class, [
                'choices' => $options['liste_batiments'],
                'placeholder' => 'Choisir un batiment',
                'required' => false,
                'label' => false,
                'attr' => ['class' => 'bat-selection'],
            ])
            // Ajout d'un champ 'salle' de type TextType avec des options spécifiques.
            ->add('salle', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Rechercher une salle'
                ],
                'label' => false,
            ])
            // Ajout d'un champ 'submit' de type SubmitType avec des options spécifiques.
            ->add('submit', SubmitType::class, [
                'label' => 'Rechercher'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // Définition d'options par défaut, notamment la méthode de soumission du formulaire et la liste des batiments.
        $resolver->setDefaults([
            'liste_batiments' => [],
            'method' => 'GET',
        ]);
    }
}
