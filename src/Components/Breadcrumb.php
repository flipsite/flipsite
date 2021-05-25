<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Breadcrumb extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\PathTrait;

    protected string $type = 'nav';

    public function build(array $data, array $style, array $flags) : void
    {
        $this->addStyle($style['container'] ?? []);
        $this->setAttribute('aria-label', 'breadcrumb');
        $separator = $data['separator'] ?? '/';
        unset($data['separator']);
        $keys = array_keys($data);
        $keys = array_reverse($keys);
        $last = $keys[0];
        foreach ($data as $url => $item) {
            if (is_string($item)) {
                $item = [
                    'text' => $item,
                    'url'  => $url,
                ];
            }
            $a = $this->builder->build('a', $item, $style);
            if (null !== $a) {
                $this->addChild($a);
                if ($url === $last) {
                    $a->addStyle($style['current'] ?? []);
                    $a->setAttribute('aria-current', 'page');
                } else {
                    $span = new Element('span', true);
                    $span->addStyle($style['separator'] ?? []);
                    $span->setContent($separator);
                    $this->addChild($span);
                }
            }
        }
    }
}
