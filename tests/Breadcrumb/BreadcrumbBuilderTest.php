<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Tests\Breadcrumb;

use PHPUnit\Framework\TestCase;
use TestApp\Entity\Breadcrumbable\WithHandlerEntity;
use TestApp\Entity\Breadcrumbable\WithHandlerMultipleLevelsEntity;
use TestApp\Entity\Breadcrumbable\WithoutHandlerEntity;
use TestApp\Seo\Breadcrumbable\BreadcrumbableWithHandlerHandler;
use TestApp\Seo\Breadcrumbable\BreadcrumbableWithHandlerMultipleLevelsHandler;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Umanit\SeoBundle\Breadcrumb\BreadcrumbBuilder;
use Umanit\SeoBundle\Handler\Breadcrumbable\Breadcrumbable;
use Umanit\SeoBundle\Model\Breadcrumb;

class BreadcrumbBuilderTest extends TestCase
{
    public function testWithoutHandlerMustThrowLogicException()
    {
        $builder = $this->getBuilder(new Breadcrumbable([]));
        $entity = new WithoutHandlerEntity();

        $this->expectException('LogicException');

        $builder->buildBreadcrumb($entity);
    }

    public function testMicrodata()
    {
        $builder = $this->getBuilder(new Breadcrumbable([new BreadcrumbableWithHandlerHandler()]));
        $entity = new WithHandlerEntity();

        $result = $builder->buildBreadcrumb($entity);

        self::assertStringContainsString('<ol itemscope itemtype="https://schema.org/BreadcrumbList">', $result);
        self::assertStringContainsString('<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">', $result);
        self::assertStringContainsString('<span itemprop="name">Hello World!</span>', $result);
        self::assertStringContainsString('<meta itemprop="position" content="1" />', $result);
    }

    public function testJsonLd()
    {
        $builder = $this->getBuilder(new Breadcrumbable([new BreadcrumbableWithHandlerHandler()]));
        $entity = new WithHandlerEntity();

        $result = $builder->buildBreadcrumb($entity, Breadcrumb::FORMAT_JSON_LD);

        self::assertStringContainsString('"@type": "BreadcrumbList"', $result);
        self::assertStringContainsString('"@type": "ListItem"', $result);
        self::assertStringContainsString('"name": "Hello World!"', $result);
        self::assertStringContainsString('"position": 1', $result);
    }

    public function testRdfa()
    {
        $builder = $this->getBuilder(new Breadcrumbable([new BreadcrumbableWithHandlerHandler()]));
        $entity = new WithHandlerEntity();

        $result = $builder->buildBreadcrumb($entity, Breadcrumb::FORMAT_RDFA);

        self::assertStringContainsString('<ol vocab="https://schema.org/" typeof="BreadcrumbList">', $result);
        self::assertStringContainsString('<li property="itemListElement" typeof="ListItem">', $result);
        self::assertStringContainsString('<span property="name">Hello World!</span>', $result);
        self::assertStringContainsString('<meta property="position" content="1" />', $result);
    }

    public function testWithMultipleLevels()
    {
        $builder = $this->getBuilder(new Breadcrumbable([new BreadcrumbableWithHandlerMultipleLevelsHandler()]));
        $entity = new WithHandlerMultipleLevelsEntity();

        $result = json_decode(str_replace([
            '<script type="application/ld+json">', '</script>',
        ],
            '',
            $builder->buildBreadcrumb($entity, Breadcrumb::FORMAT_JSON_LD)
        ), true);

        self::assertCount(3, $result['itemListElement']);
        self::assertArrayHasKey('item', $result['itemListElement'][1]);
        self::assertArrayNotHasKey('item', $result['itemListElement'][2]);
    }

    private function getBuilder(Breadcrumbable $breadcrumbable): BreadcrumbBuilder
    {
        $loader = new FilesystemLoader();
        $loader->setPaths(__DIR__.'/../../src/Resources/views/', 'UmanitSeo');
        $twig = new Environment($loader);

        $canonical = $this
            ->getMockBuilder('Umanit\SeoBundle\Routing\Canonical')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $canonical->method('url')->willReturn('/');

        return new BreadcrumbBuilder($twig, $canonical, $breadcrumbable, [
            'breadcrumb_json_ld'   => '@UmanitSeo/breadcrumb/breadcrumb.json-ld.html.twig',
            'breadcrumb_microdata' => '@UmanitSeo/breadcrumb/breadcrumb.microdata.html.twig',
            'breadcrumb_rdfa'      => '@UmanitSeo/breadcrumb/breadcrumb.rdfa.html.twig',
        ]);
    }
}
