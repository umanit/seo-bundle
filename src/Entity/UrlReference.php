<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="umanit_seo_url_reference", indexes={
 *     @ORM\Index(name="umanit_seo_url_ref_search_idx", columns={"seo_uuid"}),
 * })
 * @ORM\Entity(repositoryClass="Umanit\SeoBundle\Repository\UrlHistoryRepository")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class UrlReference
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="route", type="string", length=255, nullable=false)
     */
    private $route;

    /**
     * @var string
     *
     * @ORM\Column(name="url", nullable=false, length=512)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=10, nullable=true)
     */
    private $locale;

    /**
     * @var string
     *
     * @ORM\Column(name="seo_uuid", type="guid", unique=true)
     */
    private $seoUuid;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): UrlReference
    {
        $this->url = $url;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): UrlReference
    {
        $this->locale = $locale;

        return $this;
    }

    public function getSeoUuid(): ?string
    {
        return $this->seoUuid;
    }

    public function setSeoUuid(?string $seoUuid): UrlReference
    {
        $this->seoUuid = $seoUuid;

        return $this;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(?string $route): UrlReference
    {
        $this->route = $route;

        return $this;
    }
}
