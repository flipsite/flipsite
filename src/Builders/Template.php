<?php

declare(strict_types=1);

namespace Flipsite\Builders;

use Adbar\Dot;
use Flipsite\Utils\ArrayHelper;

class Template
{
    private array $col;
    private array $template;

    public function __construct(array $col, array $template)
    {
        if (isset($template['default'])) {
            $col = ArrayHelper::merge($template['default'], $col);
            unset($template['default']);
        }
        $this->col      = $col;
        $this->template = $template;
    }

    public function apply() : array
    {
        $dot = new \Adbar\Dot($this->col);
        $col = $this->addData($this->template, $dot);
        return $this->checkConditional($col, $dot);
    }

    private function addData(array $template, Dot $data) : array
    {
        foreach ($template as $attr => &$val) {
            if (is_array($val)) {
                $val = $this->addData($val, $data);
            } elseif (is_string($val) && str_starts_with($val, '{{')) {
                $ref = mb_substr($val, 2, mb_strlen($val) - 4); // between {{ and }}
                $val = $data->get($ref);
            }
        }
        return $template;
    }

    private function checkConditional(array $col, Dot $data) : array
    {
        $rename = [];
        foreach ($col as $attr => &$val) {
            $parts = explode('|', (string) $attr);
            if (is_array($val)) {
                $val = $this->checkConditional($val, $data);
            }
            if (isset($parts[1])) {
                if ($this->check($parts[1], $data)) {
                    $rename[$attr] = $parts[0];
                } else {
                    unset($col[$attr]);
                }
            }
        }
        if (count($rename)) {
            foreach ($rename as $old => $new) {
                $col = ArrayHelper::renameKey($col, $old, $new);
            }
        }
        $numeric = true;
        foreach (array_keys($col) as $key) {
            if (!is_numeric($key)) {
                $numeric = false;
            }
        }
        if ($numeric) {
            $col = array_values($col);
        }

        return $col;
    }

    private function check(string $condition, Dot $data) : bool
    {
        if (0 === mb_strpos($condition, 'if(')) {
            $condition = mb_substr($condition, 3, mb_strlen($condition) - 4);
            return $data->has(str_replace(':', '.', $condition));
        }
        return false;
    }
}
