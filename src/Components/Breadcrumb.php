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

        $homeIcon = $component->getDataValue('home', true);
        if (isset($homeIcon['value'])) {
            $links[0]['sr'] = $links[0]['text'];
            unset($links[0]['text']);
            $links[0]['icon']           = $homeIcon;
            $links[0]['icon']['_style'] = $style['home'] ?? [];
        }

        $icon = $component->getDataValue('separator', true);
        if (is_string($icon)) {
            $icon = ['value' => $icon];
        }

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
                'icon',
                $icon,
                $style['separator']
            );
            $component->addChild($separatorComponentData);
        }
        $inherited->setParent($component->getId(), $component->getType());
        parent::build($component, $inherited);

        $span = new Element('span');
        $span->addStyle($style['current'] ?? []);
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
