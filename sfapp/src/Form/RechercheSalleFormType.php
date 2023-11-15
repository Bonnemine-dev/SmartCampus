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
        $builder
            ->add('batiment', ChoiceType::class, [
                'choices' => $options['liste_batiments'],
                'placeholder' => 'Choisir un batiment',
                'required' => false,
                'label' => false,
                'attr' => ['class' => 'bat-selection'],
            ])
            ->add('salle', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Rechercher une salle'
                ],
                'label' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Rechercher'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'liste_batiments' => [],
            'method' => 'GET',
        ]);
    }
}
