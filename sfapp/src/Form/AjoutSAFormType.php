<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class AjoutSAFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Ajout d'un champ 'batiment' de type ChoiceType avec des options spécifiques.
        $builder
            ->add('nom', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Nom du SA'
                ],
                'label' => false,
            ])
            // Ajout d'un champ 'submit' de type SubmitType avec des options spécifiques.
            ->add('submit', SubmitType::class, [
                'label' => 'ajout'
            ]);
    }
}
