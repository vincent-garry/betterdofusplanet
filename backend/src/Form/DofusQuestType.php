<?php

namespace App\Form;

use App\Entity\Dofus;
use App\Entity\DofusQuest;
use App\Entity\Quest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DofusQuestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('questOrder')
            ->add('dofus', EntityType::class, [
                'class' => Dofus::class,
                'choice_label' => 'id',
            ])
            ->add('quest', EntityType::class, [
                'class' => Quest::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DofusQuest::class,
        ]);
    }
}
