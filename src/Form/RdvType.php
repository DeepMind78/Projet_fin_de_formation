<?php

namespace App\Form;

use App\Entity\Rdv;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
                'days'=> range(date('d'),31), 
                'months' => range(date('m'), 12),
                
            ])
            ->add('heure', TimeType::class, [
                'hours' => range(7,22),
                'minutes'=>range(0,0)
                // 'with_minutes'=>false
                
            ])
            ->add('duree', ChoiceType::class, [
                "choices"=> [
                    1=>1,
                    2=>2,
                    3=>3
                ],
                    "label" => "Durée en heure",
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
