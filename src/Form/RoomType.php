<?php

namespace App\Form;

use App\Entity\Room;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'style' => 'min-width: 250px;margin-bottom: 5px;'
                ]
            ])
            ->add('capacity', IntegerType::class, [
                'label' => 'Capacité',
                'attr' => [
                    'style' => 'min-width: 250px;margin-bottom: 5px;'
                ]
            ])
            ->add('features', ChoiceType::class, [
                'label' => 'Options',
                'choices' => [
                    'Wifi' => 'Wifi',
                    'Vidéoprojecteur' => 'Vidéoprojecteur',
                    'Paperboard' => 'Paperboard',
                    'Chauffage au sol' => 'Chauffage au sol',
                    'Balcon ou terrasse' => 'Balcon',
                    'Estrade' => 'Estrade',
                ],
                'expanded' => true,
                'multiple' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Room::class,
        ]);
    }
}
