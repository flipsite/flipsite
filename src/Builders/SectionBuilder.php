<?php

declare(strict_types=1);

namespace Flipsite\Builders;

use Flipsite\Components\AbstractElement;
use Flipsite\Components\Element;
use Flipsite\Components\Event;
use Flipsite\Enviroment;
use Flipsite\Utils\ArrayHelper;

class SectionBuilder
{
    private Enviroment $enviroment;
    private ComponentBuilder $componentBuilder;
    private ?array $theme;

    public function __construct(Enviroment $enviroment, ComponentBuilder $componentBuilder, ?array $theme = null)
    {
        $this->enviroment       = $enviroment;
        $this->componentBuilder = $componentBuilder;
        $this->theme            = $theme;
    }

    public function getSection(array $data) : ?AbstractElement
    {
        if (isset($data['script'])) {
            foreach ($data['script'] as $type => $script) {
                foreach ($script as $id => $script) {
                    $filename = $this->enviroment->getSiteDir().'/'.$script;
                    if (file_exists($filename)) {
                        $this->componentBuilder->dispatch(new Event($type.'-script', $id, file_get_contents($filename)));
                    }
                }
            }
            unset($data['script']);
        }
        $id = $data['id'] ?? null;
        unset($data['id']);

        $style = $this->getStyle($data['style'] ?? []);
        unset($data['style']);

        $wrapper = null;
        $empty   = $style['section']['wrapper']['empty'] ?? false;
        if (!$empty && isset($style['section']['wrapper']) && count($style['section']['wrapper'])) {
            $wrapper = new Element($style['section']['wrapper']['type'] ?? 'div');
            unset($style['section']['wrapper']['type']);
            $wrapper->addStyle($style['section']['wrapper']);
            if ($id) {
                $wrapper->setAttribute('id', $id);
                $id = false;
            }
        }

        $container = null;
        $empty     = $style['section']['container']['empty'] ?? false;
        if (!$empty && isset($style['section']['container'])) {
            $container = new Element($style['section']['container']['type'] ?? 'div');
            unset($style['section']['container']['type']);
            $container->addStyle($style['section']['container']);
        }

        $section = new Element($style['section']['type'] ?? 'section');
        unset($style['section']['type']);
        if ($id) {
            $section->setAttribute('id', $id);
        }
        $section->addStyle($style['section'] ?? []);
        unset($style['section']);

        $children = $this->getComponents($data, $style);
        if ($container) {
            $section->addChild($container);
            $container->addChildren($children);
        } else {
            $section->addChildren($children);
        }

        if ($wrapper) {
            $wrapper->addChild($section);
            return $wrapper;
        }
        return $section;
    }

    private function getStyle($style) : array
    {
        if (is_string($style)) {
            $style = ['inherit' => [$style]];
        }
        if (!is_array($style)) {
            $style = [];
        }

        if (isset($style['inherit'])) {
            while (count($style['inherit'])) {
                $inherited = array_shift($style['inherit']);
                $style     = ArrayHelper::merge($this->theme['style'][$inherited] ?? [], $style);
            }
        }

        $style['section'] = ArrayHelper::merge($this->theme['components']['section'] ?? [], $style['section'] ?? []);
        return $style;
    }

    private function getComponents(array $sectionData, array $style) : array
    {
        $components = [];
        foreach ($sectionData as $type => $data) {
            if (null === $data) {
                continue;
            }
            $component = $this->componentBuilder->build($type, $data, $style[$type] ?? []);
            if (null !== $component) {
                $components[] = $component;
            }
        }
        return $components;
    }
}
