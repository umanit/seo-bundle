<?php


namespace Umanit\SeoBundle\Tests\functional\TwigExtension;

use AppTestBundle\Entity\Category;
use AppTestBundle\Entity\SeoPage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Umanit\SeoBundle\Tests\WebTestCase;
use Umanit\SeoBundle\Doctrine\Annotation\SchemaOrgBuilder;

/**
 * Class CanonicalTest
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class SchemaOrgTest extends WebTestCase
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
     * Tests @SchemaOrgBuilder() annotation with service.
     */
    public function testSchemaOrgService()
    {
        $page = (new SeoPage())->setSlug('test-schema-org-service');
        $category = $page->getCategory()->setSlug('category-schema-org-service');
        $this->save($page);


        $this->client->request('GET', '/page/category-schema-org-service/test-schema-org-service');

        $expected = <<<HTML
<script type="application/ld+json">{"@context":"https:\/\/schema.org","@type":"LocalBusiness","name":"Test","email":"test@umanit.fr","contactPoint":{"@type":"ContactPoint","areaServed":"Worldwide"}}</script>\n
HTML;

        $this->assertContains($expected, $this->client->getResponse()->getContent());
    }
    /**
     * Tests @SchemaOrgBuilder() annotation with method.
     */
    public function testSchemaOrgMethod()
    {
        $cat = (new Category())->setSlug('test-schema-org');
        $this->save($cat);

        $this->client->request('GET', '/category/test-schema-org');

        $expected = <<<HTML
<script type="application/ld+json">{"@context":"https:\/\/schema.org","@type":"MensClothingStore","name":"Test","email":"test@umanit.fr","contactPoint":{"@type":"ContactPoint","areaServed":"Worldwide"}}</script>\n
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
