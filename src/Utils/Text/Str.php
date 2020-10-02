<?php

namespace Umanit\SeoBundle\Utils\Text;

class Str
{
    /**
     * Insensitive fuzzy search of a string in an array.
     *
     * @param string $needle
     * @param array  $haystack
     *
     * @return bool
     */
    public static function striposInArray(string $needle, array $haystack): bool
    {
        $needle = mb_strtolower($needle);

        foreach ($haystack as $hay) {
            if ($hay === $needle || stripos($needle, $hay)) {
                return true;
            }
        }

        return false;
    }
}
