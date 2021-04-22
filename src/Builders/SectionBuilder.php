<?php

declare(strict_types=1);

namespace Flipsite\Builders;

use Flipsite\Components\AbstractElement;
use Flipsite\Components\Element;
use Flipsite\Components\Event;
use Flipsite\Enviroment;
use Flipsite\Exceptions\SectionDataFormatException;
use Flipsite\Utils\ArrayHelper;

class SectionBuilder
{
    private Enviroment $enviroment;
    private ComponentBuilder $componentBuilder;
    private ?array $sectionStyle;
    private ?array $containerStyle;

    public function __construct(Enviroment $enviroment, ComponentBuilder $componentBuilder, ?array $sectionStyle = null, array $containerStyle = null)
    {
        $this->enviroment       = $enviroment;
        $this->componentBuilder = $componentBuilder;
        $this->sectionStyle     = $sectionStyle;
        $this->containerStyle   = $containerStyle;
    }

    public function getSection(array $data) : ?AbstractElement
    {
        assert(!isset($data['style']) || is_array($data['style']), new SectionDataFormatException($data, 'style', 'array'));
        if (is_array($data['style'] ?? false) && !ArrayHelper::isAssociative($data['style'])) {
            $data['style'] = ArrayHelper::merge(...$data['style']);
        }
        $sectionStyle = $data['style']['section'] ?? [];
        unset($data['style']['section']);
        if (null !== $this->sectionStyle) {
            $sectionStyle = ArrayHelper::merge($this->sectionStyle, $sectionStyle);
        }
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

        $wrapper = null;
        if (isset($sectionStyle['wrapper'])) {
            $wrapper = new Element($sectionStyle['wrapper']['type'] ?? 'div');
            unset($sectionStyle['wrapper']['type']);
            $wrapper->addStyle($sectionStyle['wrapper']);
            if ($id) {
                $wrapper->setAttribute('id', $id);
                $id = false;
            }
        }
        $section = new Element($sectionStyle['type'] ?? 'section');
        unset($sectionStyle['type']);
        if ($id) {
            $section->setAttribute('id', $id);
            $id = false;
        }
        $section->addStyle($sectionStyle);

        $style = $data['style'] ?? [];
        unset($data['style']);

        // Add default root container style
        if (null !== $this->containerStyle) {
            $style['container'] = ArrayHelper::merge($this->containerStyle, $style['container'] ?? []);
        }

        $container = $this->getContainer($data, $style);
        $section->addChild($container);

        if ($wrapper) {
            $wrapper->addChild($section);
            return $wrapper;
        }
        return $section;
    }

    private function getContainer(array $data, array $style) : AbstractElement
    {
        if (!ArrayHelper::isAssociative($data)) {
            $data = ['cols' => $data];
        }
        if (isset($style['template'])) {
            $t = new Template($data, $style['template']);
            unset($style['template']);
            $data = $t->apply();
        }

        $container = new Element($style['container']['type'] ?? 'div');
        if (isset($style['container'])) {
            unset($style['container']['type']);
            $container->addStyle($style['container']);
            unset($style['container']);
        }

        $cols = $this->normalizeCol0($data, ['cols', 'options']);
        if (isset($data['options'])) {
            $cols = $this->applyOptions($cols, $data['options']);
        }
        $colsStyle = $this->normalizeCol0($style, ['container', 'cols', 'colsAll', 'colsOdd', 'colsEven']);
        $colsEven  = ArrayHelper::merge($style['colsAll'] ?? [], $style['colsEven'] ?? []);
        $colsOdd   = ArrayHelper::merge($style['colsAll'] ?? [], $style['colsOdd'] ?? []);
        foreach ($cols as $i => $col) {
            $colStyle = ArrayHelper::merge(0 == $i % 2 ? $colsEven : $colsOdd, $colsStyle[$i] ?? []);
            if (isset($colStyle['template'])) {
                $t = new Template($col, $colStyle['template']);
                unset($colStyle['template']);
                $col = $t->apply();
            }
            if (!ArrayHelper::isAssociative($col)) {
                $col['cols'] = $col;
            }
            if (isset($col['cols'])) {
                if (isset($col['options'])) {
                    $col['cols'] = $this->applyOptions($col['cols'], $col['options']);
                }
                $container->addChild($this->getContainer($col['cols'], $colStyle));
            } else {
                $colContainerStyle = $colStyle['container'] ?? [];
                unset($colStyle['container']);
                $components = $this->getComponents($col, $colStyle);
                if ((count($components) > 1 && count($cols) > 1) || count($colContainerStyle)) {
                    $colContainer = new Element($colContainerStyle['type'] ?? 'div');
                    if (count($colContainerStyle)) {
                        unset($colContainerStyle['type']);
                        $colContainer->addStyle($colContainerStyle);
                    }
                    $colContainer->addChildren($components);
                    $container->addChild($colContainer);
                } else {
                    $container->addChildren($components);
                }
            }
        }

        return $container;
    }

    private function normalizeCol0(array $data, array $notIn) : array
    {
        $col0 = array_filter($data, function ($key) use ($notIn) {
            return !in_array($key, $notIn);
        }, ARRAY_FILTER_USE_KEY);
        $cols = $data['cols'] ?? [];
        if (count($col0)) {
            $cols[0] = ArrayHelper::merge($cols[0] ?? [], $col0);
        }
        ksort($cols);
        return $cols;
    }

    private function getComponents(array $components, array $style) : array
    {
        $list = [];
        foreach ($components as $type => $data) {
            if (null === $data) {
                continue;
            }
            $flags = [];
            $tmp   = explode('|', $type);
            $type  = array_shift($tmp);
            foreach ($tmp as $flag) {
                $flags[$flag] = true;
            }
            $component = $this->componentBuilder->build($type, $data, $style[$type] ?? [], $flags);
            if (null !== $component) {
                $list[] = $component;
            }
        }
        return $list;
    }

    private function applyOptions(array $cols, array $options) : array
    {
        if (isset($options['sort'])) {
            $sort      = explode(' ', $options['sort']);
            $direction = mb_strtolower($sort[1] ?? 'asc');
            $attr      = $sort[0];
            usort($cols, function ($a, $b) use ($attr) {
                return $a[$attr] <=> $b[$attr];
            });
            if ('desc' === $direction) {
                $cols = array_reverse($cols);
            }
        }
        $start = $options['start'] ?? 0;
        $count = $options['count'] ?? null;
        if ($start || $count) {
            $cols = array_slice($cols, $start, $count);
        }
        return $cols;
    }
}
