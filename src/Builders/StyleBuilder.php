<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Components\Document;
use Flipsite\Components\AbstractElement;
use Flipsite\Components\Element;
use Flipsite\Utils\ArrayHelper;
use Symfony\Component\Yaml\Yaml;
use Flipsite\Style\Tailwind;
use Flipsite\Style\Callbacks\UnitCallback;
use Flipsite\Style\Callbacks\ScreenWidthCallback;
use Flipsite\Style\Callbacks\ResponsiveSizeCallback;

class StyleBuilder implements BuilderInterface, EventListenerInterface
{
    private array $dataAttributesWithClasses = ['data-toggle', 'data-animate', 'data-selected'];

    private array $customCss = [];

    public function __construct(private array $colors, private array $fonts = [], private array $themeSettings = [], private bool $minmizeClasses = false, private bool $preflight = true)
    {
    }

    public function getDocument(Document $document): Document
    {
        $elements = [];
        $classes  = [];
        $this->getElementsAndClasses($document, $elements, $classes);
        $elements = array_values(array_unique($elements));
        $classes  = array_values(array_unique($classes));

        $config = Yaml::parse(file_get_contents(__DIR__.'/../Style/config.yaml'));

        // Overwrite keyframe definitions instead of merge
        // foreach ($this->theme['keyframes'] ?? [] as $keyframe => $definition) {
        //     if (isset($config['keyframes'][$keyframe])) {
        //         $config['keyframes'][$keyframe] = $definition;
        //         unset($config['keyframes'][$keyframe]);
        //     }
        // }

        $config = ArrayHelper::merge($config, ['colors' => $this->colors, 'fonts' => $this->fonts]);
        $fonts  = $config['fonts'] ?? [];

        unset($config['fonts']);
        if (!isset($config['fontFamily'])) {
            $config['fontFamily'] = [];
        }

        foreach ($fonts as $type => $options) {
            if (!is_array($options)) {
                continue;
            }
            $font = $options['family'];
            if (false !== mb_strpos($font, ' ')) {
                $font = "'".$font."'";
            }
            $font                        = [$font];
            $fallback                    = $options['fallback'] ?? 'sans';
            $font                        = array_merge($font, $config['fontFamily'][$fallback] ?? []);
            $config['fontFamily'][$type] = $font;
        }

        $tailwind = new Tailwind($config, $this->themeSettings, $this->preflight);
        $tailwind->addCallback('size', new UnitCallback());
        $tailwind->addCallback('size', new ScreenWidthCallback($config['screens']));
        $tailwind->addCallback('size', new ResponsiveSizeCallback($config['screens'], true));

        $css        = $tailwind->getCss($elements, $classes);
        $newClasses = [];

        $css .= $this->addCustomClasses();

        if ($this->minmizeClasses) {
            $css = $this->minmizeClasses($css, $classes, $newClasses);
            $this->replaceClasses($document, $newClasses);
        }

        $style = new Element('style', true);
        $style->setContent($css);
        $document->getChild('head')->addChild($style);

        return $document;
    }

    public function addListener(EventListenerInterface $listener): void
    {
        $this->listeners[] = $listener;
    }

    private function dispatch(Event $event): void
    {
        foreach ($this->listeners as $listener) {
            $listener->handleEvent($event);
        }
    }

    public function handleEvent(Event $event) : void
    {
        if ('background-image' === $event->getType()) {
            foreach ($event->getData() as $mediaQuery => $declarations) {
                $this->customCss[$mediaQuery] ??= [];
                foreach ($declarations as $selector => $styles) {
                    $this->customCss[$mediaQuery][$selector] ??= [];
                    foreach ($styles as $property => $value) {
                        $this->customCss[$mediaQuery][$selector][$property] = $value;
                    }
                }
            }
        }
    }

    private function addCustomClasses() : string
    {
        $css = '';
        foreach ($this->customCss as $mediaQuery => $declarations) {
            $css .= '@media '.$mediaQuery.' {';
            foreach ($declarations as $selector => $styles) {
                $css .= $selector.' {';
                foreach ($styles as $property => $value) {
                    $css .= $property.':'.$value.';';
                }
                $css .= '}';
            }
            $css .= '}';
        }
        return $css;
    }

