<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Utils\EntityParser;

use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Umanit\SeoBundle\Utils\Text\Html;
use Umanit\SeoBundle\Utils\Text\Str;

class Title implements EntityParserInterface
{
    // The field names we do not want in a title.
    public const STRIP_KEYS = [
        'slug',
        'owner',
        'status',
        'sort',
        'locale',
        'siteaccess',
        'translation',
        'uuid',
        'image',
        'media',
        'video',
        'excerpt',
        'description',
        'intro',
        'introduction',
    ];

    // Friendly field names found in a title.
    public const FAV_KEYS = [
        'name',
        'title',
        'label',
        'appellation',
        'username',
        'nickname',
        'pseudonym',
        'denomination',
        'designation',
        'firstname',
        'lastname',
        'surname',
        'identity',
    ];

    public function __construct(
        private readonly PropertyAccessorInterface $accessor,
    ) {
    }

    /**
     * Generate and return an entity's title.
     *
     * @param object $entity The object from which the title is generated.
     * @param int    $length The max length of the excerpt.
     */
    public function fromEntity(object $entity, int $length = 100): ?string
    {
        $refl = new \ReflectionClass($entity);
        $properties = $refl->getProperties();

        // Consider favourite keys first
        uasort($properties, function (\ReflectionProperty $a, \ReflectionProperty $b): int {
            if (\in_array($a->getName(), $this::FAV_KEYS, true)) {
                return -1;
            }

            if (\in_array($b->getName(), $this::FAV_KEYS, true)) {
                return 1;
            }

            return 0;
        });

        // Parse every string attributes
        foreach ($properties as $property) {
            // Strip out unwanted values
            if (Str::striposInArray($property->getName(), self::STRIP_KEYS)) {
                continue;
            }

            // Get the value
            try {
                $value = $this->accessor->getValue($entity, $property->getName());
            } catch (AccessException) {
                continue;
            }

            // If the field is one of the favourite
            // keys, directly return its value.
            if (false === \is_string($value)) {
                continue;
            }

            if (!Str::striposInArray($property->getName(), self::FAV_KEYS)) {
                continue;
            }

            return Html::trimText($value, $length);
        }

        return null;
    }
}
