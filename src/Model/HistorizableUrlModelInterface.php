<?php

namespace Umanit\SeoBundle\Model;

use Umanit\SeoBundle\Entity\UrlReference;

interface HistorizableUrlModelInterface extends RoutableModelInterface
{
    public function getUrlReference(): ?UrlReference;

    public function setUrlReference(?UrlReference $urlRef): HistorizableUrlModelInterface;
}
