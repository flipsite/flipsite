<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Assets\ImageHandler;
use Flipsite\Assets\VideoHandler;
use Flipsite\Components\AbstractElement;
use Flipsite\Components\AbstractComponent;
use Flipsite\Components\ComponentListenerInterface;
use Flipsite\Components\Event;
use Flipsite\Data\SiteDataInterface;
use Flipsite\Data\Slugs;
use Flipsite\EnvironmentInterface;
use Flipsite\Assets\Assets;
use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\DataHelper;
use Flipsite\Utils\Path;
use Flipsite\Utils\ColorHelper;

class ComponentBuilder
{
    private ImageHandler $imageHandler;
    private VideoHandler $videoHandler;
    private array $listeners    = [];
    private array $theme        = [];
    private array $localization = [];
    private Slugs $slugs;

    public function __construct(private EnvironmentInterface $environment, private SiteDataInterface $siteData, private Path $path)
    {
        $this->assets = new Assets($environment->getAssetSources());
        // $this->slugs = $reader->getSlugs();
    }

    public function build(string $type, array|string|int|bool $data, array $parentStyle, array $options): ?AbstractComponent
    {
        if (isset($data['_script'])) {
            $this->handleScripts($data['_script']);
        }
        $flags = explode(':', $type);
        $type  = array_shift($flags);

        $parentType = false;
        if (isset($parentStyle['type']) && $parentStyle['type'] !== $type) {
            $parentType = $parentStyle['type'];
        }

        $style = $this->siteData->getComponentStyle($type);

        $style = ArrayHelper::merge($style, $parentStyle);

        if ($parentType) {
            $parentTypeflags = explode(':', $parentType);
            $parentType      = array_shift($parentTypeflags);
            $parentTypeStyle = $this->siteData->getComponentStyle($parentType);
            $style           = ArrayHelper::merge($parentTypeStyle, $style);
        }
        if (isset($data['_options']['hidden'])) {
            return null;
        }
        if (isset($data['_options']['render'])) {
            if (!$this->handleRenderOptions($data['_options']['render'])) {
                return null;
            }
            unset($data['_options']['render']);
        }

        if (is_array($data) && isset($data['_style'])) {
            // If string, => inherit
            if (is_string($data['_style'])) {
                $data['_style'] = ['inherit' => $data['_style']];
            }

            // Resolve inheritance
            while (isset($data['_style']['inherit'])) {
                $inheritType    = $data['_style']['inherit'];
                unset($data['_style']['inherit']);
                $data['_style'] = ArrayHelper::merge($this->siteData->getComponentStyle($inheritType), $data['_style']);
            }

            $style = ArrayHelper::merge($style, $data['_style']);
            unset($data['_style']);
        }

        $options['appearance'] = $style['appearance'] ?? $options['appearance'];
        unset($style['appearance']);
        if (isset($style['dark'])) {
            $style = \Flipsite\Utils\StyleAppearanceHelper::apply($style, $options['appearance']);
        }

        if (is_array($style)) {
            $type = $style['type'] ?? $type;
            unset($style['type'],$style['section']);
        }

        if (isset($data['_dataSource']) && is_array($data['_dataSource'])) {
            $data = DataHelper::applyData($data, $data['_dataSource'], '_dataSource');
        } elseif (isset($data['_dataSource']) && is_string($data['_dataSource'])) {
            $dataSource          = $this->getDataSource($data['_dataSource']);
            $data['_dataSource'] = [];
            $data                = DataHelper::applyData($data, $dataSource, '_dataSource');
        }

        $class = 'Flipsite\\Components\\'.ucfirst($type);
        if (class_exists($class)) {
            $component = new $class();
        } else {
            return null;
        }

        if (isset($data['_comment'])) {
            if (isset($data['_comment']['before'])) {
                $component->addCommentBefore($data['_comment']['before']);
            }
            if (isset($data['_comment']['after'])) {
                $component->addCommentAfter($data['_comment']['after']);
            }
            unset($data['_comment']);
        }
        if (method_exists($component, 'addBuilder')) {
            $component->addBuilder($this);
        }
        if (method_exists($component, 'addEnvironment')) {
            $component->addEnvironment($this->environment);
        }
        if (method_exists($component, 'addAssets')) {
            $component->addAssets($this->assets);
        }
        if (method_exists($component, 'addPath')) {
            $component->addPath($this->path);
        }
        if (method_exists($component, 'addSiteData')) {
            $component->addSiteData($this->siteData);
        }
        // Handle nav stuff
        if (in_array($data['_action'] ?? '', ['page', 'auto']) && isset($data['_target'])) {
            $options['navState'] = [];
            $page                = $this->path->getPage();
            if (str_starts_with($page, $data['_target'])) {
                $options['navState']['active'] = true;
            }
            if ($data['_target'] === $page) {
                $options['navState']['active'] = true;
            }
        }
        if (count($options['navState'] ?? [])) {
            $style = $this->handleNavStyle($style, $options['navState'] ?? []);
        }

        $data = $component->normalize($data);

        if ($data['_isEmpty'] ?? false) {
            return null;
        }

        $data['_attr'] ??= [];
        if (isset($data['_attr']['_data'])) {
            if (is_string($data['_attr']['_data'])) {
                $tmp = explode(',', $data['_attr']['_data']);
                foreach ($tmp as $pair) {
                    $tmp2 = explode('=', $pair);
                    if (count($tmp2) === 2) {
                        $attr                 = 'data-'.$tmp2[0];
                        $val                  = $tmp2[1];
                        $data['_attr'][$attr] = $val;
                    }
                }
            }
            unset($data['_attr']['_data']);
        }
        if (isset($data['_attr'])) {
            foreach ($data['_attr'] as $attr => $value) {
                $component->setAttribute($attr, $value);
            }
            unset($data['_attr']);
        }
        if (isset($style['tag'])) {
            $component->setTag($style['tag']);
            unset($style['tag']);
        }
        unset($data['_meta'],$data['_name']);

        if (isset($data['_bg'])) {
            $style['background'] ??= [];
            $style['background']['src'] = $data['_bg'];
            unset($data['_bg']);
        }
        if (isset($style['background'])) {
            $this->handleBackground($component, $style['background']);
            unset($style['background']);
        }
        $component->build($data, $style ?? [], $options);
        return $component;
    }

