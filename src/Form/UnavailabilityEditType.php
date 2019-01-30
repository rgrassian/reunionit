<?php

namespace App\Form;

use App\Entity\Room;
use App\Entity\Unavailability;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnavailabilityEditType extends UnavailabilityType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('startDate', DateTimeType::class, [
                'label' => 'Début',
                'date_format' => 'dd/MM/yyyy HH:ii',
                'widget' => 'single_text',
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'Fin',
                'date_format' => 'dd/MM/yyyy HH:ii',
                'widget' => 'single_text'
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
