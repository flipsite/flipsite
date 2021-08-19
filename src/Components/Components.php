<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Components extends AbstractComponent
{
    use Traits\BuilderTrait;

    protected string $type = 'div';

    public function build(array $data, array $style, array $flags) : void
    {
        $this->addStyle($style['container'] ?? []);
        foreach ($this->getComponents($data) as $component) {
            $category = new Element('div');
            $category->addStyle($style['category'] ?? []);
            $category->setContent($component['name']);
            $this->addChild($category);
            foreach ($component['variants'] as $variant => $data) {
                $component          = $this->builder->build($variant, $data, []);
                $componentContainer = new Element('div');
                $componentContainer->addStyle($style['variant'] ?? []);
                $componentContainer->addChild($component);
                $this->addChild($componentContainer);
            }
        }
    }

    private function getComponents(array $components) : array
    {
        return [[
            'name'     => 'heading',
            'variants' => [
                'heading:sm' => 'Flipsite Heading',
                'heading:md' => 'Flipsite Heading',
                'heading'    => 'Flipsite Heading',
                'heading:lg' => 'Flipsite Heading',
            ],
        ]];
    }
}
