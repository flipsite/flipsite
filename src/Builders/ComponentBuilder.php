<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Assets\ImageHandler;
use Flipsite\Assets\VideoHandler;
use Flipsite\Components\AbstractElement;
use Flipsite\Components\AbstractComponent;
use Flipsite\Data\SiteDataInterface;
use Flipsite\Data\Slugs;
use Flipsite\EnvironmentInterface;
use Flipsite\Assets\Assets;
use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\Path;
use Flipsite\Utils\ColorHelper;
use Flipsite\Style\Style;
use Flipsite\Utils\Filter;
use Flipsite\Utils\Plugins;

class ComponentBuilder
{
    use \Flipsite\Traits\ComponentTypeTrait;
    private ImageHandler $imageHandler;
    private VideoHandler $videoHandler;
    private array $listeners    = [];
    private array $theme        = [];
    private array $localization = [];
    private Slugs $slugs;
    private array $recursiveData = [];
    private array $sharedData    = [];

    public function __construct(private EnvironmentInterface $environment, private SiteDataInterface $siteData, private Path $path, private ?Plugins $plugins = null)
    {
        $this->assets = new Assets($environment->getAssetSources());
    }

    public function build(string $type, array|string|int|bool $data, array $parentStyle, array $options): ?AbstractComponent
    {
        if (str_starts_with($type, '_')) {
            return null;
        }
        if (($options['recursionDepth'] ?? 0) > 50) {
            return null;
        }
        if (is_array($data)) {
            if ($data['_options']['recursiveId'] ?? false) {
                $recursiveId = $data['_options']['recursiveId'];
                unset($data['_options']['recursiveId']);
                $this->recursiveData[$recursiveId] = [
                    'type'        => $type,
                    'data'        => $data,
                    'parentStyle' => $parentStyle
                ];
            };
            if ($data['_options']['recursiveContent'] ?? false) {
                $recursiveId = $data['_options']['recursiveContent'];
                unset($data['_options']['recursiveContent']);
                $recursive                   = $this->recursiveData[$recursiveId];
                $recursiveType               = $recursive['type'];
                $data[$recursiveType]        = $recursive['data'];
                $parentStyle[$recursiveType] = $recursive['parentStyle'];
                $options['recursionDepth'] ??= 0;
                $options['recursionDepth']++;
            }
        } elseif (!is_array($data)) {
            $data = ['value' => $data];
        }

        if (isset($data['_script'])) {
            $this->handleScripts($data['_script']);
        }

        $type = $this->getComponentType($type);
        if (!$type) {
            return null;
        }
        $class = 'Flipsite\\Components\\' . ucfirst($type);

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
        if ($data['_options']['hidden'] ?? false) {
            return null;
        }

        if (is_array($data) && isset($data['_style'])) {
            // If string, => inherit
            if (is_string($data['_style'])) {
                $data['_style'] = ['inherit' => $data['_style']];
            }

            // Resolve inheritance
            while (isset($data['_style']['inherit'])) {
                $inheritType   = $data['_style']['inherit'];
                unset($data['_style']['inherit']);
                $data['_style'] = ArrayHelper::merge($this->siteData->getComponentStyle($inheritType), $data['_style']);
            }

            $style = ArrayHelper::merge($style, $data['_style']);
            unset($data['_style']);
        }

        $options['appearance'] = $style['appearance'] ?? $options['appearance'];
        unset($style['appearance']);

        if (is_array($style)) {
            $type = $style['type'] ?? $type;
            unset($style['type'],$style['section']);
        }

        if (isset($data['_dataSource'])) {
            $dataSource = is_array($data['_dataSource']) ? $data['_dataSource'] : $this->getDataSource($data['_dataSource']);
            unset($data['_dataSource']);
            foreach ($dataSource as $key => $value) {
                $options['parentDataSource'][$key] = $value;
            }
        }

        if (isset($style['transitionDelayStep']) && isset($data['_repeatIndex'])) {
            $multiplier = intval($data['_repeatIndex']);
            $style['transitionDelay'] ??= 'delay-0';
            $delay        = new Style($style['transitionDelay'], 'delay-');
            $step         = new Style($style['transitionDelayStep'] ?? null, 'delay-step-');
            $variants     = $delay->getVariants();
            foreach ($variants as $variant) {
                $initialValue = intval($step->getValue($variant));
                $delay->setValue($variant, $multiplier * $initialValue);
            }
            $style['transitionDelay'] = $delay->encode();
            unset($style['transitionDelayStep']);
        }

        $component = new $class();

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

        $style = ArrayHelper::merge($component->getDefaultStyle(), $style);
        $style = \Flipsite\Utils\StyleAppearanceHelper::apply($style, $options['appearance']);

        if ($options['parentDataSource']) {
            $replaced = [];
            $data     = $component->applyData($data, $options['parentDataSource'] ?? [], $replaced);
            if (in_array('{copyright.year}', $replaced)) {
                $this->dispatch(new Event('ready-script', 'copyright', file_get_contents(__DIR__.'/../../js/dist/copyright.min.js')));
            }
        }

        // Handle nav stuff
        if (in_array($data['_action'] ?? '', ['page', 'auto']) && isset($data['_target'])) {
            $options['navState'] = ['active' => false, 'exact' => false];
            $page                = $this->path->getPage();
            if (str_starts_with($page, $data['_target'])) {
                $options['navState']['active'] = true;
            }
            if ($data['_target'] === $page) {
                $options['navState']['exact'] = true;
            }
        }

        if (count($options['navState'] ?? [])) {
            $style = $this->handleNavStyle($style, $options['navState'] ?? []);
        }

        $data = $component->normalize($data);

        if (isset($data['_options']['render'])) {
            if (!$this->handleRenderOptions($data['_options']['render'])) {
                return null;
            }
            unset($data['_options']['render']);
        }

        if (isset($data['default']) && (!isset($data['value']) || !$data['value'] || preg_match('/\{[a-zA-Z]+\}$/', $data['value']))) {
            $data['value'] = $data['default'];
            unset($data['default']);
        }

        if ($data['_isEmpty'] ?? false) {
            return null;
        }

        if ($this->plugins) {
            $data = $this->plugins->run('beforeComponentBuild', $data, $options);
            if (isset($data['__options'])) {
                $options = $data['__options'];
                unset($data['__options']);
            }
        }
        $data['_attr'] ??= [];
        if (isset($data['_attr']['_data'])) {
            if (is_string($data['_attr']['_data'])) {
                $tmp = ArrayHelper::decodeJsonOrCsv($data['_attr']['_data']);
                foreach ($tmp as $pair) {
                    $tmp2 = explode('=', $pair);
                    if (count($tmp2) === 2) {
                        $attr                 = 'data-' . $tmp2[0];
                        $val                  = $tmp2[1];
                        $data['_attr'][$attr] = $val;
                    }
                }
            }
            unset($data['_attr']['_data']);
        }
        $style = $this->handleStyleStates($style, $data);

        if (isset($options['parentDataSource'])) {
            $style = $this->handleApplyStyleData($style, $options['parentDataSource']);
        }

        if (isset($style['tag'])) {
            $component->setTag($style['tag']);
            unset($style['tag']);
        }
        unset($data['_meta'], $data['_name']);

        if (isset($data['_bg'])) {
            $style['background'] ??= [];
            $style['background']['src'] = $data['_bg'];
            unset($data['_bg']);
        }
        if (isset($style['width']) && strpos($style['width'], 'w-scroll') !== false) {
            $data['_attr'] ??= [];
            $data['_attr']['data-scroll-progress-width'] = true;
            $this->dispatch(new Event('ready-script', 'scroll-progress', file_get_contents(__DIR__.'/../../js/dist/scroll-progress.min.js')));
        }
        if (($style['textScale'] ?? '') === 'text-scale') {
            $data['_attr'] ??= [];
            $data['_attr']['data-text-scale'] = true;
            $this->dispatch(new Event('ready-script', 'text-scale', file_get_contents(__DIR__.'/../../js/dist/text-scale.min.js')));
        }
        unset($style['textScale']);
        if (isset($style['background'])) {
            $style['background'] = $this->handleApplyStyleData($style['background'], $options['parentDataSource']);
            $style['background'] = $this->handleStyleStates($style['background'], $data);
            $this->handleBackground($component, $style['background']);
            unset($style['background']);
        }
        unset($options['navState']);
        if (isset($data['_attr'])) {
            foreach ($data['_attr'] as $attr => $value) {
                if (!is_string($value) || (!str_starts_with($value, '{') && !str_ends_with($value, '}'))) {
                    $component->setAttribute($attr, $value);
                }
            }
            unset($data['_attr']);
        }
        // if (isset($options['_original'])) {
        //     if (is_string($options['_original'])) {
        //         $options['original'] = ['value' => $options['original']];
        //     }
        // }
        // $component->setAttribute('_original', null);
        $component->build($data, $style ?? [], $options);

        if ($this->plugins) {
            $component = $this->plugins->run('afterComponentBuild', $component);
        }
        return $component;
    }

