<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Form\Type;

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
use Umanit\SeoBundle\Model\HistorizableUrlModelInterface;
use Umanit\SeoBundle\Repository\UrlHistoryRepositoryInterface;
use Umanit\SeoBundle\Utils\SeoMetadataResolver;

class SeoMetadataType extends AbstractType
{
    public function __construct(
        private readonly SeoMetadataResolver $seoMetadataResolver,
        private readonly UrlHistoryRepositoryInterface $urlHistoryRepository,
        private readonly bool $injectCodePrettify,
    ) {
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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            if (null === $event->getData() || !$event->getForm()->getParent() instanceof FormInterface) {
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

        if (!$parent instanceof FormInterface) {
            return;
        }

        $entity = $parent->getData();

        if ($entity instanceof HistorizableUrlModelInterface && $entity->getUrlReference()) {
            $view->vars['url_history'] = $this
                ->urlHistoryRepository
                ->findBySeoUuid($entity->getUrlReference()->getSeoUuid())
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
            $parentForm->get($childName)->getConfig()->getType()->getInnerType()::class,
            $options
        );
    }
}
