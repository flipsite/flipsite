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
        $this->element->addStyle($data->getStyle('preview'));
        if ($url) {
            $this->element->addStyle($data->getStyle('link'));
            $this->element->setAttribute('data-href', $url);
        }

        $container = new Element('div');
        $container->addStyle($data->getStyle('container'));
        $this->element->addChild($container);

        $section = new Element('div');
        $sectionData = $this->sectionBuilder->getExample($data->get('section'));
        $sectionData['style'] = $this->sectionBuilder->getInheritedStyle($sectionData['style'] ?? '');
        $container->addChild($section);

        $resize = new Element('div');
        $resize->addChild($this->sectionBuilder->getSection($sectionData));
        $resize->setAttribute('data-contain', true);

        $section->addChild($resize);

        $raw = $this->sectionBuilder->getExample($data->get('section'));
        $json = new Element('div', true);
        $json->addStyle(['display'=>'hidden']);
        $json->setAttribute('data-type', 'json');
        $json->setContent(json_encode($raw));
        $this->element->addChild($json);

        $this->builder->dispatch(new Event('ready-script', 'section-preview', file_get_contents(__DIR__.'/section-preview.js')));
    }

    public function render(int $indentation = 2, int $level = 0, bool $oneline = false) : string
    {
        return $this->element->render($indentation, $level, $oneline);
    }
}
