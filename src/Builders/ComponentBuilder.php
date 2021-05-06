<?php

declare(strict_types=1);

namespace Flipsite\Builders;

use Flipsite\Assets\ImageHandler;
use Flipsite\Components\AbstractComponent;
use Flipsite\Components\AbstractComponentFactory;
use Flipsite\Components\ComponentListenerInterface;
use Flipsite\Components\Event;
use Flipsite\Data\Reader;
use Flipsite\Enviroment;
use Flipsite\Exceptions\ComponentNotFoundException;
use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\Path;

class ComponentBuilder
{
    private Enviroment $enviroment;
    private Reader $reader;
    private Path $path;
    private ImageHandler $imageHandler;
    private array $listeners      = [];
    private array $factories      = [];
    private array $componentStyle = [];

    public function __construct(Enviroment $enviroment, Reader $reader, Path $path)
    {
        $this->enviroment   = $enviroment;
        $this->reader       = $reader;
        $this->path         = $path;
        $this->imageHandler = new ImageHandler(
            $enviroment->getImageSources(),
            $enviroment->getImgDir()
        );
        $this->componentStyle = $reader->get('theme.components') ?? [];
    }

    public function addFactory(AbstractComponentFactory $factory) : void
    {
        $this->factories[] = $factory;
    }

    public function build(string $type, $data, array $style) : ?AbstractComponent
    {
        $tmp   = explode(':', $type);
        $type  = $tmp[0];
        $flags = isset($tmp[1]) ? explode('+', $tmp[1]) : [];
        $style = ArrayHelper::merge($this->componentStyle[$type] ?? [], $style);

        $variants     = isset($style['variants']) ? array_keys($style['variants']) : null;
        $variantFound = false;
        if ($variants) {
            foreach ($flags as $flag) {
                if (in_array($flag, $variants)) {
                    $style        = ArrayHelper::merge($style, $style['variants'][$flag]);
                    $variantFound = true;
                }
            }
            if (isset($data['variant'])) {
                foreach (explode('+', $data['variant']) as $variant) {
                    $style        = ArrayHelper::merge($style, $style['variants'][$variant]);
                    $variantFound = true;
                }
            }
            if (!$variantFound) {
                $style = ArrayHelper::merge($style, $style['variants']['DEFAULT'] ?? []);
            }
        }
        unset($style['variants']);

        $type = $style['type'] ?? $type;
        unset($style['type']);

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
                if (method_exists($component, 'addPath')) {
                    $component->addPath($this->path);
                }
                if (method_exists($component, 'addReader')) {
                    $component->addReader($this->reader);
                }
                if (method_exists($component, 'addSlugs')) {
                    $component->addSlugs($this->reader->getSlugs());
                }

                $component->with($data, $style, $flags);
                return $component;
            }
        }
        return null;
        //throw new ComponentNotFoundException($type, $data);
    }

    public function addListener(ComponentListenerInterface $listener) : void
    {
        $this->listeners[] = $listener;
    }

    public function dispatch(Event $event) : void
    {
        foreach ($this->listeners as $listener) {
            $listener->handleComponentEvent($event);
        }
    }
}
