<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
//
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class NoteType extends AbstractType {
//    /**
//     * {@inheritdoc}
//     */
//    public function buildForm(FormBuilderInterface $builder, array $options) {
//        $builder->add('valeur')
//                ->add('ajouter', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class)
//        ;
//    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('valeur', ChoiceType::class, array(
                    'choices' => array(
                        'Rubbish' => '1',
                        'Bad' => '2',
                        'Average' => '3',
                        'Good' => '4',
                        'Very Good' => '5'
                    ),))
                ->add('ajouter', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Note'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'appbundle_note';
    }

}
