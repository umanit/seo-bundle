<?php

namespace Umanit\SeoBundle\Utils\Text;

/**
 * String Helper.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class Str
{
    /**
     * Insensitive fuzzy search of a string in an array.
     *
     * @param  string $needle
     * @param array   $haystack
     *
     * @return bool
     */
    public static function striposInArray($needle, array $haystack): bool
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
