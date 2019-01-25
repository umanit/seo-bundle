<?php

namespace Umanit\SeoBundle\Utils\Text;

/**
 * Html helper.
 * Inspired by Bolt CMS.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class Html
{
    /**
     * Trim text to a given length.
     *
     * @param string $str           String to trim
     * @param int    $desiredLength Target string length
     * @param bool   $hellip        Add dots when the string is too long
     * @param int    $cutOffCap     Maximum difference between string length when removing words
     *
     * @return string Trimmed string
     */
    public static function trimText($str, $desiredLength, $hellip = true, $cutOffCap = 10): string
    {
        if ($hellip) {
            $ellipseStr = ' …';
            $newLength  = $desiredLength - 1;
        } else {
            $ellipseStr = '';
            $newLength  = $desiredLength;
        }

        $str = trim(strip_tags($str));

        if (mb_strlen($str) > $desiredLength) {
            $nextChar = mb_substr($str, $newLength, 1);
            $str      = mb_substr($str, 0, $newLength);
            if (' ' !== $nextChar && ($lastSpace = mb_strrpos($str, ' ')) !== false) {
                // Check for to long cutoff
                if (mb_strlen($str) - $lastSpace >= $cutOffCap) {
                    // Trim the ellipse, as we do not want a space now
                    return $str.trim($ellipseStr);
                }
                $str = mb_substr($str, 0, $lastSpace);
            }
            $str .= $ellipseStr;
        }

        return $str;
    }
}
