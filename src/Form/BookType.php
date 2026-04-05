<?php

namespace App\Form;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Category;
use App\Entity\Language;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'placeholder' => 'Entrez le titre du livre',
                    'class' => 'form-control',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Entrez la description du livre',
                ],
            ])
            ->add('yearPublication', IntegerType::class, [
                'label' => 'Année de publication',
                'attr' => [
                    'placeholder' => 'Entrez l\'année de publication',
                ],
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock disponible',
                'attr' => [
                    'placeholder' => 'Exemplaires disponibles',
                    'min' => 0,
                ],
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Quantité totale',
                'attr' => [
                    'placeholder' => 'Nombre total d\'exemplaires',
                    'min' => 0,
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Sélectionnez une image pour le livre',
                ],
            ])
        
            ->add('author', EntityType::class, [
                'class' => Author::class,
                'choice_label' => 'getAllName',
            ])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'label',
                'multiple' => true,
            ])
            ->add('language', EntityType::class, [
                'class' => Language::class,
                'choice_label' => 'nom',
            ])
            ->add('isbn', TextType::class, [
                'label' => 'ISBN',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Entrez le numéro ISBN du livre',
                ],
                'constraints' => [
                    new Assert\Isbn(
                        message: 'Le numéro ISBN "{{ value }}" n\'est pas valide.'
                    )
                ]
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, $this->EventListener(...))
        ;
    }

    public function EventListener(PreSubmitEvent $event) 
    {
        $form =  $event->getForm();
        $book = $form->getData();

        if($book instanceof Book) {
            $book->setCreatedAt(new \DateTimeImmutable());
        }
  
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
            'csrf_protection' => true,
        ]);
    }
}
