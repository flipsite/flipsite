<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Assets\ImageHandler;
use Flipsite\Assets\VideoHandler;
use Flipsite\Components\AbstractComponent;
use Flipsite\Components\AbstractComponentFactory;
use Flipsite\Components\ComponentListenerInterface;
use Flipsite\Components\Event;
use Flipsite\Components\Traits\RepeatTrait;
use Flipsite\Data\Reader;
use Flipsite\Enviroment;
use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\Path;
use Flipsite\Utils\CanIUse;
use Psr\Http\Message\ServerRequestInterface as Request;

class ComponentBuilder
{
    use RepeatTrait;

    private ImageHandler $imageHandler;
    private VideoHandler $videoHandler;
    private array $listeners                = [];
    private array $factories                = [];
    private array $theme                    = [];
    private array $localization             = [];

    public function __construct(private Request $request, private Enviroment $enviroment, private Reader $reader, private Path $path, private CanIUse $canIUse)
    {
        $this->imageHandler = new ImageHandler(
            $enviroment->getAssetSources(),
            $enviroment->getImgDir(),
            $enviroment->getImgBasePath(),
        );
        $this->videoHandler = new VideoHandler(
            $enviroment->getSiteDir().'/assets',
            $enviroment->getVideoDir(),
            $enviroment->getVideoBasePath(),
        );
        $this->theme = $reader->get('theme') ?? [];
    }

    public function addFactory(AbstractComponentFactory $factory): void
    {
        $this->factories[] = $factory;
    }

