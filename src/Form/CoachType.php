<?php

namespace App\Form;

use App\Entity\Coach;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class CoachType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('imageFile', FileType::class, [
                'required'=> false,
                'label' => 'Photo'
                ])
            ->add('adresse')
            ->add('codePostal')
            ->add('ville')
            ->add('age')
            ->add('telephone')
            ->add('diplomeFile', FileType::class, [
                'required'=>false,
                'label' => 'Diplôme'
                ])
            ->add('domaine')
            ->add('prix', null, ['attr'=>[
        'placeholder'=>"Prix à l'heure"
                ]
            ])
            ->add('descriptionCoach')
            ->add('descriptionSeance');

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Coach::class,
        ]);
    }
}
