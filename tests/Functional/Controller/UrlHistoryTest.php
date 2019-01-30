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
        $page = (new SeoPage())->setSlug('former-slug');
        $this->save($page);
        // Change the slug
        $page->setSlug('new-slug');
        $this->save($page);
        // Try and access the old route
        $this->client->request('GET', '/page/my-category/former-slug');
        // Assert redirect to the new one
        $this->assertEquals(301, $this->client->getResponse()->getStatusCode());
    }

    public function testUrlHistoryOfSubAttribute()
    {
        // Create a SeoPage with a slug
        $page = (new SeoPage())->setSlug('former-slug');
        $this->save($page);
        // Change the slug
        $page->setSlug('new-slug');
        $page->getCategory()->setSlug('old-category-slug');
        $this->save($page);
        // Change the slug of the category
        $page->getCategory()->setSlug('new-category-slug');
        $this->save($page);
        // Try and access the old route
        $this->client->request('GET', '/page/old-category-slug/new-slug');
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
