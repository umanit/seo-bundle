<?php

namespace AppTestBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Umanit\SeoBundle\Doctrine\Annotation as Seo;
use Doctrine\ORM\Mapping as ORM;
use Umanit\SeoBundle\Doctrine\Model\UrlHistorizedInterface;
use Umanit\SeoBundle\Doctrine\Model\UrlHistorizedTrait;

/**
 * Class Product. Used for testing multilevel url.
 *
 * @ORM\Entity()
 * @Seo\Route(
 *     routeName="app_test_product_show",
 *     routeParameters={
 *         @Seo\RouteParameter(parameter="slug", property="slug"),
 *         @Seo\RouteParameter(parameter="category", property="categories[0].slug"),
 *         @Seo\RouteParameter(parameter="mainCategory", property="categories[0].parent.slug")
 * })
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class Product implements UrlHistorizedInterface
{
    use UrlHistorizedTrait;

    /**
     * The identifier of Product.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * The name of the Product
     *
     * @var string
     * @ORM\Column(nullable=true)
     */
    private $name;

    /**
     * The slug of the Product
     *
     * @var string
     * @ORM\Column(unique=true)
     */
    private $slug;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="AppTestBundle\Entity\ProductCategory", cascade={"all"})
     */
    private $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
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

    public function getCategories(): ?Collection
    {
        return $this->categories;
    }

    public function addCategory(ProductCategory $categeory): self
    {
        if (!$this->categories->contains($categeory)) {
            $this->categories[] = $categeory;
            $categeory->addProduct($this);
        }

        return $this;
    }
}
