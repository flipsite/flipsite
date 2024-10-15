<?php

declare(strict_types=1);
namespace Flipsite\Style;

use Symfony\Component\Yaml\Yaml;

final class Preflight
{
    private array $styles;

    public function __construct()
    {
        $this->styles = Yaml::parse(file_get_contents(__DIR__.'/preflight.yaml'));
    }

    public function getCss(array $elements) : string
    {
        $css = '';
        foreach ($this->styles as $block) {
            $for = is_string($block['for']) ? [$block['for']] : $block['for'];
            unset($block['for']);
            $forElements = array_intersect($for, $elements);
            if (count($forElements)) {
                $css .= implode(',', $forElements).'{'.$this->rules($block).'}';
            }
        }
        return $css;
    }

    public function getAll() : string
    {
        $css = '';
        foreach ($this->styles as $block) {
            $for = is_string($block['for']) ? [$block['for']] : $block['for'];
            unset($block['for']);
            $css .= implode(',', $for).'{'.$this->rules($block).'}';
        }
        return $css;
    }

    private function rules(array $rules) : string
    {
        $css = [];
        foreach ($rules as $attr => $value) {
            $css[] = $attr.':'.$value;
        }
        return implode(';', $css);
    }
}
