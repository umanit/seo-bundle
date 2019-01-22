<?php

namespace Umanit\SeoBundle\Runtime\Twig;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Umanit\SeoBundle\Exception\NotSeoEntityException;
use Umanit\SeoBundle\Model\AnnotationReaderTrait;
use Umanit\SeoBundle\Routing\Canonical;
use Umanit\SeoBundle\Runtime\CurrentSeoEntity;

/**
 * Class SeoExtension
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class SeoExtension extends AbstractExtension
{
    use AnnotationReaderTrait;

    /** @var Canonical */
    private $canonical;

    /** @var CurrentSeoEntity */
    private $currentSeoEntity;

    /**
     * SeoExtension constructor.
     *
     * @param Canonical        $canonical
     * @param CurrentSeoEntity $currentSeoEntity
     */
    public function __construct(Canonical $canonical, CurrentSeoEntity $currentSeoEntity)
    {
        $this->canonical    = $canonical;
        $this->currentSeoEntity = $currentSeoEntity;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('canonical', [$this, 'canonical'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Generates and returns the canonical url.
     *
     * @param object|null $entity
     * @param array|null  $overrides
     *
     * @return string
     */
    public function canonical(?object $entity = null, array $overrides = []): string
    {
        try {
            return sprintf('<link rel="canonical" href="%s"/>', $this->canonical->url(
                $entity ?? $this->currentSeoEntity->get(),
                $overrides
            ));
        } catch (NotSeoEntityException $e) {
            return '';
        }
    }
}
