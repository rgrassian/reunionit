<?php

namespace App\Form;

use App\Entity\Room;
use App\Entity\Unavailability;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnavailabilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DateTimeType::class, [
                'label' => 'Début',
                'format' => 'yyyy'
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'Fin'
            ])
            ->add('guests')
            ->add('object', TextType::class, [
                'label' => 'Objet'
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de réservation',
                'choices' => [
                    'Sélectionnez' => [
                        'réunion' => 0,
                        'autre' => 1
                    ]
                ],
                'expanded' => false,
                'multiple' => false
            ])
            ->add('organiser', EntityType::class, [
                'label' => 'Organisateur',
                'class' => User::class,
                'choice_label' => 'email',
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('room', EntityType::class, [
                'label' => 'Salle',
                'class' => Room::class,
                'choice_label' => 'name'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Unavailability::class,
        ]);
    }
}
