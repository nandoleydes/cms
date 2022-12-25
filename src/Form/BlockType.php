<?php

namespace App\Form;

use App\Entity\Block;
use App\Form\SectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class BlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('active', CheckboxType::class, [
                'label'    => 'Active',
                'required' => false,
                'attr' => ['value' => true],
            ])
            ->add('title')
            ->add('position')
            ->add('parentId', ChoiceType::class, [
                'choices' => $options['choices']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Block::class,
            'choices' => null
        ]);
    }
}
