<?php

namespace App\Form;

/*use App\Entity\Salle;*/
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Classe FiltreSalleFormType pour créer un formulaire de filtrage pour les salles.
 * Cette classe étend AbstractType et définit la structure du formulaire pour filtrer les salles
 * en fonction de divers critères tels que l'étage, l'orientation, la présence d'ordinateurs, et de systèmes d'acquisition.
 */
class FiltreSalleFormType extends AbstractType
{
    /**
     * Construit le formulaire de filtrage pour les salles.
     * Ajoute des champs de sélection pour filtrer les salles par étage, orientation, présence d'ordinateurs, et de systèmes d'acquisition.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire.
     * @param array $options Les options pour le constructeur de formulaire.
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Ajout d'un champ 'etage' de type ChoiceType avec des options spécifiques.
        $builder
            ->add('etage', ChoiceType::class, [
                'choices' => [
                    'RDC' => 0,
                    '1' => 1,
                    '2' => 2,
                    '3' => 3
                ],
                'expanded' => true,
                'multiple' => true,
                'label' => 'Étage',
                'required' => false,
            ])
            // Ajout d'un champ 'orientation' de type ChoiceType avec des options spécifiques.
            ->add('orientation', ChoiceType::class, [
                'choices' => [
                    'Nord' => 'nord',
                    'Sud' => 'sud',
                    'Est' => 'est',
                    'Ouest' => 'ouest'
                ],
                'expanded' => true,
                'multiple' => true,
                'label' => 'Orientation',
                'required' => false,
            ])
            // Ajout d'un champ 'ordinateurs' de type ChoiceType avec des options spécifiques.
            ->add('ordinateurs', ChoiceType::class, [
                'choices' => [
                    'Non spécifié' => null,
                    'Avec' => 1,
                    'Sans' => 0,
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'Ordinateur',
                'required' => true,
            ])
            // Ajout d'un champ 'sa' de type ChoiceType avec des options spécifiques.
            ->add('sa', ChoiceType::class, [
                'choices' => [
                    'Non spécifié' => null,
                    'Avec' => 1,
                    'Sans' => 0,
                    'Demande en cours' => 2,
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'Système d\'acquisition',
                'required' => true,
            ]);
    }

    /**
     * Configure les options par défaut pour le formulaire de filtrage des salles.
     * Définit les options par défaut, y compris la méthode de requête HTTP utilisée pour le formulaire.
     *
     * @param OptionsResolver $resolver Le résolveur d'options pour le formulaire.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        // Définition d'options par défaut, notamment la méthode de soumission du formulaire.
        $resolver->setDefaults([
            'method' => 'GET',
        ]);
    }
}
