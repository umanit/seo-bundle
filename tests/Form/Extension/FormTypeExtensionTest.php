<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Tests\Form\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use TestApp\Entity\WithHasSeoMetadata;
use TestApp\Entity\WithoutHasSeoMetadata;
use Umanit\SeoBundle\Form\Extension\FormTypeExtension;
use Umanit\SeoBundle\Form\Type\SeoMetadataType;
use Umanit\SeoBundle\Utils\SeoMetadataResolver;

class FormTypeExtensionTest extends TypeTestCase
{
    public function testAddSeoMetadataOnEntityWithHasSeoMetadataInterface(): void
    {
        $entity = new WithHasSeoMetadata();
        $form = $this->factory->create(FormType::class, $entity);

        self::assertTrue($form->has('seoMetadata'));
    }

    public function testNotAddSeoMetadataOnEntityWithoutHasSeoMetadataInterface(): void
    {
        $entity = new WithoutHasSeoMetadata();
        $form = $this->factory->create(FormType::class, $entity);

        self::assertFalse($form->has('seoMetadata'));
    }

    protected function getExtensions()
    {
        $metadataResolver = $this
            ->getMockBuilder(SeoMetadataResolver::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $entityManager = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $type = new SeoMetadataType($metadataResolver, $entityManager, true);

        return [
            new PreloadedExtension([$type], []),
        ];
    }

    protected function getTypeExtensions()
    {
        return [
            new FormTypeExtension(SeoMetadataType::class),
        ];
    }
}
