<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Classe RechercheSAFormType pour créer un formulaire de recherche pour les Systèmes d'Acquisition (SA).
 * Cette classe étend AbstractType et définit la structure du formulaire pour permettre la recherche
 * de SA par nom.
 */
class RechercheSAFormType extends AbstractType
{
    /**
     * Construit le formulaire de recherche pour les Systèmes d'Acquisition (SA).
     * Ajoute un champ de texte pour la saisie du nom du SA et un bouton de soumission.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire.
     * @param array $options Les options pour le constructeur de formulaire.
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Ajout d'un champ 'batiment' de type ChoiceType avec des options spécifiques.
        $builder
            ->add('sa_nom', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Rechercher un SA'
                ],
                'label' => false,
            ])
            // Ajout d'un champ 'submit' de type SubmitType avec des options spécifiques.
            ->add('submit', SubmitType::class, [
                'label' => 'Rechercher'
            ]);
    }
}
