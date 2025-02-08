<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\YamlComponentData;
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
            $linkComponentData = new YamlComponentData(
                null,
                null,
                'container',
                $link,
                $style['links'] ?? []
            );
            $component->addChild($linkComponentData);

            $separatorComponentData = new YamlComponentData(
                null,
                null,
                'svg',
                ['src' => $icon],
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
