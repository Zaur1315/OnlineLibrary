<?php

namespace App\Form;

use App\Entity\Book;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Название Книги',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Введите название книги',
                ]
            ])
            ->add('author', TextType::class, [
                'label' => 'Автор',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Введите Имя Автора',
                ]
            ])
            ->add('genre', TextType::class, [
                'label' => 'Жанр',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Введите Жанр',
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание Книги',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 6,
                    'placeholder' => 'Введите Описание Книги',
                ]
            ])
            ->add('publicationDate', DateType::class, [
                'label' => 'Дата Публикации',
                'required' => true,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('pageCount', IntegerType::class, [
                'label' => 'Количество Страниц',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                ]
            ])
            ->add('coverImage', FileType::class, [
                'label' => 'Обложка книги (изображение в формате JPG или PNG)',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control mt-2',
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Пожалуйста, загрузите изображение в формате JPG или PNG',
                    ]),
                ],
            ])
            ->add('save', SubmitType::class, [
            'label' => 'Сохранить',
            'attr' => [
                'class' => 'btn btn-primary mt-3',
                ]
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
