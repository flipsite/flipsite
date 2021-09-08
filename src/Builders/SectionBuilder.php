<?php

declare(strict_types=1);

namespace Flipsite\Builders;

use Flipsite\Components\AbstractElement;
use Flipsite\Components\Element;
use Flipsite\Components\Event;
use Flipsite\Enviroment;
use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\StyleAppearanceHelper;

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
        if (isset($data['root'])) {
            $data = array_merge($data, $data['root']);
            unset($data['root']);
        }
        if (isset($data['script'])) {
            foreach ($data['script'] as $type => $script) {
                if (is_string($script)) {
                    $script = [$script];
                }
                foreach ($script as $id => $script) {
                    $filename = $this->enviroment->getSiteDir().'/'.$script;
                    if (file_exists($filename)) {
                        $this->componentBuilder->dispatch(new Event($type.'-script', $id, file_get_contents($filename)));
                    } else {
                        $filename = $this->enviroment->getVendorDir().'/flipsite/flipsite/docs/'.$script;
                        if (file_exists($filename)) {
                            $this->componentBuilder->dispatch(new Event($type.'-script', $id, file_get_contents($filename)));
                        }
                    }
                }
            }
            unset($data['script']);
        }
        $id = $data['id'] ?? null;
        unset($data['id']);

        $style      = $this->getStyle($data['style'] ?? $data['style:light'] ?? $data['style:dark'] ?? $data['style:auto'] ?? []);
        $appearance = isset($data['style:dark']) ? 'dark' : $this->theme['appearance'] ?? 'light';
        if (isset($style['appearance'])) {
            $appearance = $style['appearance'];
            unset($style['appearance']);
        }
        unset($data['style'],$data['style:dark']);

        $style['section'] = StyleAppearanceHelper::apply($style['section'], $appearance);
        unset($style['section']['dark']);

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
            $container = new Element($style['section']['container']['tag'] ?? 'div');
            unset($style['section']['container']['tag']);
            $container->addStyle($style['section']['container']);
        }

        $section = new Element($style['section']['tag'] ?? 'section');
        unset($style['section']['tag']);
        if ($id) {
            $section->setAttribute('id', $id);
        }
        $section->addStyle($style['section'] ?? []);
        unset($style['section']);

        $components = $this->componentBuilder->build($data, $style, $appearance);
        if ($container) {
            $section->addChild($container);
            $container->addChildren($components);
        } else {
            $section->addChildren($components);
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
        if (isset($style['root'])) {
            $root = $style['root'];
            unset($style['root']);
            $style = ArrayHelper::merge($root, $style);
        }


        if (isset($style['inherit'])) {
            if (is_string($style['inherit'])) {
                $style['inherit'] = [$style['inherit']];
            }
            while (count($style['inherit'])) {
                $inherited      = array_shift($style['inherit']);
                $tmp            = explode(':', $inherited);
                $variants       = isset($tmp[1]) ? explode('+', $tmp[1]) : [];
                $inherited      = $tmp[0];
                $inheritedStyle = $this->theme['style'][$inherited] ?? [];
                $variantFound   = false;
                foreach ($variants as $variant) {
                    if (isset($inheritedStyle['variants'][$variant])) {
                        $inheritedStyle = ArrayHelper::merge($inheritedStyle, $inheritedStyle['variants'][$variant]);
                        $variantFound   = true;
                    }
                }
                if (!$variantFound && isset($inheritedStyle['variants']['DEFAULT'])) {
                    $inheritedStyle = ArrayHelper::merge($inheritedStyle, $inheritedStyle['variants']['DEFAULT']);
                }
                unset($inheritedStyle['variants']);
                $style = ArrayHelper::merge($inheritedStyle, $style);
            }
            unset($style['inherit']);
        }

        $style['section'] = ArrayHelper::merge($this->theme['components']['section'] ?? [], $style['section'] ?? []);
        return $style;
    }
}