    public function addListener(EventListenerInterface $listener): void
    {
        $this->listeners[] = $listener;
    }

    public function dispatch(Event $event): void
    {
        foreach ($this->listeners as $listener) {
            $listener->handleEvent($event);
        }
    }

    public function shareData(string $id, array $data): void
    {
        $this->sharedData[$id] = $data;
    }

    public function getSharedData(string $id): array
    {
        return $this->sharedData[$id] ?? [];
    }

    private function handleScripts(array $scripts)
    {
        foreach ($scripts['global'] ?? [] as $id => $script) {
            $filepath = $this->environment->getSiteDir() . '/' . $script;
            if (file_exists($filepath)) {
                $this->dispatch(new Event('global-script', $id, file_get_contents($filepath)));
            }
        }
        foreach ($scripts['ready'] ?? [] as $id => $script) {
            $filepath = $this->environment->getSiteDir() . '/' . $script;
            if (file_exists($filepath)) {
                $this->dispatch(new Event('ready-script', $id, file_get_contents($filepath)));
            }
        }
    }

    private function handleApplyStyleData(array $style, array $variables): array
    {
        foreach ($style as $key => &$value) {
            if (is_string($value) && in_array($key, ['textColor', 'borderColor', 'fill', 'gradient', 'color'])) {
                $matches = [];
                preg_match_all('/\{[^{}]+\}/', $value, $matches);
                foreach ($matches[0] as $match) {
                    $key = trim($match, '{}');
                    if (isset($variables[$key])) {
                        $value = str_replace($match, $variables[$key], $value);
                    }
                }
            }
        }
        return $style;
    }

