<?php
// src/Form/ArticleType.php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Categories;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType; // Import SubmitType

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('body')
            ->add('category', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'name',
                'placeholder' => 'Choose a category',
                'required' => true,
            ])
            ->add('image', FileType::class, [
                'label' => 'Image (JPEG, PNG, or GIF file)',
                'mapped' => false, // Tell Symfony not to map this field to any property on the entity
                'required' => false, // Image upload is optional
            ])
            ->add('authorId')
            ->add('submit', SubmitType::class, [ // Use SubmitType::class for the submit button type
                'label' => 'Submit',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
