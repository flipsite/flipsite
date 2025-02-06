<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

abstract class AbstractTag extends AbstractComponent
{
    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data = $component->getData();
        if (!isset($data['value']) && !$this->empty && !$this->oneline) {
            $this->render = false;
            return;
        }
        $this->tag = $data['tag'] ?? $this->tag;
        if (isset($data['value'])) {
            $this->setContent($data['value']);
        }
    }

    public function normalize(array $data): array
    {
        if (isset($data['_noContent'])) {
            $this->oneline = true;
        }
        return $data;
    }
}
