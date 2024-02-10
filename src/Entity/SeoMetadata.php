<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable
 */
#[ORM\Embeddable]
class SeoMetadata
{
    /**
     * @ORM\Column(nullable=true)
     */
    #[ORM\Column(nullable: true)]
    protected ?string $title = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $description = null;

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
