<?php

namespace App\Form;

use App\Entity\QuizResult;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuizResultType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userid', HiddenType::class) // Assuming you have a user ID
            ->add('score', IntegerType::class)
            ->add('questionnumber', HiddenType::class) // Assuming you have a question number
            ->add('quizsubmitted', HiddenType::class) // Assuming you have a quiz submitted flag
            // Add other fields as needed
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => QuizResult::class,
        ]);
    }
}
