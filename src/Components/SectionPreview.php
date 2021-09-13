<?php

declare(strict_types=1);

namespace Flipsite\Components;

class SectionPreview extends AbstractComponent
{
    use Traits\UrlTrait;
    use Traits\BuilderTrait;
    use Traits\SectionBuilderTrait;
    private AbstractElement $element;

    public function with(ComponentData $data) : void
    {
        $external = false;
        $url = $data->get('url', true);
        if (null !== $url) {
            $url = $this->url($url, $external);
            if ('#missing' === $url) {
                $url = null;
            }
        }

        $this->element = new Element('div');
        $this->element->addStyle($data->getStyle('container'));
        if ($url) {
            $this->element->addStyle($data->getStyle('link'));
            $this->element->setAttribute('data-href', $url);
        }

        $section = new Element('div');
        $sectionData = $this->sectionBuilder->getExample($data->get('section'));
        $sectionData['style'] = $this->sectionBuilder->getInheritedStyle($sectionData['style'] ?? '');
        $sectionData['style']['section']['border'] = null;
        $this->element->addChild($section);

        $resize = new Element('div');
        $resize->addChild($this->sectionBuilder->getSection($sectionData));
        $resize->setAttribute('data-contain', true);

        $section->addChild($resize);

        $this->builder->dispatch(new Event('ready-script', 'section-preview', file_get_contents(__DIR__.'/section-preview.js')));
    }

    public function render(int $indentation = 2, int $level = 0, bool $oneline = false) : string
    {
        return $this->element->render($indentation, $level, $oneline);
    }
}
