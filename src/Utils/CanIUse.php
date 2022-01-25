<?php

declare(strict_types=1);
namespace Flipsite\Utils;

final class CanIUse
{
    private \UAParser\Result\Client $result;

    public function __construct()
    {
        $parser       = \UAParser\Parser::create();
        //$this->result = $parser->parse($_SERVER['HTTP_USER_AGENT']);
        $this->result = $parser->parse('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Safari/605.1.15');
    }

    public function cssMathFunctions() : bool
    {
        $major  = intval($this->result->ua->major);
        $minor  = intval($this->result->ua->minor);

        $supported = [
            'IE'               => false,
            'Edge'             => 12,
            'Firefox'          => 75,
            'Chrome'           => 79,
            'Safari'           => [11, 1],
            'Opera'            => 66,
            'Mobile Safari'    => [11, 3],
            'Android'          => 93,
            'Opera Mobile'     => 64,
            'UC Browser'       => false,
            'Samsung Internet' => 12
        ];

        if (!isset($supported[$this->result->ua->family])) {
            return true;
        }

        $supported = $supported[$this->result->ua->family];
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

    public function webp() : bool
    {
        if (isset($this->cache['webp'])) {
            return $this->cache['webp'];
        }
        $supported = [
            'IE'            => false,
            'Edge'          => 18,
            'Firefox'       => 65,
            'Chrome'        => 32,
            'Safari'        => 15,
            'Opera'         => 19,
            'Mobile Safari' => 14,
        ];

        $major  = intval($this->result->ua->major);
        $minor  = intval($this->result->ua->minor);

        if (!isset($supported[$this->result->ua->family])) {
            return $this->cache['webp'] = true;
        }
        $supported = $supported[$this->result->ua->family];
        if (is_bool($supported)) {
            return $this->cache['webp'] = $supported;
        }
        if (is_int($supported)) {
            return $this->cache['webp'] = $major >= $supported;
        }

        if (is_array($supported)) {
            return $this->cache['webp'] = $major > $supported[0] || ($major === $supported[0] && $minor >= $supported[1]);
        }

        return $this->cache['webp'] = false;
    }
}
