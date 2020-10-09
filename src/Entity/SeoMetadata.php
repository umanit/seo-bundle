<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable
 */
class SeoMetadata
{
    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): SeoMetadata
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): SeoMetadata
    {
        $this->description = $description;

        return $this;
    }
}
