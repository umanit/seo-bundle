<?php

namespace AppTestBundle\Controller;

use AppTestBundle\Entity\Product;
use AppTestBundle\Entity\ProductCategory;
use AppTestBundle\Entity\ProductMainCategory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    /**
     * @Route("/product/{mainCategory}/{category}/{slug}", name="app_test_product_show")
     *
     * @param string $mainCategory
     * @param string $category
     * @param string $slug
     *
     * @return Response
     */
    public function show(string $mainCategory, string $category, string $slug)
    {
        $mainCategory = $this->getDoctrine()->getRepository(ProductMainCategory::class)->findOneBy(['slug' => $mainCategory]);
        $category     = $this->getDoctrine()->getRepository(ProductCategory::class)->findOneBy(['slug' => $category]);
        $product      = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['slug' => $slug]);

        if (null === $mainCategory || null === $category || null === $product) {
            throw $this->createNotFoundException();
        }

        return $this->render('@AppTestBundle/product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @Route("/product-category/{mainCategory}/{slug}", name="app_test_product_category_show")
     *
     * @param string $mainCategory
     * @param string $slug
     *
     * @return Response
     */
    public function showCategory(string $mainCategory, string $slug)
    {
        $mainCategory = $this->getDoctrine()->getRepository(ProductMainCategory::class)->findOneBy(['slug' => $mainCategory]);
        $category     = $this->getDoctrine()->getRepository(ProductCategory::class)->findOneBy(['slug' => $slug]);

        return $this->render('@AppTestBundle/product/show.html.twig', [
            'product' => $category,
        ]);
    }
}
