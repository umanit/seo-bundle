<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Umanit\SeoBundle\Entity\SeoMetadata;
use Umanit\SeoBundle\Entity\UrlHistory;
use Umanit\SeoBundle\Model\HistorizableUrlModelInterface;
use Umanit\SeoBundle\Utils\SeoMetadataResolver;

class SeoMetadataType extends AbstractType
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var SeoMetadataResolver */
    private $seoMetadataResolver;

    /** @var bool */
    private $injectCodePrettify;

    public function __construct(
        SeoMetadataResolver $seoMetadataResolver,
        EntityManagerInterface $em,
        bool $injectCodePrettify
    ) {
        $this->seoMetadataResolver = $seoMetadataResolver;
        $this->em = $em;
        $this->injectCodePrettify = $injectCodePrettify;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'translation_domain' => $options['translation_domain'],
                'label'              => 'seo.title',
                'required'           => false,
            ])
            ->add('description', TextareaType::class, [
                'translation_domain' => $options['translation_domain'],
                'label'              => 'seo.description',
                'required'           => false,
            ])
        ;

        // Add placeholders to seo fields.
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            if (null === $event->getData() || null === $event->getForm()->getParent()) {
                return;
            }

            /** @var SeoMetadata $seoMetadata */
            $seoMetadata = $event->getData();
            $seoForm = $event->getForm();
            $parentModelData = $event->getForm()->getParent()->getData();

            // Title
            if (null === $seoMetadata->getTitle()) {
                $title = $this->seoMetadataResolver->metaTitle($parentModelData, false, false);

                $this->setSubFormOption($seoForm, 'title', 'attr', [
                    'placeholder' => html_entity_decode($title),
                ]);
            }

            // Description
            if (null === $seoMetadata->getDescription()) {
                $description = $this->seoMetadataResolver->metaDescription($parentModelData);

                $this->setSubFormOption($seoForm, 'description', 'attr', [
                    'placeholder' => html_entity_decode($description),
                ]);
            }
        });
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['inject_code_prettify'] = $this->injectCodePrettify;

        $parent = $form->getParent();

        if (null === $parent) {
            return;
        }

        $entity = $parent->getData();

        if ($entity instanceof HistorizableUrlModelInterface && $entity->getUrlReference()) {
            $view->vars['url_history'] = $this->em
                ->getRepository(UrlHistory::class)
                ->findBy(['seoUuid' => $entity->getUrlReference()->getSeoUuid()], ['id' => 'ASC'])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => SeoMetadata::class,
            'translation_domain' => 'UmanitSeoBundle',
        ]);
    }

    protected function setSubFormOption(
        FormInterface $parentForm,
        string $childName,
        string $optionName,
        $optionValue
    ): void {
        $options = $parentForm->get($childName)->getConfig()->getOptions();
        $options[$optionName] = array_merge($options[$optionName], $optionValue);

        $parentForm->add(
            $childName,
            \get_class($parentForm->get($childName)->getConfig()->getType()->getInnerType()),
            $options
        );
    }
}
