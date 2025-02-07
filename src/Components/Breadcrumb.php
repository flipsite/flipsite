<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\ComponentData;
use Flipsite\Data\InheritedComponentData;

final class Breadcrumb extends AbstractGroup
{
    //use Traits\BuilderTrait;
    use Traits\PathTrait;
    use Traits\SiteDataTrait;

    protected string $tag = 'nav';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $style    = $component->getStyle();
        $page     = $this->path->getPage();
        $current  = $this->siteData->getPageName($page, $this->path->getLanguage());
        $links    = $this->getLinks($page);
        $icon     = $component->getDataValue('separator', true);

        foreach ($links as $i => $link) {
            $textComponentData = new ComponentData(
                $component->getId().'.text'.$i,
                'text',
                ['value' => $link['text']],
            );
            unset($link['text']);
            $linkComponentData = new ComponentData(
                $component->getId().'.link'.$i,
                'container',
                $link,
                $style['links'] ?? []
            );
            $linkComponentData->addChild($textComponentData);
            $component->addChild($linkComponentData);

            $separatorComponentData = new ComponentData(
                $component->getId().'.separator'.$i,
                'svg',
                ['value' => $icon],
                $style['separator']
            );
            $component->addChild($separatorComponentData);
        }

        parent::build($component, $inherited);

        $span = new Element('span');
        $span->setContent($current);
        $this->addChild($span);
    }

    private function getLinks(string $page): array
    {
        if ($page === 'home') {
            return [];
        }
        $links   = [];
        $parts   = explode('/', $page);
        array_pop($parts);
        while (count($parts)) {
            $linkPage = implode('/', $parts);
            if ($this->siteData->getSlugs()->isPage($linkPage)) {
                $links[]  = [
                    '_target'  => $linkPage,
                    '_action'  => 'page',
                    'text'     => $this->siteData->getPageName($linkPage, $this->path->getLanguage())
                ];
            }
            array_pop($parts);
        }
        $links[] = [
            '_target'  => 'home',
            '_action'  => 'page',
            'text'     => $this->siteData->getPageName('home', $this->path->getLanguage())
        ];
        return array_reverse($links);
    }
}
