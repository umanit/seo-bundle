<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Utils\EntityParser;

interface EntityParserInterface
{
    /**
     * Generate and return an entity's excerpt.
     *
     * @param object $entity The object from which the excerpt is generated.
     * @param int    $length The max length of the excerpt.
     *
     * @return ?string
     */
    public function fromEntity(object $entity, $length = 150): ?string;
}
