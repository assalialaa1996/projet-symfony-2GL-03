<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form;

use App\Entity\Post;
use App\Form\Type\DateTimePickerType;
use App\Form\Type\TagsInputType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Définit le formulaire utilisé pour créer et manipuler les articles de blog.
 *
 
 */
class PostType extends AbstractType
{
    private $slugger;

    
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
    

        $builder
            ->add('title', null, [
                'attr' => ['autofocus' => true],
                'label' => 'Titre',
            ])
            ->add('summary', TextareaType::class, [
              
                'label' => 'sommaire'
            ])
            ->add('content', null, [
                'attr' => ['rows' => 20],
                'label' => 'Contenu',
            ])
            ->add('publishedAt', DateTimePickerType::class, [
                'label' => 'publié le'
            ])
            ->add('tags', TagsInputType::class, [
                'label' => 'tags',
                'required' => false,
            ])
        
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var Post */
                $post = $event->getData();
                if (null !== $postTitle = $post->getTitle()) {
                    $post->setSlug($this->slugger->slug($postTitle)->lower());
                }
            })
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
