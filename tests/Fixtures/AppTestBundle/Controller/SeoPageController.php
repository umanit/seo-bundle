<?php

namespace AppTestBundle\Controller;

use AppTestBundle\Entity\SeoPage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeoPageController extends AbstractController
{
    /**
     * @Route("/page/{slug}", name="app_test_page_show")
     */
    public function show(string $slug)
    {
        $page = $this->getDoctrine()->getRepository(SeoPage::class)->findOneBy(['slug' => $slug]);
        if (null === $page) {
            throw $this->createNotFoundException();
        }

        return new Response('Page : '.$page->getSlug());
    }
}
