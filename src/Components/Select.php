<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;
use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

final class Select extends AbstractComponent
{
    protected string $tag   = 'select';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $options = ArrayHelper::decodeJsonOrCsv($data['options']);
        $selected = $data['selected'] ?? '';
        foreach ($options as $value) {
            $option = new Element('option', true);
            $option->setContent($value);
            $option->setAttribute('value', $value);
            if ($selected === $value) {
                $option->setAttribute('selected', true);
            }
            $this->addChild($option);
        }
    }

    public function normalize(array $data): array
    {
        if (!is_array($data)) {
            $data = ['options' => [$data]];
        }
        if (!isset($data['options'])) {
            $data['options'] = ['No options'];

        }
        return $data;
    }
}
