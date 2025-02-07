<?php

declare(strict_types=1);
namespace Flipsite\Data;

class ComponentData extends AbstractComponentData
{
    use ComponentTypesTrait;

    public function __construct(string $id, string $type, array $data, array $style = [])
    {
        $this->id    = $id;
        $this->type  = $type;
        $this->data  = $data;
        $this->style = $style;
    }
}
