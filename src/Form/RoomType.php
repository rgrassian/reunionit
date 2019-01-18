<?php

namespace App\Form;

use App\Entity\Room;
use App\Provider\FeaturesProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoomType extends AbstractType
{
    private $featuresProvider;

    public function __construct(FeaturesProvider $featuresProvider)
    {
        $this->featuresProvider = $featuresProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $features = $this->featuresProvider->getFeatures();
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('capacity', IntegerType::class, [
                'label' => 'Capacité',
                'attr' => [
                    'min' => 0
                ]
            ])
            ->add('features', ChoiceType::class, [
                'label' => 'Options',
                'attr' => [
                    'class' => 'custom-control custom-checkbox',
                    'style' => 'text-color: red;'
                ],
                'choices' => $features,
                'expanded' => true,
                'multiple' => true
            ])
            ->add('picture', FileType::class, [
                'required' => true,
                'label' => false,
                'attr' => [
                    'class' => 'dropify',
                    'data-default-file' => $options['image_url']
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Room::class,
            'image_url' => null
        ]);
    }
}
