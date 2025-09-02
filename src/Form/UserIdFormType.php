<?php

namespace App\Form;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;

class UserIdFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user_id', HiddenType::class, [
                'mapped' => false, // This field is not mapped to an entity property
            ])
            ->add('confirm', SubmitType::class, ['label' => 'Delete Article']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, // This form doesn't map to a specific data class
        ]);
    }
}
