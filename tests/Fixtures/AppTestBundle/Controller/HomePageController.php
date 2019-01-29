<?php

namespace AppTestBundle\Controller;

use AppTestBundle\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomePageController extends AbstractController
{
    /**
     * @Route("//", name="app_test_home_page")
     *
     * @return Response
     */
    public function show(string $slug)
    {
        return $this->render('@AppTestBundle/home/show.html.twig');
    }
}
