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
        $elements = array_unique($elements);
        $classes  = array_unique($classes);

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
        // $newClasses = [];
        // if ($this->isEnabled) {
        //     $css = $this->minmizeClasses($css, $classes, $newClasses);
        //     $html = $this->replaceClasses($html, $newClasses);
        // }

        $style = new Element('style', true);
        $style->setContent($css);
        $document->getChild('head')->addChild($style);

        return $document;
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
}
