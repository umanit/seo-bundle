<?php

namespace Umanit\SeoBundle\Tests\functional\TwigExtension;

use AppTestBundle\Entity\SeoPage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Umanit\SeoBundle\Tests\WebTestCase;

/**
 * Class SeoMetadataTest
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class SeoMetadataTest extends WebTestCase
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

    public function testDefaultSeoMetadata()
    {
        $page = (new SeoPage())->setSlug('test-seo-metadata');
        $this->em->persist($page);
        $this->em->flush();
        $this->em->refresh($page);
        $this->em->clear();

        $this->client->request('GET', '/page/test-seo-metadata');

        $content = <<<HTML
<meta name="title" content="Umanit Seo - Customize this default title to your needs." />
<meta name="description" content="Umanit Seo - Customize this default description to your needs." />\n
HTML;

        $this->assertContains($content, $this->client->getResponse()->getContent());
    }

    public function testDeductedSeoMetadata()
    {
        $page = (new SeoPage())
            ->setSlug('test-seo-metadata-deducted')
            ->setName('seo-name')
            ->setIntroduction('seo-introduction')
        ;
        $this->em->persist($page);
        $this->em->flush();
        $this->em->refresh($page);
        $this->em->clear();

        $this->client->request('GET', '/page/test-seo-metadata-deducted');

        $content = <<<HTML
<meta name="title" content="seo-name" />
<meta name="description" content="seo-introduction" />\n
HTML;

        $this->assertContains($content, $this->client->getResponse()->getContent());
    }
}
