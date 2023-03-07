<?php

declare(strict_types=1);
namespace Flipsite\Utils;

class StyleNthOptimizer
{
    private array $values = [];

    public function __construct(private string $style)
    {
        $keywords = ['even', 'odd', 'first', 'last'];
        $tmp = explode(' ', $style);
        foreach ($tmp as $value) {
            foreach ($keywords as $keyword) {
                if (strpos($value, $keyword.':') !== false) {
                    $this->values[$keyword] = str_replace($keyword.':','',$value);
                    continue 2;
                }
            }
            $this->values['base'] = $value;
        }
    }

    public function get(int $index, int $total) :?string
    {
        if ($index === 0) {
            return $this->values['first'] ?? $this->values['odd'] ?? $this->values['base'];
        } elseif ($index === $total-1) {
            return $this->values['last'] ?? $this->values[$index%2==0?'odd':'even'] ?? $this->values['base'];
        } elseif ($index%2==0) {
            return $this->values['odd'] ?? $this->values['base'];
        } elseif ($index%2==1) {
            return $this->values['even'] ?? $this->values['base'];
        }
        return $this->style;
    }
}