    public function addListener(ComponentListenerInterface $listener): void
    {
        $this->listeners[] = $listener;
    }

    public function dispatch(Event $event): void
    {
        foreach ($this->listeners as $listener) {
            $listener->handleComponentEvent($event);
        }
    }

    private function handleScripts(array $scripts)
    {
        foreach ($scripts['global'] ?? [] as $id => $script) {
            $filepath = $this->environment->getSiteDir().'/'.$script;
            if (file_exists($filepath)) {
                $this->dispatch(new Event('global-script', $id, file_get_contents($filepath)));
            }
        }
        foreach ($scripts['ready'] ?? [] as $id => $script) {
            $filepath = $this->environment->getSiteDir().'/'.$script;
            if (file_exists($filepath)) {
                $this->dispatch(new Event('ready-script', $id, file_get_contents($filepath)));
            }
        }
    }

    private function handleNavStyle(array $style, array $types): array
    {
        $style = ArrayHelper::applyStringCallback($style, function ($str) use ($types) {
            if (strpos($str, 'nav-active:') === false && strpos($str, 'nav-exact:') === false) {
                return $str;
            }
            $res = [];
            $tmp = explode(' ', $str);
            foreach ($tmp as $cls) {
                $active = str_starts_with($cls, 'nav-active:');
                $exact  = str_starts_with($cls, 'nav-exact:');
                if (count($types) === 0 && !$active && !$exact) {
                    $res[] = $cls;
                }
                if (isset($types['active']) && $active) {
                    $res[] = str_replace('nav-active:', '', $cls);
                }
                if (isset($types['exact']) && $exact) {
                    $res[] = str_replace('nav-exact:', '', $cls);
                }
            }
            $res = array_unique($res);
            return implode(' ', $res);
        });
        return $style;
    }

