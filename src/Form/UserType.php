<?php

namespace App\Form;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use Elao\Enum\Bridge\Symfony\Form\Type\EnumType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'required' => true
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
                'required' => true,
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat password'],
                'required' => !$options['is_edit_action'],
                'mapped' => false,
            ])
            ->add('role', EnumType::class, [
                'class' => UserRole::class,
                'label' => 'User role',
                'multiple' => false,
            ])
        ;
        if ($options['is_edit_action']) {
            $builder->add('enabled', ChoiceType::class, [
                'label' => 'Enabled',
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit_action' => false,
        ]);
    }
}
