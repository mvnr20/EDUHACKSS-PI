<?php

namespace App\Form;
use App\Entity\QuestionEvaluation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class QuestionEvaluationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                
        ->add('subject', TextType::class, [
            'label' => 'Subject',
            'attr' => [
                'class' => 'form-control mb-3',
                'placeholder' => 'Subject'
            ]
        ])
        ->add('question', TextType::class, [
            'label' => 'question',
            'attr' => [
                'class' => 'form-control mb-3',
                'placeholder' => 'question is '
            ]
        ])
        ->add('answer', TextType::class, [
            'label' => 'answer',
            'attr' => [
                'class' => 'form-control mb-3',
                'placeholder' => 'answer'
            ]
        ])
        ->add('description', TextType::class, [
            'label' => 'description',
            'label'=>'description',
            'attr' => [

                'class' => 'form-control mb-3',
                'placeholder' => 'description '
            ]
        ])
        ->add('option1', TextType::class, [
            'label' => 'option1',
            
            'attr' => [
                'class' => 'form-control mb-3',
                'placeholder' => 'option1 '
            ]
        ])
        ->add('option2', TextType::class, [
            'label' => 'option2',
            'attr' => [
                'class' => 'form-control mb-3',
                'placeholder' => 'option2'
            ]
        ])
        ->add('option3', TextType::class, [
            'label' => 'option3',
            'attr' => [
                'class' => 'form-control mb-3',
                'placeholder' => 'option3'
            ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QuestionEvaluation::class,
        ]);
    }
}
