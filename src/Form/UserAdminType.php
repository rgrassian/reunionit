<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class UserAdminType extends UserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'roles', ChoiceType::class, [
                'choices' => [
                    'Administrateur' => 'ROLE_ADMIN',
                    'Salarié' => 'ROLE_EMPLOYEE',
                    'Invité' => 'ROLE_GUEST'
                ],
                'expanded' => false,
                'multiple' => true,
            ]
        );
    }
}