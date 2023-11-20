<?php

namespace App\Form;

/*use App\Entity\Salle;*/
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreSalleFormType extends AbstractType
{
    // Méthode pour construire le formulaire.
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

    // Méthode pour configurer les options du formulaire.
    public function configureOptions(OptionsResolver $resolver): void
    {
        // Définition d'options par défaut, notamment la méthode de soumission du formulaire.
        $resolver->setDefaults([
            'method' => 'GET',
        ]);
    }
}
