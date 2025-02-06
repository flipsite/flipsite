<?php

declare(strict_types=1);
namespace Flipsite\Data;

use Flipsite\Utils\ArrayHelper;

class YamlComponentData extends AbstractComponentData
{
    use ComponentTypesTrait;

    public function __construct(string $id, string $type, array $data, SiteDataInterface $siteData)
    {
        $this->id    = $id;
        $this->type  = $type;
        $style       = $data['_style'] ?? [];
        unset($data['_style']);
        foreach ($data as $attr => $value) {
            $type = explode(':', $attr)[0];
            switch ($type) {
                case 'toggle':
                case 'logo':
                    $type = 'button';
                    break;
            }
            if (!$this->isComponent($type)) {
                $this->data[$attr] = $value;
            } else {
                if (!is_array($value)) {
                    $value = ['value' => $value];
                }
                $value['_style']  = ArrayHelper::merge($style[$attr] ?? [], $value['_style'] ?? []);
                $this->children[] = new YamlComponentData($id.'.'.$attr, $type, $value, $siteData);
            }
        }
        foreach ($style as $attr => $value) {
            $type = explode(':', $attr)[0];
            if (!$this->isComponent($type)) {
                $this->style[$attr] = $value;
            }
        }
    }
}
