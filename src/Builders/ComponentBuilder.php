<?php

declare(strict_types=1);

namespace Flipsite\Builders;

use Flipsite\Assets\ImageHandler;
use Flipsite\Components\AbstractComponent;
use Flipsite\Components\AbstractComponentFactory;
use Flipsite\Components\ComponentData;
use Flipsite\Components\ComponentListenerInterface;
use Flipsite\Components\Event;
use Flipsite\Data\Reader;
use Flipsite\Enviroment;
use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\Path;
use Flipsite\Utils\StyleAppearanceHelper;

class ComponentBuilder
{
    private ImageHandler $imageHandler;
    private ?SectionBuilder $sectionBuilder = null;
    private array $listeners      = [];
    private array $factories      = [];
    private array $componentStyle = [];

    public function __construct(private Enviroment $enviroment, private Reader $reader, private Path $path)
    {
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

    public function build(array $data, array $style, string $appearance) : array
    {
        $components = [];
        foreach ($data as $type => $componentData) {
            if (null === $componentData) {
                continue;
            }
            if (isset($componentData['if'])) {
                if ($this->handleIf(is_array($componentData['if']) ? $componentData['if'] : ['isset' => $componentData['if']])) {
                    return null;
                }
                unset($componentData['if']);
            }
            $component = $this->getComponent($type, $componentData, $style, $appearance);
            if (null !== $component) {
                $components[] = $component;
            }
        }
        return $components;
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

    private function getComponent(string $type, $data, array $style, string $appearance) : ?AbstractComponent
    {
        // Figure out component type
        $flags          = explode(':', $type);
        $type           = array_shift($flags);
        if (!isset($style[$type]['inherit']) || $style[$type]['inherit']) {
            $componentStyle = ArrayHelper::merge($this->getComponentStyle($type), $style[$type] ?? []);
        } else {
            $componentStyle = $style[$type] ?? [];
        }
        $componentType  = $componentStyle['type'] ?? $type;
        unset($componentStyle['type']);

        if (strpos($componentType, ':')) {
            $flags          = explode(':', $componentType);
            $componentType           = array_shift($flags);
        }

        // Get component from factory
        $component = $this->buildComponent($componentType);
        if (null === $component) {
            return null;
        }
        $componentData = new ComponentData($flags, $data, $componentStyle, $appearance);
        $id            = $componentData->getId();
        if ($id) {
            $component->setAttribute('id', $id);
        }
        $component->with($componentData);
        return $component;
    }

    public function getComponentStyle(string $type) : array
    {
        $style = $this->componentStyle[$type] ?? [];
        if (isset($style['inherit']) && !$style['inherit']) {
            unset($style['inherit']);
            return $style;
        }
        $inheritStyle = [];
        foreach ($this->factories as $factory) {
            $inheritStyle = ArrayHelper::merge($inheritStyle, $factory->getStyle($type));
        }
        if (null !== $inheritStyle) {
            $style = ArrayHelper::merge($inheritStyle, $style);
        }
        $style['inherit'] = false; // custom style is loaded
        $this->componentStyle[$type] = $style;
        unset($style['inherit']);
        return $style;
    }

    private function buildComponent(string $type) : ?AbstractComponent
    {
        //Check external factories
        foreach ($this->factories as $factory) {
            $component = $factory->get($type);
            if (null !== $component) {
                if (method_exists($component, 'addBuilder')) {
                    $component->addBuilder($this);
                }
                if (method_exists($component, 'addSectionBuilder')) {
                    if (null === $this->sectionBuilder) {
                        $this->sectionBuilder = new SectionBuilder(
                            $this->enviroment,
                            $this->reader,
                            $this,
                        );
                    }

                    $component->addSectionBuilder($this->sectionBuilder);
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
                return $component;
            }
        }
        return null;
    }

    private function handleIf(array $if) : bool
    {
        if (isset($if['isset']) && !$if['isset']) {
            return true;
        }
        return false;
    }
}
