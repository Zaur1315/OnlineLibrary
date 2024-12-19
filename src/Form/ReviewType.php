<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', IntegerType::class, [
            'label' => 'Оценка (от 1 до 5)',
            'attr' => [
                'min' => 1,
                'max' => 5,
                'step' => 1,
                'class' => 'form-control',
            ],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Ваш отзыв',
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Оставить отзыв',
                'attr' => [
                    'class' => 'btn btn-primary mt-3'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }

}
