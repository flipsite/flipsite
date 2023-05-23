<?php

declare(strict_types=1);

namespace Flipsite\Builders;

use Flipsite\Assets\ImageHandler;
use Flipsite\Assets\VideoHandler;
use Flipsite\Components\AbstractComponent;
use Flipsite\Components\AbstractComponentFactory;
use Flipsite\Components\ComponentListenerInterface;
use Flipsite\Components\Event;
use Flipsite\Data\Reader;
use Flipsite\Environment;
use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\DataHelper;
use Flipsite\Utils\Path;
use Psr\Http\Message\ServerRequestInterface as Request;

class ComponentBuilder
{
    private ImageHandler $imageHandler;
    private VideoHandler $videoHandler;
    private array $listeners                = [];
    private array $factories                = [];
    private array $theme                    = [];
    private array $localization             = [];

    public function __construct(private Request $request, private Environment $environment, private Reader $reader, private Path $path)
    {
        $this->imageHandler = new ImageHandler(
            $environment->getAssetSources(),
            $environment->getImgDir(),
            $environment->getImgBasePath(),
        );
        $this->videoHandler = new VideoHandler(
            $environment->getSiteDir().'/assets',
            $environment->getVideoDir(),
            $environment->getVideoBasePath(),
        );
        $this->theme = $reader->get('theme') ?? [];
    }

    public function addFactory(AbstractComponentFactory $factory): void
    {
        $this->factories[] = $factory;
    }

    public function build(string $type, array|string|int|bool $data, array $parentStyle, string $appearance): ?AbstractComponent
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

        $style = $this->getStyle($type, $flags);
        $style = ArrayHelper::merge($style, $parentStyle);

        if ($parentType) {
            $parentTypeflags = explode(':', $parentType);
            $parentType      = array_shift($parentTypeflags);
            $parentTypeStyle = $this->getStyle($parentType, $parentTypeflags);
            $style           = ArrayHelper::merge($parentTypeStyle, $style);
        }

        if (isset($data['_options'],$data['_options']['isset'])) {
            if (!$data['_options']['isset']) {
                return null;
            }
        };

        if (is_array($data) && isset($data['_style'])) {
            // If string, => inherit
            if (is_string($data['_style'])) {
                $data['_style'] = ['inherit' => $data['_style']];
            }

            // Resolve inheritance
            while (isset($data['_style']['inherit'])) {
                $inheritType    = $data['_style']['inherit'];
                unset($data['_style']['inherit']);
                $data['_style'] = ArrayHelper::merge($this->getStyle($inheritType), $data['_style']);
            }

            $style = ArrayHelper::merge($style, $data['_style']);
            unset($data['_style']);
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

        if (isset($data['_dataSource'])) {
            $data = DataHelper::applyData($data, $data['_dataSource'], '_dataSource', ['_dataSourceList']);
        }

        // Check external factories
        foreach ($this->factories as $factory) {
            $component = $factory->get($type);
            if (null !== $component) {
                if (method_exists($component, 'addBuilder')) {
                    $component->addBuilder($this);
                }
                if (method_exists($component, 'addEnvironment')) {
                    $component->addEnvironment($this->environment);
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
                if (method_exists($component, 'addRequest')) {
                    $component->addRequest($this->request);
                }
                $data          = $component->normalize($data);
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
                    $component->setBackground($component, $style['background']);
                    unset($style['background']);
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
}
