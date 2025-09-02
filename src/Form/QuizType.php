<?php

// src/Form/QuizType.php

namespace App\Form;

use App\Entity\Quiz;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType; // Import DateTimeType
use Symfony\Component\Validator\Constraints as Assert;
class QuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Title', TextType::class, [
                'label' => 'Title',
                'attr' => [
                    'class' => 'form-control mb-3',
                    'placeholder' => 'Title'
                ]
            ])
            ->add('subject', TextType::class, [
                'label' => 'Subject',
                'attr' => [
                    'class' => 'form-control mb-3',
                    'placeholder' => 'Subject'
                ]
            ])
            ->add('NbQuestion', IntegerType::class, [
                'label' => 'Number of Questions',
                'attr' => [
                    'class' => 'form-control mb-3',
                    'placeholder' => 'Number of Questions',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\GreaterThanOrEqual([
                        'value' => 4,
                        'message' => 'The number of questions should be greater than or equal to 4.',
                    ]),
                ],
            ])
            ->add('datecreated', DateTimeType::class, [ // Add the datecreated field
                'label' => 'Deadline',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control mb-3',
                    'placeholder' => 'Date Created'
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    
                    new Assert\GreaterThanOrEqual([
                        'value' => 'today',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Quiz::class,
        ]);
    }
}
