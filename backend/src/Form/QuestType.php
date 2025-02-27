<?php

namespace App\Form;

use App\Entity\Quest;
use App\Entity\QuestStep;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('level')
            ->add('steps', EntityType::class, [
                'class' => QuestStep::class,
                'choice_label' => 'id',
            ])
            ->add('prerequisite', EntityType::class, [
                'class' => Quest::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('quests', EntityType::class, [
                'class' => Quest::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quest::class,
        ]);
    }
}
