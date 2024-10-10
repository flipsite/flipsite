<?php

declare(strict_types=1);
namespace Flipsite\Components;

abstract class AbstractComponent extends AbstractElement
{
    use \Flipsite\Traits\ComponentTypeTrait;

    abstract public function build(array $data, array $style, array $options) : void;

    public function normalize(string|int|bool|array $data) : array
    {
        return is_array($data) ? $data : ['value' => $data];
    }

    public function applyData(array $data, array $dataSource) : array
    {
        foreach ($data as $key => &$value) {
            $isComponent = !!$this->getComponentType($key);
            if ($isComponent) {
            } elseif (is_string($value) && strpos($value, '{') !== false) {
                $matches = [];
                preg_match_all('/\{[^{}]+\}/', $value, $matches);
                $original = $value;
                foreach ($matches[0] as $match) {
                    $var   = trim($match, '{}');
                    if (isset($dataSource[$var])) {
                        $value = str_replace($match, $dataSource[$var] ?? null, $value);
                    } else {
                        $value = null;
                    }
                }
                if (count($matches[0]) === 1) {
                    $data['_original'] ??= [];
                    $data['_original'][$key] = $original;
                };
            } elseif (is_array($value) && in_array($key, ['_attr', '_options', 'render'])) {
                $value = $this->applyData($value, $dataSource);
            }
        }
        return $data;
    }
}
