<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Group extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\UrlTrait;

    public function with($data, $style) : void
    {
        $style = $this->setStyle($style);

        foreach ($data as $type => $componentData) {
            $this->addChild($this->builder->build($type, $componentData));
        }
    }

    // private function setStyle(array $style) : array
    // {
    //     $this->addStyle($style['container'] ?? null);
    //     $this->addStyle($style['wrapper'] ?? null);
    // }

    // protected string $tag = 'div';

    // public function __construct(array $data)
    // {
    //     // $style = $data['style'];
    //     // print_r($style);
    //     // unset($data['style']);
    //     // $this->tag = 'section';
    //     // foreach ($data as $componentType => $componentData) {
    //     //     //echo $componentType;
    //     // }
    // }

    // public function with(ComponentData $data) : void
    // {
    //     $this->addStyle($data->getStyle('container'));
    //     $components = $this->builder->build($data->get(), $data->getStyle(), $data->getAppearance());
    //     $this->addChildren($components);
    // }
}
