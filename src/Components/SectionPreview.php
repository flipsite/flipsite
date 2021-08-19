<?php

declare(strict_types=1);

namespace Flipsite\Components;

class SectionPreview extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\SectionBuilderTrait;
    private AbstractElement $element;

    public function with(ComponentData $data) : void
    {
        $this->element = new Element('div');
        $this->element->addStyle($data->getStyle('container'));

        $section = new Element('div');
        $section->addStyle($data->getStyle('section'));
        $this->element->addChild($section);

        $resize = new Element('div');
        $resize->addChild($this->sectionBuilder->getSection($data->get()));
        $resize->setAttribute('data-contain', true);

        $section->addChild($resize);

        $this->builder->dispatch(new Event('ready-script', 'section-preview', file_get_contents(__DIR__.'/section-preview.js')));
    }

    public function render(int $indentation = 2, int $level = 0, bool $oneline = false) : string
    {
        return $this->element->render($indentation, $level, $oneline);
    }
}
