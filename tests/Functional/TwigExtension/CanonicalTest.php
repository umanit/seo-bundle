<?php

namespace Umanit\SeoBundle\Tests\functional\TwigExtension;

use AppTestBundle\Entity\SeoPage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Umanit\SeoBundle\Tests\WebTestCase;

/**
 * Class CanonicalTest
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class CanonicalTest extends WebTestCase
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

    public function tearDown()
    {
        $this->client = null;
    }

    public function testCanonicalWithoutParameters()
    {
        $page = (new SeoPage())->setSlug('test-canonical');
        $category = $page->getCategory()->setSlug('category-canonical');
        $this->save($page);


        $this->client->request('GET', '/page/category-canonical/test-canonical');

        $expected = <<<HTML
<link rel="canonical" href="http://localhost/page/category-canonical/test-canonical"/>
HTML;

        $this->assertContains($expected, $this->client->getResponse()->getContent());
    }

    public function testCanonicalWithParameters()
    {
        $page = (new SeoPage())->setSlug('test-canonical-params');
        $category = $page->getCategory()->setSlug('category-canonical-w-params');
        $this->save($page);

        $this->em->clear();
        $this->client->request('GET', '/test/canonical/with/parameters/test-canonical-params');
        $expected = <<<HTML
<link rel="canonical" href="http://localhost/page/category-canonical-w-params/test-canonical-params"/>
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