    private function handleRenderOptions(array $options): bool
    {
        if (isset($options['hasSubpages'])) {
            if (!$this->siteData->getSlugs()->hasSubpages($options['hasSubpages'])) {
                return false;
            }
        }
        if (isset($options['isPage'])) {
            $pages       = explode(',', trim(str_replace(' ', '', $options['isPage'])));
            $currentPage = $this->path->getPage();
            foreach ($pages as $page) {
                if ($currentPage === $page) {
                    return true;
                }
                if (str_ends_with($page, '*')) {
                    $page = trim($page, '*');
                    if (str_starts_with($currentPage, $page)) {
                        return true;
                    }
                }
            }
            return false;
        }
        if (isset($options['notPage'])) {
            $pages       = explode(',', trim(str_replace(' ', '', $options['notPage'])));
            $currentPage = $this->path->getPage();
            foreach ($pages as $page) {
                if ($currentPage === $page) {
                    return false;
                }
                if (str_ends_with($page, '*')) {
                    $page = trim($page, '*');
                    if (str_starts_with($currentPage, $page)) {
                        return false;
                    }
                }
            }
            return true;
        }
        return true;
    }

    private function handleBackground(AbstractElement &$element, array $style): void
    {
        $src      = $style['src'] ?? false;
        $gradient = $this->parseThemeColors($style['gradient'] ?? '');
        $options  = $style['options'] ?? [];
        $options['width'] ??= 512;
        $options['srcset'] ??= ['1x', '2x'];
        $options['webp'] ??= true;
        $style['position'] ??= 'bg-center';
        $style['size'] ??= 'bg-cover';
        $style['repeat'] ??= 'bg-no-repeat';
        unset($style['src'],$style['gradient'],$style['options']);
        if ($src) {
            $imageAttributes = $this->assets->getImageAttributes($src, $options);
            if (strlen($gradient)) {
                $gradient .= ',';
            }
            // SVG
            if (str_ends_with($src, '.svg')) {
                $element->setAttribute('style', 'background-image:'.$gradient.'url('.$imageAttributes->getSrc().');');
            } else {
                $srcset = $imageAttributes->getSrcset('url');
                if ($srcset) {
                    $element->setAttribute('style', 'background-image:'.$gradient.'-webkit-image-set('.$srcset.')');
                }
            }
            if (($style['options']['loading'] ?? '') === 'eager') {
                $this->builder->dispatch(new Event('preload', 'background', $imageAttributes));
            }
        } elseif ($gradient) {
            $element->setAttribute('style', 'background-image:'.$gradient);
            unset($style['options']);
        } else {
            unset($style['options'], $style['position'], $style['size'], $style['repeat']);
        }
        foreach ($style as $attr => $val) {
            $element->addStyle(['bg.'.$attr => $val]);
        }
    }

    private function parseThemeColors(string $gradient) : string
    {
        if (!strlen($gradient)) {
            return $gradient;
        }
        $colors          = $this->siteData->getColors();
        $colors['white'] = '#ffffff';
        $colors['black'] = '#000000';
        return ColorHelper::parseAndReplace($gradient, $colors);
    }

    private function getDataSource(string $dataSourceString) : array
    {
        if (str_starts_with($dataSourceString, '${content.')) {
            $dataSourceString = substr($dataSourceString, 10, strlen($dataSourceString) - 11);
        }
        $tmp          = explode('.', $dataSourceString);
        $collectionId = $tmp[0];
        $itemId       = intval($tmp[1]);

        $collection = $this->siteData->getCollection($collectionId);
        if (!$collection) {
            return null;
        }
        $item = $collection->getItem($itemId);
        if ($item) {
            return $item->jsonSerialize();
        }
        return [];
    }
}
