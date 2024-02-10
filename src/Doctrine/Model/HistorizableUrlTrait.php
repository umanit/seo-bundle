<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Doctrine\Model;

use Doctrine\ORM\Mapping as ORM;
use Umanit\SeoBundle\Entity\UrlReference;
use Umanit\SeoBundle\Model\HistorizableUrlModelInterface;

trait HistorizableUrlTrait
{
    /**
     * @ORM\OneToOne(targetEntity="Umanit\SeoBundle\Entity\UrlReference", cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="seo_url_reference", nullable=false)
     */
    #[ORM\OneToOne(targetEntity: UrlReference::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'seo_url_reference', nullable: false)]
    protected ?UrlReference $urlReference = null;

    public function getUrlReference(): ?UrlReference
    {
        return $this->urlReference;
    }

    public function setUrlReference(?UrlReference $urlReference): HistorizableUrlModelInterface
    {
        $this->urlReference = $urlReference;

        return $this;
    }
}
