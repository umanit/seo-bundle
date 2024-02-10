<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Utils\Text;

class Str
{
    /**
     * Insensitive fuzzy search of a string in an array.
     */
    public static function striposInArray(string $needle, array $haystack): bool
    {
        $needle = mb_strtolower($needle);

        foreach ($haystack as $hay) {
            if ($hay === $needle || stripos($needle, (string) $hay)) {
                return true;
            }
        }

        return false;
    }
}
