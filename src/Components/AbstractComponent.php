<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

abstract class AbstractComponent extends AbstractElement
{
    use \Flipsite\Traits\ComponentTypeTrait;

    abstract public function build(AbstractComponentData $component, InheritedComponentData $inherited): void;

    public function normalize(array $data): array
    {
        return $data;
    }

    public function applyData(array $data, array $dataSource, array &$replaced): array
    {
        foreach ($data as $key => &$value) {
            $isComponent = !!$this->getComponentType($key);
            if ($isComponent) {
            } elseif (is_string($value) && strpos($value, '{') !== false) {
                // Dont apply data on JSON
                if (str_starts_with($value, '[{"')) {
                    $json = json_decode($value, true);
                    if (is_array($json)) {
                        continue;
                    }
                }
                $matches = [];
                preg_match_all('/\{[^{}]+\}/', $value, $matches);
                $original = $value;
                foreach ($matches[0] as $match) {
                    $var   = trim($match, '{}');
                    if (isset($dataSource[$var])) {
                        $value      = str_replace($match, (string)$dataSource[$var], (string)$value);
                        $replaced[] = $match;
                    } else {
                        $value = null;
                    }
                }
                if ($original !== $value) {
                    $data['_original'] ??= [];
                    $data['_original'][$key] = $original;
                };
            } elseif (is_array($value) && in_array($key, ['_attr', '_options', 'render'])) {
                $value = $this->applyData($value, $dataSource, $replaced);
            }
        }
        return $data;
    }
}
