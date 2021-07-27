<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Carrier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //dd($options);
        //pour recup les adresses du user
        $user = $options['user'];

        $builder
            ->add('adresses', EntityType::class, [
                'label' =>false,
                'required' => true,
                'class' => Address::class,
                //pour recup les adresses du user
                'choices' => $user->getAddresses(),
                'multiple' => false,
                'expanded' => true,

            ])
            ->add('carriers', EntityType::class, [
                'label' =>'Choisissez votre transporteur',
                'required' => true,
                'class' => Carrier::class,
                //pour recup les adresses du user
                'multiple' => false,
                'expanded' => true,

            ])
            ->add('submit', SubmitType::class,[
                'label'=> 'Valider ma commande',
                'attr' =>[
                    'class'=> 'btn btn-success btn-block'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
            //on recupere le user passé dans OrderController
            'user' => array()
        ]);
    }
}
