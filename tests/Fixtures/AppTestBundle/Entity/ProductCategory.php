<?php

namespace AppTestBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Umanit\SeoBundle\Doctrine\Annotation as Seo;
use Umanit\SeoBundle\Doctrine\Model\UrlHistorizedInterface;
use Umanit\SeoBundle\Doctrine\Model\HistorizableUrlTrait;

/**
 * Class ProductCategory. Used for testing multilevel url.
 *
 * @ORM\Entity()
 * @Seo\Route(
 *     routeName="app_test_product_category_show",
 *     routeParameters={
 *         @Seo\RouteParameter(parameter="slug", property="slug"),
 *         @Seo\RouteParameter(parameter="mainCategory", property="parent.slug")
 * })
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class ProductCategory implements UrlHistorizedInterface
{
    use HistorizableUrlTrait;

    /**
     * The identifier of ProductCategory.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * The name of the ProductCategory
     *
     * @var string
     * @ORM\Column(nullable=true)
     */
    private $name;

    /**
     * The slug of the ProductCategory
     *
     * @var string
     * @ORM\Column(unique=true)
     */
    private $slug;

    /**
     * @ORM\ManyToOne(targetEntity="AppTestBundle\Entity\ProductMainCategory", cascade={"all"})
     * @var ProductMainCategory
     */
    private $parent;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="AppTestBundle\Entity\Product", inversedBy="categories")
     */
    private $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getParent(): ?ProductMainCategory
    {
        return $this->parent;
    }

    public function setParent(?ProductMainCategory $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getProducts(): ?Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product)
    {
        $this->products[] = $product;

        return $this;
    }
}
