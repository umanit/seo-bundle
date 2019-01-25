<?php

namespace Umanit\SeoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Umanit\SeoBundle\Entity\SeoMetadata;
use Umanit\SeoBundle\Utils\EntityParser\Excerpt;
use Umanit\SeoBundle\Utils\EntityParser\Title;

/**
 * Class SeoMetadataType
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class SeoMetadataType extends AbstractType
{
    /** @var Excerpt */
    private $excerpt;

    /** @var array */
    private $metadataConfig;

    /**  @var TranslatorInterface */
    private $translator;

    /** @var Title */
    private $title;

    /**
     * SeoMetadataType constructor.
     *
     * @param Excerpt             $excerpt
     * @param Title               $title
     * @param TranslatorInterface $translator
     * @param array               $metadataConfig
     */
    public function __construct(
        Excerpt $excerpt,
        Title $title,
        TranslatorInterface $translator,
        array $metadataConfig
    ) {
        $this->excerpt        = $excerpt;
        $this->title          = $title;
        $this->metadataConfig = $metadataConfig;
        $this->translator     = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            if (null === $event->getData() || null === $event->getForm()->getParent()) {
                return;
            }
            /** @var SeoMetadata $seoMetadata */
            $seoMetadata     = $event->getData();
            $seoForm         = $event->getForm();
            $parentModelData = $event->getForm()->getParent()->getData();
            $locale          = method_exists($parentModelData, 'getLocale') ? $parentModelData->getLocale() : null;
            // Title
            if (null === $seoMetadata->getTitle()) {
                $title = $this->title->fromEntity($parentModelData) ?? $this->translator->trans(
                        $this->metadataConfig['default_title'],
                        [],
                        $options['translation_domain'],
                        $locale
                    );

                $this->setSubFormOption($seoForm, 'title', 'attr', [
                    'placeholder' => html_entity_decode($title),
                ]);
            }
            // Description
            if (null === $seoMetadata->getDescription()) {
                $description = $this->excerpt->fromEntity($parentModelData) ?? $this->translator->trans(
                        $this->metadataConfig['default_description'],
                        [],
                        $options['translation_domain'],
                        $locale
                    );

                $this->setSubFormOption($seoForm, 'description', 'attr', [
                    'placeholder' => html_entity_decode($description),
                ]);
            }
        });
    }

    /**
     * Set a form options.
     *
     * @param FormInterface $parentForm
     * @param string        $childName
     * @param string        $optionName
     * @param mixed         $optionValue
     */
    protected function setSubFormOption(FormInterface $parentForm, string $childName, string $optionName, $optionValue): void
    {
        $options = $parentForm->get($childName)->getConfig()->getOptions();

        $options[$optionName] = $optionValue;

        $parentForm->add(
            $childName,
            get_class($parentForm->get($childName)->getConfig()->getType()->getInnerType()),
            $options
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => SeoMetadata::class,
            'translation_domain' => 'UmanitSeoBundle',
        ]);
    }
}
