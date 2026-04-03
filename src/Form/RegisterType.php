<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Entrez votre adresse email',
                ], 
                'constraints' => [
                    new Assert\NotBlank(message: 'L\'adresse email ne peut pas être vide.'),
                    new Assert\Email(message: 'L\'adresse email "{{ value }}" n\'est pas valide.'),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => [
                        'placeholder' => 'Entrez votre mot de passe',
                        'type' => 'password',
                    ],
                   
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => [
                        'placeholder' => 'Confirmez votre mot de passe',
                        'type' => 'password',
                    ],
                   
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'mapped' => false,
                 'constraints' => [
                            new Assert\NotBlank(message: 'Le mot de passe ne peut pas être vide.'),
                            new Assert\Regex(
                                pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?#&]{8,}$/',
                                message: 'Le mot de passe doit comporter au moins 8 caractères, incluant une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial.'
                            )
                       
                    ]
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, $this->EventListener(...))
        ;
    }

    public function EventListener(PreSubmitEvent $event) 
    {
        $form =  $event->getForm();
        $user = $form->getData();

        if($user instanceof User) {
            $user->setCreatedAt(new \DateTimeImmutable());
        }
  
    }



    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
