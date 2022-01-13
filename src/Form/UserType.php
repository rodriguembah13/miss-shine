<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fullName',TextType::class,[
            'attr'=>['class' => 'form-control','']
        ])
            ->add('email',TextType::class,[
                'attr'=>['class' => 'form-control','']
            ])
            ->add('username',TextType::class,[
                'attr'=>['class' => 'form-control','']
            ])
            ->add('password',PasswordType::class,[
                'attr'=>['class' => 'form-control','']
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => ['ROLE_USER'=>'ROLE_USER','ROLE_MODERATEUR'=>'ROLE_MODERATEUR','ROLE_ADMIN'=>'ROLE_ADMIN'],
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
