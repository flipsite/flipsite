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
use Flipsite\Utils\CanIUse;
use Psr\Http\Message\ServerRequestInterface as Request;

class ComponentBuilder
{
    private ImageHandler $imageHandler;
    private ?SectionBuilder $sectionBuilder = null;
    private array $listeners                = [];
    private array $factories                = [];
    private array $componentStyle           = [];

    public function __construct(private Request $request, private Enviroment $enviroment, private Reader $reader, private Path $path, private CanIUse $canIUse)
    {
        $this->imageHandler = new ImageHandler(
            $enviroment->getImageSources(),
            $enviroment->getImgDir(),
            $enviroment->getImgBasePath(),
        );
        $this->componentStyle = $reader->get('theme.components') ?? [];
    }

    public function addFactory(AbstractComponentFactory $factory) : void
    {
        $this->factories[] = $factory;
    }

    public function build(string $type, $data) : ?AbstractComponent
    {
        if (is_array($data) && (isset($data['style']) || isset($data['extend']))) {
            $style = [
                'bgColor'       => 'bg-red',
                'heading'       => [
                    'textSize' => 'text-20'
                ]];
        }
        //unset($data['style']);
        $flags      = explode(':', $type);
        $type       = array_shift($flags);
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
                if (method_exists($component, 'addCanIUse')) {
                    $component->addCanIUse($this->canIUse);
                }
                if (method_exists($component, 'addRequest')) {
                    $component->addRequest($this->request);
                }

                $component->with($data, $style ?? []);
                return $component;
            }
        }
        return null;
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
            $flags                   = explode(':', $componentType);
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

    public function expandStyle(array|string $style) : array
    {
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
        $style['inherit']            = false; // custom style is loaded
        $this->componentStyle[$type] = $style;
        unset($style['inherit']);
        return $style;
    }

    private function buildComponent(string $type, ) : ?AbstractComponent
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

    private function handleIf(array $if) : bool
    {
        if (isset($if['isset']) && !$if['isset']) {
            return true;
        }
        return false;
    }
}
