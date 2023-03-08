<?php

declare(strict_types=1);
namespace Flipsite\Utils;

class StyleNthOptimizer
{
    private array $value = ['base'=>''];
    private array $cols = [];

    public function __construct(private string $style)
    {
        $keywords = ['even', 'odd', 'first', 'last'];
        $tmp = explode(' ', $style);
        foreach ($tmp as $value) {
            foreach ($keywords as $keyword) {
                if (strpos($value, $keyword.':') !== false) {

                    $colValue = str_replace($keyword.':','',$value);
                    $tmp = explode(':',$colValue);
                    $this->cols[$keyword] ??= [];
                    if (count($tmp) === 1) {
                        $this->cols[$keyword]['base'] = $tmp[0];
                    } else {
                        $this->cols[$keyword][$tmp[0]] = $tmp[1];
                    }
                    continue 2;
                }
            }
            $tmp = explode(':',$value);
            if (count($tmp) === 1) {
                $this->value['base'] = $tmp[0];
            } else {
                $this->value[$tmp[0]] = $tmp[1];
            }
        }
    }

    public function get(int $index, int $total) :?string
    {
        $style = null;
        if ($index === 0) {
            $style = $this->cols['first'] ?? $this->cols['odd'] ?? null;
        } elseif ($index === $total-1) {
            $style = $this->cols['last'] ?? $this->cols[$index%2==0?'odd':'even'] ?? null;
        } elseif ($index%2==0) {
            $style = $this->cols['odd'] ?? null;
        } elseif ($index%2==1) {
            $style = $this->cols['even'] ?? null;
        }
        if ($style) {
            foreach ($style as $variant => $value) {
                $this->value[$variant] = $value;
            }
        }
        $style = $this->value['base'];
        unset($this->value['base']);
        foreach ($this->value as $variant => $value) {
            $style.= ' '.$variant.':'.$value;
        }
        return $style;
    }
}
