<?php

namespace AppTestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ProductMainCategory. Used for testing multilevel url.
 *
 * @ORM\Entity()
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class ProductMainCategory
{
    /**
     * The identifier of ProductMainCategory.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * The name of the ProductMainCategory
     *
     * @var string
     * @ORM\Column(nullable=true)
     */
    private $name;

    /**
     * The slug of the ProductMainCategory
     *
     * @var string
     * @ORM\Column(unique=true)
     */
    private $slug;

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
}