    private function isContainer(string $type): bool
    {
        return in_array($type, ['container', 'logo', 'button', 'link', 'toggle', 'question', 'nav', 'social']);
    }

    private function handleStyleStates(array $style, array &$data)
    {
        foreach ($style as $attr => &$value) {
            if (is_string($value)) {
                $update  = false;
                $setting = new Style($value);
                if ($setting->hasVariant('open')) {
                    $this->dispatch(new Event('global-script', 'toggle', file_get_contents(__DIR__ . '/../../js/dist/toggle.min.js')));
                    $setting->removeValue('!open');
                    $open    = $setting->removeValue('open');
                    $notOpen = $setting->getValue();
                    if (isset($data['_attr']['data-toggle'])) {
                        $data['_attr']['data-toggle'] .= ' '.$open.' '.$notOpen;
                    } else {
                        $data['_attr'] ??= [];
                        $data['_attr']['data-toggle'] = $open.' '.$notOpen;
                    }
                    $update = true;
                }
                foreach (['xs', 'sm', 'md', 'lg', 'xl', '2xl'] as $bp) {
                    if ($setting->hasVariant($bp.':open')) {
                        $this->dispatch(new Event('global-script', 'toggle', file_get_contents(__DIR__ . '/../../js/dist/toggle.min.js')));
                        $open    = $bp.':'.$setting->removeValue($bp.':open');
                        $notOpen = $bp.':'.$setting->getValue($bp);
                        if (isset($data['_attr']['data-toggle'])) {
                            $data['_attr']['data-toggle'] .= ' '.$open.' '.$notOpen;
                        } else {
                            $data['_attr'] ??= [];
                            $data['_attr']['data-toggle'] = $open.' '.$notOpen;
                        }
                        $update = true;
                    }
                }
                if ($setting->hasVariant('offscreen')) {
                    $this->dispatch(new Event('ready-script', 'anim', file_get_contents(__DIR__ . '/../../js/dist/anim.min.js')));
                    $animate      = $setting->removeValue('offscreen');
                    $notAnimate   = $setting->getValue();
                    if (isset($data['_attr']['data-animate'])) {
                        $data['_attr']['data-animate'] .= ' '.$animate.' '.$notAnimate;
                    } else {
                        $data['_attr'] ??= [];
                        $data['_attr']['data-animate'] = $animate.' '.$notAnimate;
                    }
                    $update = true;
                }
                if ($setting->hasVariant('selected')) {
                    $selected    = $setting->removeValue('selected');
                    $notSelected = $setting->getValue();
                    if (isset($data['_attr']['data-selected'])) {
                        $data['_attr']['data-selected'] .= ' '.$selected.' '.$notSelected;
                    } else {
                        $data['_attr'] ??= [];
                        $data['_attr']['data-selected'] = $selected.' '.$notSelected;
                    }
                    $update = true;
                }
                if ($update) {
                    $value = $setting->encode();
                }
            }
        }
        return $style;
    }

