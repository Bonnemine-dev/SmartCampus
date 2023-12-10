<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class FiltreSAFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('etat', ChoiceType::class, [
            'choices' => [
                'Éteint' => 0,
                'Allumé' => 1,
                'Défaillant' => 2,
            ],
            'expanded' => true,
            'multiple' => true,
            'label' => 'État',
            'required' => false
        ])
        ->add('localisation', ChoiceType::class, [
            'choices' => [
                'Stock' => 'stock',
                'Salle' => 'salle',
            ],
            'expanded' => true,
            'multiple' => true,
            'label' => 'Localisation',
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'method' => 'GET',
        ]);
    }
}
