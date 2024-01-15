<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Classe FiltreSAFormType pour créer un formulaire de filtre pour les Systèmes d'Acquisition (SA).
 * Cette classe étend AbstractType et définit la structure du formulaire pour filtrer les SA
 * en fonction de différents critères tels que l'état et la localisation.
 */
class FiltreSAFormType extends AbstractType
{
    /**
     * Construit le formulaire de filtre pour les Systèmes d'Acquisition (SA).
     * Cette méthode ajoute des champs de sélection pour filtrer les SA par état et localisation.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire.
     * @param array $options Les options pour le constructeur de formulaire.
     */
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

    /**
     * Configure les options par défaut pour le formulaire de filtre des SA.
     * Cette méthode définit les options par défaut, y compris la méthode de requête HTTP utilisée.
     *
     * @param OptionsResolver $resolver Le résolveur d'options pour le formulaire.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'method' => 'GET',
        ]);
    }
}
