<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Tests\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use TestApp\Entity\WithHasSeoMetadata;
use Umanit\SeoBundle\Entity\UrlReference;
use Umanit\SeoBundle\Form\Type\SeoMetadataType;
use Umanit\SeoBundle\Utils\SeoMetadataResolver;

class SeoMetadataTypeTest extends TypeTestCase
{
    public function testAddTwoFieldsAndAListener()
    {
        $form = $this->factory->create(SeoMetadataType::class);

        self::assertTrue($form->has('title'));
        self::assertTrue($form->has('description'));
    }

    public function testVariableInFormView()
    {
        // Form without parent
        $form = $this->factory->create(SeoMetadataType::class);
        $view = $form->createView();

        self::assertArrayHasKey('inject_code_prettify', $view->vars);
        self::assertArrayNotHasKey('url_history', $view->vars);

        // Form with parent
        $entity = new WithHasSeoMetadata();
        $entity->setUrlReference(new UrlReference());
        $form = $this->factory->create(FormType::class, $entity);
        $form->add('seoMetadata', SeoMetadataType::class);
        $view = $form->get('seoMetadata')->createView();

        self::assertArrayHasKey('inject_code_prettify', $view->vars);
        self::assertArrayHasKey('url_history', $view->vars);
    }

    protected function getExtensions()
    {
        $metadataResolver = $this
            ->getMockBuilder(SeoMetadataResolver::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $repository = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $repository->method('findBy')->willReturn(null);
        $entityManager = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $entityManager->method('getRepository')->willReturn($repository);
        $type = new SeoMetadataType($metadataResolver, $entityManager, true);

        return [
            new PreloadedExtension([$type], []),
        ];
    }
}
