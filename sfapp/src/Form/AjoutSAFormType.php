<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Classe AjoutSAFormType pour créer le formulaire d'ajout d'un Système d'Acquisition (SA).
 * Cette classe étend AbstractType et définit la structure du formulaire pour l'ajout d'un SA.
 */
class AjoutSAFormType extends AbstractType
{
    /**
     * Construit le formulaire d'ajout d'un SA.
     * Cette méthode ajoute des champs au formulaire, y compris le nom du SA et un bouton de soumission.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire.
     * @param array $options Les options pour le constructeur de formulaire.
     */
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
