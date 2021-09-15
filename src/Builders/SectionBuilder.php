<?php

declare(strict_types=1);

namespace Flipsite\Builders;

use Flipsite\Components\AbstractElement;
use Flipsite\Components\Element;
use Flipsite\Components\Event;
use Flipsite\Enviroment;
use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\StyleAppearanceHelper;
use Flipsite\Sections\AbstractSectionFactory;
use Flipsite\Data\Reader;
use Flipsite\Sections\SectionFactory;

class SectionBuilder
{
    private Enviroment $enviroment;
    private Reader $reader;
    private ComponentBuilder $componentBuilder;
    private array $factories = [];
    private ?array $theme;
    private array $defaultSectionStyle = [];
    private array $inheritedStyle = [];

    public function __construct(Enviroment $enviroment, Reader $reader, ComponentBuilder $componentBuilder)
    {
        $this->enviroment       = $enviroment;
        $this->reader           = $reader;
        $this->componentBuilder = $componentBuilder;
        $this->theme            = $reader->get('theme');
        $this->defaultSectionStyle = $this->componentBuilder->getComponentStyle('section');
        $this->addFactory(new SectionFactory());
        // TODO implement support for external section factories
        // foreach ($reader->getSectionFactories() as $class) {
        //     $sectionBuilder->addFactory(new $class());
        // }
    }

    private function addFactory(AbstractSectionFactory $factory) : void
    {
        $this->factories[] = $factory;
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
                    if (strpos($script, 'vendor/') === 0) {
                        $filename = $this->enviroment->getVendorDir().'/'.ltrim($script, '/vendor');
                    } else {
                        $filename = $this->enviroment->getSiteDir().'/'.$script;
                    }
                    if (file_exists($filename)) {
                        $this->componentBuilder->dispatch(new Event($type.'-script', $id, file_get_contents($filename)));
                    }
                }
            }
            unset($data['script']);
        }
        $id = $data['id'] ?? null;
        unset($data['id']);

        $style = $data['style'] ?? $data['style:light'] ?? $data['style:dark'] ?? $data['style:auto'] ?? [];
        $appearance = isset($data['style:light']) ? 'light' : null;
        $appearance ??= isset($data['style:dark']) ? 'dark' : null;
        $appearance ??= isset($data['style:auto']) ? 'auto' : null;
        $appearance ??= $this->theme['appearance'];
        $appearance ??= 'light';
        unset($data['style'],$data['style:light'],$data['style:dark'],$data['style:auto']);

        $style = $this->getStyle($style);
        if (isset($style['appearance'])) {
            $appearance = $style['appearance'];
        }

        $style['section'] = StyleAppearanceHelper::apply($style['section'] ?? [], $appearance);
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
                $inherited      = array_shift($tmp);
                $variants       = $tmp;
                $inheritedStyle = $this->getInheritedStyle($inherited);
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
                $style = ArrayHelper::merge($inheritedStyle, $style);
            }
            unset($style['inherit']);
        }

        $style['section'] = ArrayHelper::merge($this->defaultSectionStyle, $style['section'] ?? []);

        if (isset($variants) && isset($style['section']['variants'])) {
            foreach ($variants as $variant) {
                if (isset($style['section']['variants'][$variant])) {
                    $style['section'] = ArrayHelper::merge($style['section'], $style['section']['variants'][$variant]);
                }
            }
        }

        return $style;
    }
    public function getInheritedStyle(string $inherited) : array
    {
        if (isset($this->inheritedStyle[$inherited])) {
            $this->inheritedStyle[$inherited];
        }
        $inheritStyle = [];
        foreach ($this->factories as $factory) {
            $inheritStyle = ArrayHelper::merge($inheritStyle, $factory->getStyle($inherited) ?? []);
        }
        if (isset($this->theme['sections'][$inherited])) {
            $inheritStyle = ArrayHelper::merge($inheritStyle, $this->theme['sections'][$inherited]);
        }

        return $this->inheritedStyle[$inherited] = $inheritStyle;
    }

    public function getExample(string $section) : array
    {
        foreach ($this->factories as $factory) {
            $example = $factory->getExample($section);
            if (is_array($example)) {
                return $example;
            }
        }
        return [];
    }
}
