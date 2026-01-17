<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('content', null, [
                'required' => false,
                'attr' => ['placeholder' => 'Write your article content here...']
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Category',
            ])
            ->add('mediaUpload', FileType::class, [
                'label' => 'Upload Media (Photo or File)',
                'mapped' => true,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-powerpoint',
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            'text/plain',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, WEBP) or document (PDF, DOC, DOCX, XLS, PPT, TXT)',
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
