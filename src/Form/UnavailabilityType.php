<?php

namespace App\Form;

use App\Entity\Room;
use App\Entity\Unavailability;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
                'date_format' => 'dd/MM/yyyy HH:ii',
                'widget' => 'single_text',
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'Fin',
                'date_format' => 'dd/MM/yyyy HH:ii',
                'widget' => 'single_text'
            ])
            //->add('guests')
            ->add('guests', EntityType::class, [
                'label' => 'Invités',
                'class' => User::class,
                'choice_label' => 'email',
                'multiple' => true

            ])
            ->add('object', TextType::class, [
                'label' => 'Objet'
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
