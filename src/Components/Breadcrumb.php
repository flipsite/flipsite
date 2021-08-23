<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Breadcrumb extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\PathTrait;

    protected string $tag = 'nav';

    public function with(ComponentData $data) : void
    {
        $this->addStyle($data->getStyle('container'));
        $this->setAttribute('aria-label', 'breadcrumb');
        $separator = $data->get('separator', true) ?? '/';
        $keys = array_keys($data->get());
        $keys = array_reverse($keys);
        $last = $keys[0];
        foreach ($data->get() as $url => $item) {
            if (is_string($item)) {
                $item = [
                    'text' => $item,
                    'url'  => $url,
                ];
            }
            $components = $this->builder->build(['a' => $item], ['a' => $data->getStyle()], $data->getAppearance());
            $a = $components[0];
            if ($url === $last) {
                $a->addStyle($data->getStyle('current'));
                $a->setAttribute('aria-current', 'page');
                $this->addChild($a);
            } else {
                $this->addChild($a);
                $span = new Element('span', true);
                $span->addStyle($data->getStyle('separator'));
                $span->setContent($separator);
                $this->addChild($span);
            }
        }
    }
}
