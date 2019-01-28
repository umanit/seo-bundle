<?php

namespace AppTestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Umanit\SeoBundle\Doctrine\Model\HasSeoMetadataInterface;
use Umanit\SeoBundle\Doctrine\Model\SeoMetadataTrait;
use Umanit\SeoBundle\Doctrine\Annotation\RouteParameter;
use Umanit\SeoBundle\Doctrine\Annotation\Seo;

/**
 * Class Category
 *
 * @ORM\Entity()
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
}
