<?php

namespace AppTestBundle\Controller;

use AppTestBundle\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @Route("/category/{slug}", name="app_test_category_show")
     * @param string $slug
     *
     * @return Response
     */
    public function show(string $slug)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['slug' => $slug]);
        if (null === $category) {
            throw $this->createNotFoundException();
        }

        return $this->render('@AppTestBundle/category/show.html.twig', [
            'category' => $category,
        ]);
    }
}
