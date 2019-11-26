<?php

namespace AppBundle\Form\User;

use AppBundle\Entity\Country;
use AppBundle\Entity\Group;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'username',
                TextType::class,
                [
                    'label' => 'user.username',
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'user.email',
                ]
            )
            ->add(
                'plainPassword',
                PasswordType::class,
                [
                    'label' => 'user.password',
                    'required' => $options['is_create'],
                ]
            )
            ->add(
                'group',
                EntityType::class,
                [
                    'label' => 'user.group',
                    'class' => Group::class,
                    'choice_label' =>  function (Group $group) {
                        return 'group.'.$group->getName();
                    },
                    'choice_translation_domain' => 'messages',
                    'attr' => [
                        'v-model' => 'group',
                    ],
                ]
            )
            ->add(
                'enabled',
                CheckboxType::class,
                [
                    'label' => 'user.enabled',
                    'required' => false,
                ]
            )
            ->add(
                'countries',
                EntityType::class,
                [
                    'label' => 'user.countries',
                    'class' => Country::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                    'expanded' => false,
                    'required' => false,
                    'query_builder' => function (EntityRepository $er) {
                        // keep this join to prevent multiple select queries to contribution_country_request table

                        return $er->createQueryBuilder('c')
                            ->select('c, ccr')
                            ->where('c.active = 1')
                            ->leftJoin('c.contributionCountryRequest', 'ccr');
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
            'data_class' => User::class,
            'is_create' => false,
        ]);
    }
}