    private function getElementsAndClasses(AbstractElement $element, array &$elements, array &$classes)
    {
        $elements[] = $element->getTag();
        $classes    = array_merge($classes, $element->getClasses('array'));

        foreach ($this->dataAttributesWithClasses as $dataAttribute) {
            if ($dataClasses = $element->getAttribute($dataAttribute)) {
                $classes = array_merge($classes, explode(' ', $dataClasses));
            }
        }

        $content = $element->getContent();
        if ($content) {
            $pattern = '/class="([^"]+)"/';
            if (preg_match_all($pattern, $content, $matches)) {
                $contentClasses = array_unique($matches[1]);
                foreach ($contentClasses as $cls) {
                    $classes = array_merge($classes, explode(' ', $cls));
                }
            }

            $pattern = '/<([a-zA-Z][^\s>]*)/';
            // Perform the regular expression match all
            if (preg_match_all($pattern, $content, $matches)) {
                $elements = array_merge($elements, $matches[1]);
            }
        }
        foreach ($element->getChildren() as $name => $child) {
            $this->getElementsAndClasses($child, $elements, $classes);
        }
    }

    private function minmizeClasses(string $css, array $classes, array &$newClasses): string
    {
        usort($classes, function ($a, $b) {
            return strlen($b) - strlen($a);
        });
        $escape = ['/', '|', '.', ':', '%', '[', ']', '#'];
        foreach ($classes as $i => $class) {
            $orginal = $class;
            $tmp     = explode(':', $class);
            $prefix  = false;
            if (count($tmp) > 1) {
                $class  = array_pop($tmp);
                $prefix = implode(':', $tmp);
            }
            $newClassName = $this->getClassName($i + 1);

            if ($prefix) {
                $newClassName = $prefix.':'.$newClassName;
                $oldInCss     = $prefix.':'.$class;
            } else {
                $oldInCss = $class;
            }
            $newInCss = $newClassName;

            $newClasses[$oldInCss] = $newInCss;

            foreach ($escape as $e) {
                $oldInCss = str_replace($e, '\\'.$e, $oldInCss);
                $newInCss = str_replace($e, '\\'.$e, $newInCss);
            }

            $css = str_replace('.'.$oldInCss, '.'.$newInCss, $css);
        }
        return $css;
    }

    private function getClassName(int $index): string
    {
        $label = '';

        // Convert the index to a base-26 representation
        while ($index > 0) {
            $remainder = ($index - 1) % 26;
            $label     = chr(65 + $remainder) . $label;
            $index     = intval(($index - $remainder) / 26);
        }

        return strtolower($label);
    }

    private function replaceClasses(AbstractElement $element, array $newClasses)
    {
        $style = $element->getStyle();
        foreach ($style as $attr => &$classes) {
            foreach ($classes as &$class) {
                if (isset($newClasses[$class])) {
                    $class = $newClasses[$class];
                }
            }
        }
        $element->replaceStyle($style);

        foreach ($this->dataAttributesWithClasses as $dataAttribute) {
            if ($dataClasses = $element->getAttribute($dataAttribute)) {
                $tmp = explode(' ', $dataClasses);
                foreach ($tmp as &$dataClass) {
                    if (isset($newClasses[$dataClass])) {
                        $dataClass = $newClasses[$dataClass];
                    }
                }
                $element->setAttribute($dataAttribute, implode(' ', $tmp));
            }
        }

        $content = $element->getContent();
        if (strpos($content, 'class="') !== false) {
            $pattern = '/class="([^"]+)"/';
            if (preg_match_all($pattern, $content, $matches)) {
                $contentClasses = array_unique($matches[1]);
                foreach ($contentClasses as $cls) {
                    $tmp = explode(' ', $cls);
                    foreach ($tmp as &$dataClass) {
                        if (isset($newClasses[$dataClass])) {
                            $dataClass = $newClasses[$dataClass];
                        }
                    }
                    $content = str_replace($cls, implode(' ', $tmp), $content);
                }
            }
            $element->setContent($content);
        }

        foreach ($element->getChildren() as $child) {
            $this->replaceClasses($child, $newClasses);
        }
    }
}
