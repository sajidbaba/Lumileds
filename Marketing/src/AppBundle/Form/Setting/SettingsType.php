<?php

namespace AppBundle\Form\Setting;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class SettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('settings', CollectionType::class, [
            'entry_type' => SettingType::class,
            'entry_options' => ['label' => false],
            'label' => false
        ])->add('submit', SubmitType::class, [
            'label' => 'form.save',
            'attr' => [
                'class' => 'btn btn-primary pull-left',
            ],
        ]);
    }
}
