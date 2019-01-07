<?php

namespace App\Form;

use App\Entity\Room;
use App\Entity\Unavailability;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnavailabilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate')
            ->add('endDate')
            ->add('guests')
            ->add('object')
            ->add('type')
//            ->add('organiser')
//            ->add('room')
            ->add('organiser', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'expanded' => false,
                'multiple' => false,
                'label' => 'Organisateur'
            ])
            ->add('room', EntityType::class, [
                'class' => Room::class,
                'choice_label' => 'name',
                'expanded' => false,
                'multiple' => false,
                'label' => 'Salle'
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
