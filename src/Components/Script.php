<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

final class Script extends AbstractComponent
{
    protected string $tag   = 'script';
    protected bool $oneline = true;

    public function normalize(string|int|bool|array $data): array
    {
        if (!is_array($data)) {
            $data = ['value' => $data];
        }
        if (isset($data['value'])) {
            unset($data['_attr']['src'], $data['_attr']['defer']);
        }
        return $data;
    }

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        //print_r($component);
        // if (isset($data['value'])) {
        //     $this->setContent($data['value']);
        // }
    }
}
