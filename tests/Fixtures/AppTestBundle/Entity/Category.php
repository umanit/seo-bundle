<?php

namespace AppTestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\Schema;
use Umanit\SeoBundle\Doctrine\Annotation\RouteParameter;
use Umanit\SeoBundle\Doctrine\Annotation as Seo;

/**
 * Class Category
 *
 * @ORM\Entity()
 * @Seo\Route(
 *     routeName="app_test_category_show",
 *     routeParameters={
 *         @RouteParameter(parameter="slug", property="slug")
 * })
 * @Seo\SchemaOrgBuilder("buildSchemaOrg")
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class Category
{
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
     * The slug of the SeoPage
     *
     * @var string
     * @ORM\Column(unique=true)
     */
    private $slug;

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
     * Builds the schema.org.
     *
     * @return BaseType
     */
    public function buildSchemaOrg() : BaseType
    {
        return
            Schema::mensClothingStore()
                  ->name('Test')
                  ->email('test@umanit.fr')
                  ->contactPoint(Schema::contactPoint()->areaServed('Worldwide'))
            ;
    }
}
