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
        $page = (new SeoPage())->setSlug('test-schema-org');
        $this->em->persist($page);
        $this->em->flush();
        $this->em->refresh($page);
        $this->em->clear();

        $this->client->request('GET', '/page/my-category/test-schema-org');

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
        $this->em->persist($cat);
        $this->em->flush();
        $this->em->refresh($cat);
        $this->em->clear();

        $this->client->request('GET', '/category/test-schema-org');

        $expected = <<<HTML
<script type="application/ld+json">{"@context":"https:\/\/schema.org","@type":"MensClothingStore","name":"Test","email":"test@umanit.fr","contactPoint":{"@type":"ContactPoint","areaServed":"Worldwide"}}</script>\n
HTML;

        $this->assertContains($expected, $this->client->getResponse()->getContent());
    }
}
