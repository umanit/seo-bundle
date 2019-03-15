<?php

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
use Symfony\Contracts\Translation\TranslatorInterface;
use Umanit\SeoBundle\Doctrine\Model\UrlHistorizedInterface;
use Umanit\SeoBundle\Entity\SeoMetadata;
use Umanit\SeoBundle\Entity\UrlHistory;
use Umanit\SeoBundle\Utils\EntityParser\Excerpt;
use Umanit\SeoBundle\Utils\EntityParser\Title;
use Umanit\SeoBundle\Utils\SeoMetadataResolver;

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

    /** @var EntityManagerInterface */
    private $em;
    /**
     * @var SeoMetadataResolver
     */
    private $seoMetadataResolver;

    /**
     * SeoMetadataType constructor.
     *
     * @param SeoMetadataResolver $seoMetadataResolver
     * @param TranslatorInterface|\Symfony\Component\Translation\TranslatorInterface $translator
     * @param EntityManagerInterface $em
     * @param array $metadataConfig
     */
    public function __construct(
        SeoMetadataResolver $seoMetadataResolver,
        $translator,
        EntityManagerInterface $em,
        array $metadataConfig
    ) {
        $this->seoMetadataResolver = $seoMetadataResolver;
        $this->metadataConfig = $metadataConfig;
        $this->translator     = $translator;
        $this->em             = $em;
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

    /**
     * Finishes the form view.
     *
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $entity = $form->getParent()->getData();
        if ($entity instanceof UrlHistorizedInterface && $entity->getUrlRef()) {
            $view->vars['url_history'] = $this->em->getRepository(UrlHistory::class)->findBy(['seoUuid' => $entity->getUrlRef()->getSeoUuid()], ['id' => 'ASC']);
        }
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => SeoMetadata::class,
            'translation_domain' => 'UmanitSeoBundle',
        ]);
    }
}
