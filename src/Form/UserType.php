<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Regex;


class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $roleChoices = [
            'ROLE_STUDENT' => 'ROLE_STUDENT',
            'ROLE_TEACHER' => 'ROLE_TEACHER',
            'ROLE_ADMIN' => 'ROLE_ADMIN', // Always include Admin choice
        ];;

        $builder
            ->add('firstName', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'First name cannot be blank']),
                    new Regex([
                        'pattern' => '/^[A-Za-z _]+$/',
                        'message' => 'First name must contain only letters, spaces, or underscores'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Your first name must be at least {{ limit }} characters long'
                    ]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Last name cannot be blank']),
                    new Regex([
                        'pattern' => '/^[A-Z][a-zA-Z]*$/',
                        'message' => 'Last name must start with an uppercase letter and contain only letters'
                    ]),
                ],
            ])
            ->add('username', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Username cannot be blank']),
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Email cannot be blank']),
                    new Email(['message' => 'Invalid email format']),
                ],
            ]);
        if ($options['is_new_user'])  {
            $builder
                ->add('password', PasswordType::class, [
                    'mapped' => !$options['is_new_user'], // This ensures that the password field is not mapped to the entity on edit
                    'attr' => ['class' => 'password-field'], // Add a class for JavaScript control
                    'constraints' => [
                        new NotBlank(['message' => 'Password cannot be blank']),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Password must be at least 8 characters long'
                        ]),
                        new Regex([
                            'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
                            'message' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one symbol'
                        ]),
                    ],
                ]);
        }
        $builder
        ->add('role', ChoiceType::class, [
            'label' => 'Role',
            'choices' => ($options['page'] === 'profile') ? array_diff_key($roleChoices, ['ROLE_ADMIN' => 'ROLE_ADMIN']) : $roleChoices,
            'placeholder' => 'Choose a role',
            'required' => true,
            'constraints' => [
                new NotBlank(['message' => 'Role cannot be blank']),
                new Choice([
                    'choices' => array_keys($roleChoices),
                    'message' => 'Invalid role'
                ]),
            ],
        ])
        
            ->add('picFile', FileType::class, [
                'label' => 'Profile Picture',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'image/*',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file',
                    ])
                ],
            ])
            ->add('levels', ChoiceType::class, [
                'label' => 'Levels',
                'choices' => $options['levels_choices'],
                'placeholder' => 'Choose a level',
                'required' => true,
            ])
            
            
            ->add('cin', IntegerType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'CIN cannot be blank']),
                    new Length([
                        'min' => 8,
                        'max' => 8,
                        'exactMessage' => 'CIN must contain exactly {{ limit }} numbers',
                    ]),
                ],
            ])
            ->add('phoneNumber', IntegerType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Phone number cannot be blank']),
                    new Length([
                        'min' => 8,
                        'max' => 8,
                        'exactMessage' => 'Phone number must contain exactly {{ limit }} numbers',
                    ]),
                ],
            ]);
            if ($options['page'] !== 'profile') {
                $builder
            ->add('infractionCount', IntegerType::class)
            ->add('banned', CheckboxType::class) // Use CheckboxType for boolean fields
            ->add('banDate', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('banReason', TextType::class);
    }
}

public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefaults([
        'data_class' => User::class,
        'levels_choices' => [
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
        ],
        'is_new_user' => false,
        'page' => null,
    ]);

    // Define the 'page' option as allowed
    $resolver->setAllowedTypes('page', ['null', 'string']);
}


}
