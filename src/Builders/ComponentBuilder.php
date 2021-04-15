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
    private array $listeners    = [];
    private array $factories    = [];
    private array $defaultStyle = [];

    public function __construct(Enviroment $enviroment, Reader $reader, Path $path)
    {
        $this->enviroment   = $enviroment;
        $this->reader       = $reader;
        $this->path         = $path;
        $this->imageHandler = new ImageHandler(
            $enviroment->getImageSources(),
            $enviroment->getImgDir()
        );
        $this->defaultStyle = $reader->get('theme.components') ?? [];
    }

    public function addFactory(AbstractComponentFactory $factory) : void
    {
        $this->factories[] = $factory;
    }

    public function build(string $type, $data, array $style, array $flags = []) : ?AbstractComponent
    {
        if (($style['inherit'] ?? true) && isset($this->defaultStyle[$type])) {
            $style = ArrayHelper::merge($this->defaultStyle[$type], $style);
        }
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
