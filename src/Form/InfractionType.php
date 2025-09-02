<?php

namespace App\Form;

use App\Entity\Infraction;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType; // Import DateType
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class InfractionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username', // Assuming 'username' is the property of User entity you want to display
                'label' => 'User',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('infractionDate', DateType::class, [ // Change to DateType
                'label' => 'Infraction Date',
                'widget' => 'single_text',
                'html5' => true, // Enable HTML5 compatibility for date input
                'attr' => ['class' => 'js-datepicker form-control'],
            ])
            ->add('infractionType', TextType::class, [
                'label' => 'Infraction Type',
                'attr' => ['class' => 'form-control'],
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Infraction::class,
        ]);
    }
}
