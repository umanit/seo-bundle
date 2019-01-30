<?php

namespace Umanit\SeoBundle\Tests\functional\Controller;

use AppTestBundle\Entity\SeoPage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Umanit\SeoBundle\Tests\WebTestCase;

class UrlHistoryTest extends WebTestCase
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

    public function testUrlHistory()
    {
        // Create a SeoPage with a slug
        $page = (new SeoPage())->setSlug('test-url-history-page');
        $page->getCategory()->setSlug('test-url-history-category');
        $this->save($page);
        // Change the slug
        $page->setSlug('test-url-history-page-new');
        $this->save($page);
        // Try and access the old route
        $this->client->request('GET', '/page/test-url-history-category/test-url-history-page');
        // Assert redirect to the new one
        $this->assertEquals(301, $this->client->getResponse()->getStatusCode());
    }

    public function testUrlHistoryOfSubAttribute()
    {
        // Create a SeoPage with a slug
        $page = (new SeoPage())->setSlug('test-url-History-sub-attr-page');
        $this->save($page);
        // Change the slug
        $page->setSlug('test-url-History-sub-attr-page-new');
        $page->getCategory()->setSlug('test-url-History-sub-attr-category');
        $this->save($page);
        // Change the slug of the category
        $page->getCategory()->setSlug('test-url-History-sub-attr-category-new');
        $this->save($page);
        // Try and access the old route
        $this->client->request('GET', '/page/test-url-History-sub-attr-category/test-url-History-sub-attr-page');
        // Assert redirect to the new one
        $this->assertEquals(301, $this->client->getResponse()->getStatusCode());
    }

    private function save($page)
    {
        $this->em->persist($page);
        $this->em->flush();
        $this->em->refresh($page);
    }
}
