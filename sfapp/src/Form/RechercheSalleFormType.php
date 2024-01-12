<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Classe RechercheSalleFormType pour créer un formulaire de recherche pour les salles.
 * Cette classe étend AbstractType et définit la structure du formulaire pour permettre la recherche
 * de salles par bâtiment et/ou par nom de salle.
 */
class RechercheSalleFormType extends AbstractType
{
    /**
     * Construit le formulaire de recherche pour les salles.
     * Ajoute des champs pour la sélection d'un bâtiment, la saisie du nom d'une salle, et un bouton de soumission.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire.
     * @param array $options Les options pour le constructeur de formulaire, y compris la liste des bâtiments disponibles.
     */
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

    /**
     * Configure les options par défaut pour le formulaire de recherche des salles.
     * Définit les options par défaut, y compris la liste des bâtiments disponibles et la méthode de requête HTTP utilisée pour le formulaire.
     *
     * @param OptionsResolver $resolver Le résolveur d'options pour le formulaire.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        // Définition d'options par défaut, notamment la méthode de soumission du formulaire et la liste des batiments.
        $resolver->setDefaults([
            'liste_batiments' => [],
            'method' => 'GET',
        ]);
    }
}
