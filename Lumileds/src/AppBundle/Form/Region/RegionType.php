<?php

namespace AppBundle\Form\Region;

use AppBundle\Entity\Country;
use AppBundle\Entity\Region;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Region $region */
        $region = $options['data'];

        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'region.name',
                ]
            )
            ->add(
                'countries',
                EntityType::class,
                [
                    'label' => 'region.countries',
                    'class' => Country::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                    'expanded' => false,
                    'choice_attr' => function(Country $country) use ($region) {
                        $isCountryAllowed = $country->getRegion() === null || $country->getRegion()->getId() === $region->getId();
                        $isCountryActive = $country->isActive();

                        $disabled = !($isCountryAllowed && $isCountryActive);

                        return [
                            'disabled' => $disabled,
                        ];
                    },
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'form.save',
                    'attr' => [
                        'class' => 'btn btn-primary pull-left',
                    ],
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Region::class,
        ]);
    }
}
