<?php

namespace AppBundle\Form;

//


use AppBundle\Entity\Tag;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('titre', TextType::class)
                ->add('contenu', TextareaType::class)
                ->add('date', DateType::class, array('widget' => 'single_text', 'html5' => false, 'format' => 'yyyy-MM-dd'))
                ->add('publication', null, ['required' => false, 'label' => 'Publié ?'])
                ->add('image', ImageType::class, ['required' => false]) // on le rajoute apres avoir fait la creation par le terminal de ImageType
                ->add('tags', EntityType::class, [
                    'required' => false,
                    'class' => Tag::class,
                    'choice_label' => 'titre',
                    'expanded' => true,
                    'multiple' => true,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                                ->orderBy('u.titre', 'ASC');
                    },
                ])
                ->add('submit', SubmitType::class, ['label' => 'Envoyer'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Article'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'appbundle_article';
    }

}
