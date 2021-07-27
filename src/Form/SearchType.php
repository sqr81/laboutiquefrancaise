<?php

namespace App\Form;

use App\Classe\Search;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

Class SearchType extends AbstractType
{
    //on cree le form
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('string', TextType::class,[
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Votre recherche...',
                    'class='=> 'form-control-sm',
                ]
            ])
            ->add('categories', EntityType::class,[
                'label' => false,
                'required' => false,
                //on lie à la classe Category
                'class' => Category::class,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add ('submit', SubmitType::class,[
                'label' => 'Filtrer',
                'attr' => [
                    'class' => 'btn-block btn-info'
                    ]
    ]);
    }


    //une fonction pour configurer les options
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            //on va lier le form à la classe Search
            'data_class' => Search::class,
            //on pass les infos par url
            'method' => 'GET',
            //pas besoin de cryptage
            'csrf_protection' => false,
        ]);
    }

    //pour que l url retournee soit propre
    public function getBlockPrefix()
    {
        return '';
    }
}