<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Breadcrumb extends AbstractGroup
{
    //use Traits\BuilderTrait;
    use Traits\PathTrait;
    use Traits\SiteDataTrait;

    protected string $tag = 'nav';

    public function normalize(string|int|bool|array $data) : array
    {
        return $data;
    }

    public function build(array $data, array $style, array $options) : void
    {
        $page    = $this->path->getPage();
        $current = $this->siteData->getPageName($page, $this->path->getLanguage());
        $links   = $this->getLinks($page);

        foreach ($links as $i => $link) {
            $link['_style']         = $style['links'] ?? [];
            $link['_style']['type'] = 'group';
            $data['group:'.$i]      = $link;

            $data['svg:'.$i] = [
                'value'  => $data['separator'] ?? [],
                '_style' => $style['separator'] ?? []
            ];
        }
        unset($data['separator'], $style['links'], $style['separator']);

        parent::build($data, $style, $options);

        $span = new Element('span');
        $span->setContent($current);
        $this->addChild($span);
    }

    private function getLinks(string $page) : array
    {
        if ($page === 'home') {
            return [];
        }
        $links   = [];
        $parts   = explode('/', $page);
        array_pop($parts);
        while (count($parts)) {
            $linkPage = implode(',', $parts);
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

    // {
    //     $this->addStyle($style);
    //     $items = $data['items'] ?? [];
    //     unset($data['items']);

    //     $separator = ['text' => '/'];
    //     if (isset($data['separator'])) {
    //         $separator = $data['separator'];
    //         unset($data['separator']);
    //     }

    //     foreach ($items as $i => $item) {
    //         $item['style'] = ArrayHelper::merge($style['items'] ?? [], $item['style'] ?? []);
    //         if (isset($item['active']) && $item['active'] && isset($style['active'])) {
    //             unset($item['active']);
    //             if (!isset($item['style'])) {
    //                 $item['style'] = [];
    //             }
    //             $item['style'] = ArrayHelper::merge($item['style'], $style['active'] ?? null);
    //         }
    //         $data['a:'.$i] = $item;
    //         if ($i !== count($items) - 1) {
    //             $data['separator:'.$i] = $separator;
    //         }
    //     }
    //     unset($style['items'], $style['active']);

    //     parent::build($data, $style, $options);
    // }
}