    public function build(string $type, array|string|int|bool $data, array $parentStyle, string $appearance): ?AbstractComponent
    {
        $debug = $type === 'primary';
        if (isset($data['_merge'])) {
            $merge = $data['_merge'];
            unset($data['_merge']);
            $data = ArrayHelper::merge($data, $merge);
        }
        if (isset($data['_script'])) {
            $this->handleScripts($data['_script']);
        }
        $flags = explode(':', $type);
        $type  = array_shift($flags);

        $parentType = false;
        if (isset($parentStyle['type']) && $parentStyle['type'] !== $type) {
            $parentType = $parentStyle['type'];
        }

        if (in_array('var', $flags) && is_string($data)) {
            $data = $this->addVars($data);
        }

        if (in_array('loc', $flags) && is_string($data)) {
            $data = $this->addLoc($data);
        }

        $style = $this->getStyle($type, $flags);
        $style = ArrayHelper::merge($style, $parentStyle);

        if ($parentType) {
            $parentTypeflags = explode(':', $parentType);
            $parentType      = array_shift($parentTypeflags);
            $parentTypeStyle = $this->getStyle($parentType, $parentTypeflags);
            $style           = ArrayHelper::merge($style, $parentTypeStyle);
        }

        if (isset($data['options'],$data['options']['isset'])) {
            if (!$data['options']['isset']) {
                return null;
            }
        };
        if (is_array($data) && isset($data['style:dark'])) {
            $data['style'] = $data['style:dark'];
            unset($data['style:dark']);
            $appearance = 'dark';
        }
        if (is_array($data) && isset($data['style'])) {
            // If string, => inherit
            if (is_string($data['style'])) {
                $data['style'] = ['inherit' => $data['style']];
            }
            // Resolve inheritance
            if (isset($data['style']['inherit'])) {
                $inheritFlags = explode(':', $data['style']['inherit']);
                unset($data['style']['inherit']);
                $inheritType   = array_shift($inheritFlags);
                $data['style'] = ArrayHelper::merge($this->getStyle($inheritType, $inheritFlags), $data['style']);
            }
            $style = ArrayHelper::merge($style, $data['style']);
            unset($data['style']);
        }

        // If still has variants
        if (isset($style['variants'])) {
            foreach ($flags as $flag) {
                if (isset($style['variants'][$flag])) {
                    $style = ArrayHelper::merge($style, $style['variants'][$flag]);
                }
            }
            unset($style['variants']);
        }

        $appearance = $style['appearance'] ?? $appearance;
        unset($style['appearance']);
        if (isset($style['dark'])) {
            $style = \Flipsite\Utils\StyleAppearanceHelper::apply($style, $appearance);
        }

        if (is_array($style)) {
            $type = $style['type'] ?? $type;
            unset($style['type'],$style['section']);
        }

        if (isset($data['use'])) {
            $use = $data['use'];
            unset($data['use']);
            $data = $this->attachDataToTpl($data, new \Adbar\Dot($use));
        }
        if (isset($style['tpl'])) {
            if (is_string($data) || (is_array($data) && !ArrayHelper::isAssociative($data))) {
                $data = ['value' => $data];
            }
            if (isset($style['tplDefault'])) {
                $default = $this->reader->getLocalizer()->localize($style['tplDefault'], $this->path->getLanguage());
                $data    = $this->addTplDefaultData($data, $default);
                unset($style['tplDefault']);
            }
            $tpl   = $this->reader->getLocalizer()->localize($style['tpl'], $this->path->getLanguage());
            $data  = $this->attachDataToTpl($tpl, new \Adbar\Dot($data));
            unset($style['tpl']);
        }

        // Check external factories
        foreach ($this->factories as $factory) {
            $component = $factory->get($type);
            if (null !== $component) {
                if (method_exists($component, 'addBuilder')) {
                    $component->addBuilder($this);
                }
                if (method_exists($component, 'addEnviroment')) {
                    $component->addEnviroment($this->enviroment);
                }
                if (method_exists($component, 'addImageHandler')) {
                    $component->addImageHandler($this->imageHandler);
                }
                if (method_exists($component, 'addVideoHandler')) {
                    $component->addVideoHandler($this->videoHandler);
                }
                if (method_exists($component, 'addPath')) {
                    $component->addPath($this->path);
                }
                if (method_exists($component, 'addReader')) {
                    $component->addReader($this->reader);
                }
                if (method_exists($component, 'addSlugs')) {
                    $component->addSlugs($this->reader->getSlugs());
                }
                if (method_exists($component, 'addCanIUse')) {
                    $component->addCanIUse($this->canIUse);
                }
                if (method_exists($component, 'addRequest')) {
                    $component->addRequest($this->request);
                }
                $data          = $component->normalize($data);
                $data['flags'] = $flags;
                if (isset($data['_attr'])) {
                    foreach ($data['_attr'] as $attr => $value) {
                        if (str_ends_with($attr, ':loc')) {
                            $value = $this->addLoc($value);
                            $attr  = rtrim($attr, ':loc');
                        }
                        $component->setAttribute($attr, $value);
                    }
                    unset($data['_attr']);
                }
                if (isset($style['tag'])) {
                    $component->setTag($style['tag']);
                    unset($style['tag']);
                }
                unset($data['_meta'],$data['_name']);
                if (isset($data['background'])) {
                    $component->setBackground($data['background'], $style['background'] ?? []);
                    unset($data['background']);
                }
                $component->build($data, $style ?? [], $appearance);
                return $component;
            }
        }
        return null;
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

    public function getStyle(string $type, array $flags = []): array
    {
        $style = $this->getComponentStyle($type, $flags);
        foreach ($flags as $flag) {
            if (isset($style['variants'][$flag])) {
                $style = ArrayHelper::merge($style, $style['variants'][$flag]);
            }
        }
        unset($style['variants']);
        return $style;
    }

    private function getLayout(string $layout): array
    {
        $variants = explode(':', $layout);
        $layout   = array_shift($variants);
        $style    = [];
        if (isset($this->theme['layouts'][$layout])) {
            $style = ArrayHelper::merge($style, $this->theme['layouts'][$layout]);
        }
        foreach ($variants as $variant) {
            if (isset($style['variants'][$variant])) {
                $style = ArrayHelper::merge($style, $style['variants'][$variant]);
                unset($style['variants'][$variant]);
            }
        }
        return $style;
    }

    private function buildComponent(string $type): ?AbstractComponent
    {
        //Check external factories
        foreach ($this->factories as $factory) {
            $component = $factory->get($type, );
            if (null !== $component) {
                if (method_exists($component, 'addBuilder')) {
                    $component->addBuilder($this);
                }
                if (method_exists($component, 'addEnviroment')) {
                    $component->addEnviroment($this->enviroment);
                }
                if (method_exists($component, 'addImageHandler')) {
                    $component->addImageHandler($this->imageHandler);
                }
                if (method_exists($component, 'addPath')) {
                    $component->addPath($this->path);
                }
                if (method_exists($component, 'addReader')) {
                    $component->addReader($this->reader);
                }
                if (method_exists($component, 'addSlugs')) {
                    $component->addSlugs($this->reader->getSlugs());
                }
                if (method_exists($component, 'addCanIUse')) {
                    $component->addCanIUse($this->canIUse);
                }
                if (method_exists($component, 'addRequest')) {
                    $component->addRequest($this->request);
                }
                return $component;
            }
        }
        return null;
    }

    private function handleIf(array $if): bool
    {
        if (isset($if['isset']) && !$if['isset']) {
            return true;
        }
        return false;
    }

    private function getComponentStyle(string $type, array $flags = []): array
    {
        $style = $this->theme['components'][$type] ?? [];
        if (count($flags)) {
            $type = $type.':'.implode(':', $flags);
            if (isset($this->theme['components'][$type])) {
                $style = ArrayHelper::merge($style, $this->theme['components'][$type]);
            }
        }
        return $style;
    }

    private function addVars(string $value): string
    {
        if (strpos($value, '%date.year%') !== false) {
            $value = str_replace('%date.year%', date('Y'), $value);
        }
        $matches = [];
        preg_match_all('/\%page.(.*).name\%/', $value, $matches);

        foreach ($matches[1] as $page) {
            $page  = $page === 'current' ? $this->path->getPage() : $page;
            $value = str_replace('%page.'.$page.'.name%', date('Y'), $this->reader->getPageName($page, $this->path->getLanguage()));
        }
        return $value;
    }

    private function addLoc(string $value): string
    {
        if (count($this->localization) === 0) {
            $flipsite = \Symfony\Component\Yaml\Yaml::parseFile($this->enviroment->getVendorDir().'/flipsite/flipsite/localization/flipsite.yaml');
            foreach ($flipsite as $key => $loc) {
                if (!isset($this->localization[$key])) {
                    $this->localization[$key] = $loc;
                }
            }
        }

        $language = (string)$this->path->getLanguage();
        return $this->localization[$value][$language] ?? $language.':'.$value;
    }

    private function handleScripts(array $scripts)
    {
        foreach ($scripts['global'] ?? [] as $id => $script) {
            $filepath = $this->enviroment->getSiteDir().'/'.$script;
            if (file_exists($filepath)) {
                $this->dispatch(new Event('global-script', $id, file_get_contents($filepath)));
            }
        }
        foreach ($scripts['ready'] ?? [] as $id => $script) {
            $filepath = $this->enviroment->getSiteDir().'/'.$script;
            if (file_exists($filepath)) {
                $this->dispatch(new Event('ready-script', $id, file_get_contents($filepath)));
            }
        }
    }
}
