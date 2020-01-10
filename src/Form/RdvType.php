<?php

namespace App\Form;

use App\Entity\Rdv;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RdvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder
            ->add('jour', DateType::class, [
                'years' => range(2020,2100),
                
            ])
            ->add('heure', TimeType::class, [
                'hours' => range(7,22),
                
            ])
            ->add('duree', null, [
                'attr' => [
                    "placeholder" => "Nombre d'heure"
                ]
            ])
            ->add('lieu')
        
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Rdv::class,
        ]);
    }
}
