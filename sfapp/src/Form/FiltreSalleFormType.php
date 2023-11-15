<?php

namespace App\Form;

/*use App\Entity\Salle;*/
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreSalleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
        ]);
    }
}