    private function handleNavStyle(array $style, array $types): array
    {
        $types['active'] ??= false;
        $types['exact'] ??= false;
        $style = ArrayHelper::applyStringCallback($style, function ($str) use ($types) {
            if (strpos($str, 'nav-active:') === false && strpos($str, 'nav-exact:') === false) {
                return $str;
            }
            $res = [];
            $tmp = explode(' ', $str);
            foreach ($tmp as $cls) {
                $active = strpos($cls, 'nav-active:') !== false;
                $exact  = strpos($cls, 'nav-exact:') !== false;
                if (!$types['active'] && !$types['exact'] && !$active && !$exact) {
                    $res[] = $cls;
                }
                if ($types['active'] && $active) {
                    $res[] = str_replace('nav-active:', '', $cls);
                }

                if ($types['exact'] && $exact) {
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
            $pages       = ArrayHelper::decodeJsonOrCsv($options['isPage']);
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
            $pages       = ArrayHelper::decodeJsonOrCsv($options['notPage']);
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
        if (isset($options['isLanguage'])) {
            $languages       = ArrayHelper::decodeJsonOrCsv($options['isLanguage']);
            $currentLanguage = (string)$this->path->getLanguage();
            foreach ($languages as $language) {
                if ($currentLanguage === $language) {
                    return true;
                }
            }
            return false;
        }
        if (isset($options['notLanguage'])) {
            $languages       = ArrayHelper::decodeJsonOrCsv($options['notLanguage']);
            $currentLanguage = (string)$this->path->getLanguage();
            foreach ($languages as $language) {
                if ($currentLanguage === $language) {
                    return false;
                }
            }
            return true;
        }

        if (isset($options['filterType']) || isset($options['filter']) || isset($options['filterPattern'])) {
            $options['filterFieldValue'] ??= null;
            if (is_string($options['filterFieldValue']) && preg_match('/^\{[a-zA-Z\.]+\}$/', $options['filterFieldValue'])) {
                $options['filterFieldValue'] = null;
            }
            $filter = new Filter($options['filterType'] ?? 'or', $options['filter'] ?? null, $options['filterPattern'] ?? null);
            return $filter->filterValue($options['filterFieldValue']);
        }

        return true;
    }

    public function handleBackground(AbstractElement &$element, array $style): void
    {
        $src = $style['src'] ?? false;
        if ($src && str_starts_with($src, '{') && str_ends_with($src, '}')) {
            $src = false;
        }
        if (isset($style['gradient']) && str_starts_with($style['gradient'], '{') && str_ends_with($style['gradient'], '}')) {
            unset($style['gradient']);
        }
        if (isset($style['color']) && str_starts_with($style['color'], 'bg-{') && str_ends_with($style['color'], '}')) {
            unset($style['color']);
        }

        $gradient = $this->parseThemeColors($style['gradient'] ?? '');
        $options  = $style['options'] ?? [];
        $options['loading'] ??= 'lazy';
        $options['srcset'] ??= ['1x', '2x'];
        $options['webp'] ??= true;
        unset($style['src'],$style['gradient'],$style['options']);
        if ($src) {
            if (isset($options['resolutions'])) {
                $mediaQueries = [
                    'portrait'  => '(max-width: 800px) and (orientation: portrait)',
                    'landscape' => '(max-width: 800px) and (orientation: landscape)',
                    'laptop'    => '(min-width: 801px) and (max-width: 1200px)',
                    'desktop'   => '(min-width: 1201px) and (-webkit-min-device-pixel-ratio: 2), (min-width: 1201px) and (min-resolution: 192dpi)',
                ];
                $bgClass = 'bgimg-'.substr(md5($src), 0, 6);
                $element->addStyle(['bgClass' => $bgClass]);
                $resolutions = json_decode($options['resolutions'], true);
                $css         = ['global' => []];
                if (strlen($gradient)) {
                    $gradient .= ',';
                }
                foreach ($resolutions as $type => $size) {
                    $media       = $mediaQueries[$type] ?? false;
                    $css[$media] = ['.'.$bgClass => []];
                    if ($media) {
                        $imageAttributes                               = $this->assets->getImageAttributes($src, ['width' => intval($size['w']), 'height' => intval($size['h'])]);
                        $resSrc                                        = $imageAttributes->getSrc();
                        $css[$media]['.'.$bgClass]['background-image'] = $gradient.'url(' .$resSrc. ')';
                        if ($type === 'laptop') {
                            $css['global']['.'.$bgClass]['background-image'] = $gradient.'url(' .$resSrc. ')';
                        }
                        if ($options['loading'] === 'eager') {
                            $this->dispatch(new Event('preload', 'custom', [
                                'as'    => 'image',
                                'href'  => $resSrc,
                                'media' => $media,
                            ]));
                        }
                    }
                }
                $this->dispatch(new Event('background-image', '', $css));
            } else {
                $options['width'] ??= 512;
                $imageAttributes = $this->assets->getImageAttributes($src, $options);
                if (strlen($gradient)) {
                    $gradient .= ',';
                }
                // SVG
                if (str_ends_with($src, '.svg')) {
                    $element->setAttribute('style', 'background-image:' . $gradient . 'url(' . $imageAttributes->getSrc() . ');');
                } elseif ($imageAttributes && $srcset = $imageAttributes->getSrcset('url')) {
                    if ('eager' === $options['loading']) {
                        $element->setAttribute('style', 'background-image:' . $gradient . '-webkit-image-set(' . $srcset . ')');
                    } else {
                        $element->setAttribute('data-lazybg', $gradient . '-webkit-image-set(' . $srcset . ')');
                        $this->dispatch(new Event('ready-script', 'lazy', file_get_contents(__DIR__ . '/../../js/dist/lazybg.min.js')));
                    }
                }
                if ($options['loading'] === 'eager') {
                    $this->dispatch(new Event('preload', 'background', $imageAttributes));
                }
            }
        } elseif ($gradient) {
            $element->setAttribute('style', 'background-image:' . $gradient);
            unset($style['options']);
        } else {
            unset($style['options'], $style['position'], $style['size'], $style['repeat']);
        }
        foreach ($style as $attr => $val) {
            $element->addStyle(['bg.' . $attr => $val]);
        }
    }

    private function parseThemeColors(string $gradient): string
    {
        if (!strlen($gradient)) {
            return $gradient;
        }
        $colors          = $this->siteData->getColors();
        $colors['white'] = '#ffffff';
        $colors['black'] = '#000000';
        return ColorHelper::parseAndReplace($gradient, $colors);
    }

    private function getDataSource(string $dataSourceString): array
    {
        if (str_starts_with($dataSourceString, '${content.')) {
            $dataSourceString = substr($dataSourceString, 10, strlen($dataSourceString) - 11);
        }
        $tmp          = explode('.', $dataSourceString);
        if (count($tmp) !== 2) {
            return [];
        }
        $collectionId = $tmp[0];
        $itemId       = intval($tmp[1]);

        $collection = $this->siteData->getCollection($collectionId, $this->path->getLanguage());
        if (!$collection) {
            return [];
        }
        $item = $collection->getItem($itemId);
        if ($item) {
            return $item->jsonSerialize();
        }
        return [];
    }
}
