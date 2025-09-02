<?php

namespace App\Form;

use App\Entity\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class FormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Student Form' => 'Student Form',
                    'Teacher Form' => 'Teacher Form',
                ],
                'placeholder' => 'Choose a form type',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Active' => 'Active',
                    'Inactive' => 'Inactive',
                ],
                'data' => 'active', // Set default value to 'active'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Form::class,
        ]);
    }
}
