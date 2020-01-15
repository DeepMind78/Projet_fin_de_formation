<?php

namespace App\Form;


use App\Entity\CoachSearch;
use App\Repository\CoachRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\StringType;


class CoachSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       
        $builder
            ->add('sport', null ,[
                'required'=> false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Sport'
                ]
            ])
            ->add('ville',null,[
                'required'=>false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Ville'
                ]
            ])
            
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CoachSearch::class,
        ]);
    }

    
}
