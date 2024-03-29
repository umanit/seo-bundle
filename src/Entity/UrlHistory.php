<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Umanit\SeoBundle\Repository\UrlHistoryRepository;

/**
 * @ORM\Table(name="umanit_seo_url_history", indexes={
 *     @ORM\Index(name="umanit_seo_history_search_idx", columns={"old_path", "locale"}),
 * })
 * @ORM\Entity(repositoryClass="Umanit\SeoBundle\Repository\UrlHistoryRepository")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
#[ORM\Entity(repositoryClass: UrlHistoryRepository::class)]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
#[ORM\Table(name: 'umanit_seo_url_history')]
#[ORM\Index(columns: ['old_path', 'locale'], name: 'umanit_seo_history_search_idx')]
class UrlHistory
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    /**
     * @ORM\Column(name="route", type="string", length=255, nullable=false)
     */
    #[ORM\Column(name: 'route', type: 'string', length: 255, nullable: false)]
    private ?string $route = null;

    /**
     * @ORM\Column(name="old_path", nullable=false, length=512)
     */
    #[ORM\Column(name: 'old_path', length: 512, nullable: false)]
    private ?string $oldPath = null;

    /**
     * @ORM\Column(name="new_path", nullable=false, length=512)
     */
    #[ORM\Column(name: 'new_path', length: 512, nullable: false)]
    private ?string $newPath = null;

    /**
     * @ORM\Column(name="locale", type="string", length=10, nullable=true)
     */
    #[ORM\Column(name: 'locale', type: 'string', length: 10, nullable: true)]
    private ?string $locale = null;

    /**
     * @ORM\Column(name="seo_uuid", type="guid", unique=false)
     */
    #[ORM\Column(name: 'seo_uuid', type: 'guid', unique: false)]
    private ?string $seoUuid = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(?string $route): UrlHistory
    {
        $this->route = $route;

        return $this;
    }

    public function getOldPath(): ?string
    {
        return $this->oldPath;
    }

    public function setOldPath(?string $oldPath): UrlHistory
    {
        $this->oldPath = $oldPath;

        return $this;
    }

    public function getNewPath(): ?string
    {
        return $this->newPath;
    }

    public function setNewPath(?string $newPath): UrlHistory
    {
        $this->newPath = $newPath;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): UrlHistory
    {
        $this->locale = $locale;

        return $this;
    }

    public function getSeoUuid(): ?string
    {
        return $this->seoUuid;
    }

    public function setSeoUuid(?string $seoUuid): UrlHistory
    {
        $this->seoUuid = $seoUuid;

        return $this;
    }
}
