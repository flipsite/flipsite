<?php

declare(strict_types=1);

if (!function_exists('str_starts_with')) {
    /**
     * Convenient way to check if a string starts with another string.
     *
     * @param string $haystack string to search through
     * @param string $needle   pattern to match
     *
     * @return bool returns true if $haystack starts with $needle
     */
    function str_starts_with(string $haystack, string $needle) : bool
    {
        $length = mb_strlen($needle);
        return mb_substr($haystack, 0, $length) === $needle;
    }
}

if (!function_exists('str_ends_with')) {
    /**
     * Convenient way to check if a string ends with another string.
     *
     * @param string $haystack string to search through
     * @param string $needle   pattern to match
     *
     * @return bool returns true if $haystack ends with $needle
     */
    function str_ends_with(string $haystack, string $needle) : bool
    {
        $length = mb_strlen($needle);
        return mb_substr($haystack, -$length) === $needle;
    }
}

if (!function_exists('mb_str_starts_with')) {
    /**
     * Multibyte - Convenient way to check if a string starts with another string.
     *
     * @param string $haystack string to search through
     * @param string $needle   pattern to match
     *
     * @return bool returns true if $haystack starts with $needle
     */
    function mb_str_starts_with(string $haystack, string $needle) : bool
    {
        $length = mb_strlen($needle);
        return mb_substr($haystack, 0, $length) === $needle;
    }
}

if (!function_exists('mb_str_ends_with')) {
    /**
     * Multibyte - Convenient way to check if a string ends with another string.
     *
     * @param string $haystack string to search through
     * @param string $needle   pattern to match
     *
     * @return bool returns true if $haystack ends with $needle
     */
    function mb_str_ends_with(string $haystack, string $needle) : bool
    {
        $length = mb_strlen($needle);
        return mb_substr($haystack, -$length) === $needle;
    }
}
