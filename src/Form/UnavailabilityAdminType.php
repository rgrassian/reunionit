<?php

namespace App\Form;


use App\Entity\Room;
use App\Entity\Unavailability;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;

class UnavailabilityAdminType extends UnavailabilityType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $users = $this->userRepository->findActiveUsers();

        parent::buildForm($builder, $options);

        $builder
            ->add('room', EntityType::class, [
                'label' => 'Salle',
                'class' => Room::class,
                'choice_label' => 'name'
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de réservation',
                'choices' => [
                    'Réunion' => Unavailability::REUNION,
                    'Autre' => Unavailability::AUTRE
                ],
                'expanded' => false,
                'multiple' => false
            ])
            ->add('organiser', EntityType::class, [
                'label' => 'Organisateur',
                'class' => User::class,
                'choices' => $users,
                'choice_label' => function(User $user) {return $user->getFirstName().' '.$user->getLastName();},
                'expanded' => false,
                'multiple' => false,
            ]);
    }
}
