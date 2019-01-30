<?php


namespace Umanit\SeoBundle\Tests\functional\TwigExtension;

use AppTestBundle\Entity\SeoPage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Umanit\SeoBundle\Tests\WebTestCase;
use Umanit\SeoBundle\Doctrine\Annotation\Breadcrumb;

/**
 * Class BreadcrumbTest
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class BreadcrumbTest extends WebTestCase
{
    /** @var Client */
    private $client;
    /** @var EntityManagerInterface */
    private $em;

    public function setUp()
    {
        $kernel       = self::bootKernel();
        $this->client = $kernel->getContainer()->get('test.client');
        $this->em     =
            $kernel
                ->getContainer()
                ->get('doctrine')
                ->getManager()
        ;
    }

    /**
     * Tests @Breadcrumb() annotation microdata (default).
     */
    public function testBreadcrumbMicrodata()
    {
        $page = (new SeoPage())->setSlug('test-breadcrumb-microdata');
        $category = $page->getCategory()->setSlug('category-microdata');
        $this->save($page);


        $this->client->request('GET', '/page/category-microdata/test-breadcrumb-microdata');

        $expected = <<<HTML
<ol itemscope itemtype="http://schema.org/BreadcrumbList">
    <li itemprop="itemListElement" itemscope
        itemtype="http://schema.org/ListItem">
        <a itemprop="item" href="http://localhost/">
            <span itemprop="name">Home</span></a>
        <meta itemprop="position" content="1"/>
    </li>
    <li itemprop="itemListElement" itemscope
        itemtype="http://schema.org/ListItem">
        <a itemprop="item" href="http://localhost/category/category-microdata">
            <span itemprop="name">category-microdata</span></a>
        <meta itemprop="position" content="2"/>
    </li>
    <li itemprop="itemListElement" itemscope
        itemtype="http://schema.org/ListItem">
        <a itemprop="item" href="http://localhost/page/category-microdata/test-breadcrumb-microdata">
            <span itemprop="name"></span></a>
        <meta itemprop="position" content="3"/>
    </li>
</ol>
HTML;
        $this->assertContains($expected, $this->client->getResponse()->getContent());
    }

    /**
     * Tests @Breadcrumb() annotation json-ld.
     */
    public function testBreadcrumbJsonLd()
    {
        $page = (new SeoPage())->setSlug('test-breadcrumb-json-ld');
        $category = $page->getCategory()->setSlug('category-json-ld');
        $this->save($page);


        $this->client->request('GET', '/page/category-json-ld/test-breadcrumb-json-ld');

        $expected = <<<HTML
<script type="application/ld+json">{"@context":"http:\/\/schema.org","@type":"BreadcrumbList","itemListElement":{"@type":"ListItem","position":3,"item":{"@id":"http:\/\/localhost\/page\/category-json-ld\/test-breadcrumb-json-ld","name":null}}}</script>
HTML;
        $this->assertContains($expected, $this->client->getResponse()->getContent());
    }

    /**
     * Tests @Breadcrumb() annotation RDFa.
     */
    public function testBreadcrumbRdfa()
    {
        $page = (new SeoPage())->setSlug('test-breadcrumb-rdfa');
        $category = $page->getCategory()->setSlug('category-rdfa');
        $this->save($page);


        $this->client->request('GET', '/page/category-rdfa/test-breadcrumb-rdfa');

        $expected = <<<HTML
<ol vocab="http://schema.org/" typeof="BreadcrumbList">
    <li property="itemListElement" typeof="ListItem">
        <a property="item" typeof="WebPage" href="http://localhost/">
            <span property="name">Home</span></a>
        <meta property="position" content="1">
    </li>
    <li property="itemListElement" typeof="ListItem">
        <a property="item" typeof="WebPage" href="http://localhost/category/category-rdfa">
            <span property="name">category-rdfa</span></a>
        <meta property="position" content="2">
    </li>
    <li property="itemListElement" typeof="ListItem">
        <a property="item" typeof="WebPage" href="http://localhost/page/category-rdfa/test-breadcrumb-rdfa">
            <span property="name"></span></a>
        <meta property="position" content="3">
    </li>
</ol>
HTML;
        $this->assertContains($expected, $this->client->getResponse()->getContent());
    }

    private function save($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
        $this->em->refresh($entity);
        $this->em->clear();
    }
}
