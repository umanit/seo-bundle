<?php

namespace AppTestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Umanit\SeoBundle\Doctrine\Annotation as Seo;
use Umanit\SeoBundle\Doctrine\Annotation\RouteParameter;
use Umanit\SeoBundle\Doctrine\Model\SeoMetadataTrait;
use Umanit\SeoBundle\Doctrine\Model\UrlHistorizedInterface;
use Umanit\SeoBundle\Doctrine\Model\HistorizableUrlTrait;
use Umanit\SeoBundle\Model\HasSeoMetadataInterface;

/**
 * Class SeoPage
 *
 * @ORM\Entity()
 * @Seo\Route(
 *     routeName="app_test_page_show",
 *     routeParameters={
 *         @RouteParameter(parameter="slug", property="slug"),
 *         @RouteParameter(parameter="category", property="category.slug")
 * })
 * @Seo\SchemaOrgBuilder("AppTestBundle\Service\SeoPageSchemaOrgBuilder")
 * @Seo\Breadcrumb({
 *     @Seo\BreadcrumbItem("app_test_home_page", name="Home"),
 *     @Seo\BreadcrumbItem("category", name="category.slug"),
 *     @Seo\BreadcrumbItem(name="name"),
 * })
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class SeoPage implements HasSeoMetadataInterface, UrlHistorizedInterface
{
    use SeoMetadataTrait, HistorizableUrlTrait;

    /**
     * The identifier of SeoPage.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * The name of the SeoPage
     *
     * @var string
     * @ORM\Column(nullable=true)
     */
    private $name;

    /**
     * The introduction of the SeoPage
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $introduction;

    /**
     * The slug of the SeoPage
     *
     * @var string
     * @ORM\Column(unique=true)
     */
    private $slug;

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="AppTestBundle\Entity\Category", cascade={"all"})
     */
    private $category;

    public function __construct()
    {
        $this->category = (new Category())->setSlug('my-category');
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     *
     * @return self
     */
    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    /**
     * @param string $introduction
     *
     * @return self
     */
    public function setIntroduction(?string $introduction): self
    {
        $this->introduction = $introduction;

        return $this;
    }

    /**
     * @return Category
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     *
     * @return self
     */
    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }
}
