<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Umanit\SeoBundle\Model\HasSeoMetadataInterface;

class FormTypeExtension extends AbstractTypeExtension
{
    /** @var string */
    private $formTypeFqcn;

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function __construct(string $formTypeFqcn)
    {
        $this->formTypeFqcn = $formTypeFqcn;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['data'] ?? null;

        if (!$data instanceof HasSeoMetadataInterface) {
            return;
        }

        $builder->add('seoMetadata', $this->formTypeFqcn);
    }
}
