<?php

namespace App\Form;

use App\Entity\Edition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle')
            ->add('status',ChoiceType::class,[
                'choices'=>['En attente' => 'En attente', 'Publie' => 'Publie', 'Archive' => 'Archive']
            ])
/*            ->add('createdAt')
            ->add('updatedAt')*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Edition::class,
        ]);
    }
}
