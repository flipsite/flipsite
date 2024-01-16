<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Sitemap extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\SiteDataTrait;
    use Traits\PathTrait;

    protected string $tag   = 'ul';

    public function build(array $data, array $style, string $appearance) : void
    {
        $pages = new \Adbar\Dot();
        foreach ($this->siteData->getSlugs()->getPages() as $page) {
            $pages->add(str_replace('/', '.', $page), $page);
        }
        $level = 0;
        $this->addStyle($style);
        $level = 0;
        foreach ($pages as $page => $data) {
            $this->addChild($this->buildItem($level, $page, $data, $style, $appearance));
        }
    }

    private function buildItem(int $level, string $page, array|string $data, array $style, string $appearance) : AbstractElement
    {
        $li = new Element('li');
        $li->addStyle($style['li'] ?? []);
        $li->addStyle($style['li'.$level] ?? []);

        $a = $this->builder->build('a', [
            'url'  => $page,
            'text' => $this->reader->getPageName($page, $this->path->getLanguage())
        ], ArrayHelper::merge($style['a'] ?? [], $style['a'.$level] ?? []), $appearance);

        $li->addChild($a);

        if (is_array($data)) {
            $ul = new Element('ul');
            $ul->addStyle(($style['ul'.($level + 1)]) ?? []);
            foreach ($data as $childPage => $pageData) {
                $ul->addChild($this->buildItem($level + 1, $page.'/'.$childPage, $pageData, $style, $appearance));
            }
            $li->addChild($ul);
        }
        return $li;
    }
}
