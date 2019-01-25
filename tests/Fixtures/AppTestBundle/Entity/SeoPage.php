<?php

namespace AppTestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Umanit\SeoBundle\Doctrine\Model\HasSeoMetadataInterface;
use Umanit\SeoBundle\Doctrine\Model\SeoMetadataTrait;
use Umanit\SeoBundle\Doctrine\Annotation\RouteParameter;
use Umanit\SeoBundle\Doctrine\Annotation\Seo;

/**
 * Class SeoPage
 *
 * @ORM\Entity()
 * @Seo(
 *     routeName="app_test_page_show",
 *     routeParameters={
 *         @RouteParameter(parameter="slug", property="slug")
 * })
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class SeoPage implements HasSeoMetadataInterface
{
    use SeoMetadataTrait;

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
     * @ORM\Column()
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
}
