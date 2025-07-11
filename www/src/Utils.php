<?php

namespace RecAnalyst;

/**
 * Miscellaneous utilities for working with RecAnalyst.
 */
class Utils
{
    /**
     * Format a game time as "HH:MM:SS".
     *
     * @param int $time Game time in milliseconds.
     * @param string $format sprintf-style format.
     *     Defaults to %02d:%02d:%02d, for HH:MM:SS.
     * @return string Formatted string, or "-" if the time is 0. (Zero usually
     *     means "did not occur" or "unknown" in recorded game timestamps.)
     */
    public static function formatGameTime($time, $ms_fix = 1000, $format = '%02d:%02d:%02d')
    {
        if ($time <= 0) {
            return '-';
        }
        $hour = (int)($time / $ms_fix / 3600);
        $minute = ((int)($time / $ms_fix / 60)) % 60;
        $second = ((int)($time / $ms_fix)) % 60;
        return sprintf($format, $hour, $minute, $second);
    }

    /**
     * Convert strings in record to UTF-8 encoded
     *
     * @param $str
     * @return string
     */
    public static function stringToUTF8($str, $raw_encoding = 'gbk')
    {
        $utf8_str = mb_convert_encoding($str, "UTF-8", $raw_encoding);
        return $utf8_str;
    }

    /**
     * Convert chat message to array
     *
     * @param $rawMsg
     * @return array
     */
    public static function msgToArray($rawMsg) {
        $msgArray = [];
        foreach ($rawMsg as  $msg) {
            $msgArray[] = $msg->toArray();
        }
        return $msgArray;
    }
    public static function lightenHexColor(string $hexColor, float $percent): string
    {
        // Remove the '#' if present
        $hexColor = ltrim($hexColor, '#');

        // Ensure it's a valid 6-digit hex color
        if (strlen($hexColor) !== 6) {
            // You might want to throw an exception or return a default color
            return '#000000'; // Return black for invalid input
        }

        // Convert hex to RGB
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));

        // Lighten each RGB component
        $r = round($r + (255 - $r) * $percent);
        $g = round($g + (255 - $g) * $percent);
        $b = round($b + (255 - $b) * $percent);

        // Clamp values to stay within 0-255 range
        $r = max(0, min(255, $r));
        $g = max(0, min(255, $g));
        $b = max(0, min(255, $b));

        // Convert RGB back to hex
        return sprintf("#%02X%02X%02X", $r, $g, $b);
    }
}
