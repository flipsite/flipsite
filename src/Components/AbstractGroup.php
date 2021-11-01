<?php

declare(strict_types=1);
namespace Flipsite\Components;

abstract class AbstractGroup extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\UrlTrait;

    protected string $tag = 'div';

    public function with(array $data, array $style) : void
    {
        $wrapperStyle = $style['wrapper'] ?? false;
        unset($style['wrapper']);
        if ($wrapperStyle) {
            $this->tag = $wrapperStyle['tag'] ?? 'div';
            unset($wrapperStyle['tag']);
            $this->addStyle($wrapperStyle);
            $content = new Element($style['tag'] ?? 'div');
            $content->addStyle($style);
            unset($style['tag']);
            $this->addChild($content);
        } else {
            $this->addStyle($style);
        }
        $children = [];

        foreach ($data as $type => $componentData) {
            $children[] = $this->builder->build('heading', 'Test');
        }
        if ($wrapperStyle) {
            $content->addChildren($children);
        } else {
            $this->addChildren($children);
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
