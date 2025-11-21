<?php

declare(strict_types=1);

namespace Flipsite\Data;

use Flipsite\Utils\ArrayHelper;

class YamlComponentData extends AbstractComponentData
{
    use ComponentTypesTrait;

    public function __construct(?array $path, ?string $id, string $type, array $data, ?array $style = null)
    {
        $this->path       = $path ?? [];
        $this->path[]     = $id;
        $this->id         = $id;
        $this->type       = $type;
        $style            = $data['_style'] ?? $style ?? [];
        $componentsInData = [];
        unset($data['_style']);
        foreach ($data as $attr => $value) {
            $type = explode(':', $attr)[0];
            switch ($type) {
                case 'toggle':
                case 'logo':
                    $type = 'button';
                    break;
            }
            if ('_meta' === $attr) {
                foreach ($value ?? [] as $metaAttr => $metaValue) {
                    $this->meta[$metaAttr] = $metaValue;
                }
            } elseif (!$this->isComponent($type) || 'schemaOrg' === $this->type) {
                $this->data[$attr] = $value;
            } elseif (null !== $value) {
                if (!is_array($value)) {
                    $value = ['value' => $value];
                }
                if ($this->isContainer($this->type)) {
                    $componentsInData[] = $attr;
                    $value['_style']    = ArrayHelper::merge($style[$attr] ?? [], $value['_style'] ?? []);
                    $this->children[]   = new YamlComponentData($this->path, null === $id ? null : $id.'.'.$attr, $type, $value);
                } else {
                    $this->data[$attr] = $value;
                }
            }
        }
        // Remove style for components in data
        foreach ($style as $attr => $value) {
            if (!in_array($attr, $componentsInData)) {
                $this->style[$attr] = $value;
            }
        }
    }
}
