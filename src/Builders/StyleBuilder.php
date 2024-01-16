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
use Flipsite\Builders\EventListenerInterface;

class StyleBuilder implements BuilderInterface
{
    public function __construct(private array $colors, private array $fonts = [])
    {
    }

    public function getDocument(Document $document): Document
    {
        $elements = [];
        $classes  = [];
        $this->getElementsAndClasses($document, $elements, $classes);
        $elements = array_values(array_unique($elements));
        $classes  = array_values(array_unique($classes));

        
        $prefixNeedingScript = ['scroll','stuck','enter'];
        foreach ($classes as $class) {
            if (strpos($class,':') === false) {
                continue;
            }
            $tmp = explode(':',$class);
            $prefix = $tmp[0];
            if (in_array($prefix,$prefixNeedingScript)) {
                $keyToRemove = array_search($prefix, $prefixNeedingScript);
                unset($prefixNeedingScript[$keyToRemove]);
                $this->dispatch(new Event('ready-script', $prefix, file_get_contents(__DIR__.'/../../js/ready.'.$prefix.'.min.js')));
            }
        }
        
        // $bodyHtml = $document->getChild('body')->render(2, 1);
        // if (strpos($bodyHtml, 'scroll:')) {
        //     $componentBuilder->dispatch(new Flipsite\Builders\Event('ready-script', 'scroll', file_get_contents(__DIR__.'/../js/ready.scroll.min.js')));
        // }
        // if (strpos($bodyHtml, 'stuck:')) {
        //     $componentBuilder->dispatch(new Flipsite\Builders\Event('ready-script', 'stuck', file_get_contents(__DIR__.'/../js/ready.stuck.min.js')));
        // }
        // if (strpos($bodyHtml, 'enter:')) {
        //     $componentBuilder->dispatch(new Flipsite\Builders\Event('ready-script', 'enter', file_get_contents(__DIR__.'/../js/ready.enter.min.js')));
        // }
    

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

        $tailwind = new Tailwind($config);
        $tailwind->addCallback('size', new UnitCallback());
        $tailwind->addCallback('size', new ScreenWidthCallback($config['screens']));
        $tailwind->addCallback('size', new ResponsiveSizeCallback($config['screens'], true));

        $css = $tailwind->getCss($elements, $classes);
        $newClasses = [];
        
        // $css = $this->minmizeClasses($css, $classes, $newClasses);
        // $this->parseElement($document->getChild('body'), $newClasses);

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

    private function getElementsAndClasses(AbstractElement $element, array &$elements, array &$classes)
    {
        $tag = $element->getTag();
        $elements[] = $element->getTag();
        $classes    = array_merge($classes, $element->getClasses('array'));
        $content    = $element->getContent();
        if ($content) {
            $pattern = '/class="([^"]+)"/';
            if (preg_match_all($pattern, $content, $matches)) {
                $contentClasses = array_unique($matches[1]);
                foreach ($contentClasses as $cls) {
                    $classes = array_merge($classes, explode(' ',$cls));
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
        $escape = ["/",'|','.',':','%','[',']'];
        foreach ($classes as $i => $class) {
            if (strpos($class, 'open:') !== false) {
                continue;
            }
            $orginal = $class;
            $tmp = explode(':', $class);
            $prefix = false;
            if (count($tmp) > 1) {
                $class = array_pop($tmp);
                $prefix = implode(':', $tmp);
            }
            $newClassName = $this->getClassName($i + 1);
            if ($prefix) {
                $newClassName = $prefix.':'.$newClassName;
                $oldInCss = $prefix.':'.$class;
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
            $label = chr(65 + $remainder) . $label;
            $index = intval(($index - $remainder) / 26);
        }

        return strtolower($label);
    }

    private function parseElement(AbstractElement $element, array $newClasses) {
        $element->replaceStyle($this->replaceClasses($element->getStyle(), $newClasses));
        foreach ($element->getChildren() as $child) {
            $this->parseElement($child, $newClasses);
        }
    }
    private function replaceClasses(array $style, array $newClasses) : array {
        $states = ['open:','!open:'];
        $replaced = [];
        foreach ($style as $attr => $oldClasses) {
            if (is_array($oldClasses)) {
                $replaced[$attr] = $this->replaceClasses($oldClasses, $newClasses); 
            } else {
                $new = [];
                foreach (explode(' ',$oldClasses) as $class) {
                    $tmp = explode(' ', $class);
                    foreach ($tmp as $oldClass) {
                        $prefix = '';
                        foreach ($states as $state) {
                            if (str_starts_with($oldClass, $state)) {
                                $prefix = $state;
                                $oldClass = substr($oldClass, strlen($prefix));
                            }
                        }
        
                        if (isset($newClasses[$oldClass])) {
                            $new[] = $prefix.$newClasses[$oldClass];
                        } else {
                            $new[] = $oldClass;
                        }
                    }
                }
                $replaced[$attr] = implode(' ', $new);
            }
        }
        return $replaced;
    }
}
