<?php

namespace Umanit\SeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * Class UrlHistory
 *
 * @ORM\Table(name="umanit_seo_url_history", indexes={
 *     @ORM\Index(name="umanit_seo_history_search_idx", columns={"old_path", "locale"}),
 * })
 * @ORM\Entity(repositoryClass="Umanit\SeoBundle\Repository\UrlHistoryRepository")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class UrlHistory
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
     * @ORM\Column(name="old_path", nullable=false, length=512)
     * @var string
     */
    private $oldPath;

    /**
     * @ORM\Column(name="new_path", nullable=false, length=512)
     * @var string
     */
    private $newPath;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=10, nullable=true)
     */
    private $locale;

    /**
     * @var string
     *
     * @ORM\Column(type="guid", unique=false)
     */
    private $seoUuid;

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
    public function getRoute(): ?string
    {
        return $this->route;
    }

    /**
     * @param string $route
     *
     * @return self
     */
    public function setRoute(?string $route): self
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @return string
     */
    public function getOldPath(): ?string
    {
        return $this->oldPath;
    }

    /**
     * @param string $oldPath
     *
     * @return self
     */
    public function setOldPath(?string $oldPath): self
    {
        $this->oldPath = $oldPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getNewPath(): ?string
    {
        return $this->newPath;
    }

    /**
     * @param string $newPath
     *
     * @return self
     */
    public function setNewPath(?string $newPath): self
    {
        $this->newPath = $newPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @return self
     */
    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSeoUuid(): ?string
    {
        return $this->seoUuid;
    }

    /**
     * @param string|null $seoUuid
     *
     * @return UrlHistory
     */
    public function setSeoUuid(?string $seoUuid): self
    {
        $this->seoUuid = $seoUuid;

        return $this;
    }
}
