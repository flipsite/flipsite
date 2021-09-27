<?php

declare(strict_types=1);

namespace Flipsite\Utils;

final class CanIUse
{
    public static array $parserResults = [];
    public static function getResult(string $userAgent) : \UAParser\Result\Client
    {
        if (!isset(self::$parserResults[$userAgent])) {
            $parser = \UAParser\Parser::create();
            self::$parserResults[$userAgent] = $parser->parse($userAgent);
        }
        return self::$parserResults[$userAgent];
    }
    public static function cssMathFunctions(string $userAgent) : bool
    {
        $result = self::getResult($userAgent);
        $major = intval($result->ua->major);
        $minor = intval($result->ua->minor);

        $supported = [
            'IE' => false,
            'Edge' => 12,
            'Firefox' => 75,
            'Chrome' => 79,
            'Safari' => [11,1],
            'Opera' => 66,
            'Mobile Safari' => [11,3],
            'Android' => 93,
            'Opera Mobile' => 64,
            'UC Browser' => false,
            'Samsung Internet' => 12
        ];

        if (!isset($supported[$result->ua->family])) {
            return true;
        }

        $supported = $supported[$result->ua->family];
        if (is_bool($supported)) {
            return $supported;
        }
        if (is_int($supported)) {
            return $major >= $supported ;
        }

        if (is_array($supported)) {
            return $major > $supported[0] || ($major === $supported[0] && $minor >= $supported[1]);
        }

        return false;
    }

    public static function webp(string $userAgent) : bool
    {
        $result = self::getResult($userAgent);
        $major = intval($result->ua->major);
        $minor = intval($result->ua->minor);

        $supported = [
            'IE' => false,
            'Edge' => 18,
            'Firefox' => 65,
            'Chrome' => 32,
            'Opera' => 19,
            'Mobile Safari' => 14,
        ];
        print_r($result);
        die();
        if (!isset($supported[$result->ua->family])) {
            return true;
        }
        $supported = $supported[$result->ua->family];
        if (is_bool($supported)) {
            return $supported;
        }
        if (is_int($supported)) {
            return $major >= $supported ;
        }

        if (is_array($supported)) {
            return $major > $supported[0] || ($major === $supported[0] && $minor >= $supported[1]);
        }

        return false;
    }
}
