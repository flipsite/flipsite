<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Components\Document;
use Flipsite\Components\AbstractElement;
use Flipsite\Components\Element;
use Symfony\Component\Yaml\Yaml;
use Flipsite\Style\Tailwind;
use Flipsite\Style\Callbacks\UnitCallback;
use Flipsite\Style\Callbacks\ScreenWidthCallback;
use Flipsite\Style\Callbacks\ResponsiveSizeCallback;

class StyleBuilder implements BuilderInterface
{
    public function __construct()
    {
        
    }

    public function getDocument(Document $document): Document
    {
        $elements = [];
        $classes = [];
        $this->getElementsAndClasses($document, $elements, $classes);
        $elements = array_unique($elements);
        $classes = array_unique($classes);
        $config = Yaml::parse(file_get_contents(__DIR__.'/../Style/config.yaml'));

        // Overwrite keyframe definitions instead of merge
        // foreach ($this->theme['keyframes'] ?? [] as $keyframe => $definition) {
        //     if (isset($config['keyframes'][$keyframe])) {
        //         $config['keyframes'][$keyframe] = $definition;
        //         unset($config['keyframes'][$keyframe]);
        //     }
        // }


        // $config = ArrayHelper::merge($config, $this->theme ?? []);
        // $fonts  = $config['fonts'] ?? [];

        // unset($config['fonts']);
        // if (!isset($config['fontFamily'])) {
        //     $config['fontFamily'] = [];
        // }

        // foreach ($fonts as $type => $options) {
        //     if (!is_array($options)) {
        //         continue;
        //     }
        //     $font = $options['family'];
        //     if (false !== mb_strpos($font, ' ')) {
        //         $font = "'".$font."'";
        //     }
        //     $font                        = [$font];
        //     $fallback                    = $options['fallback'] ?? 'sans';
        //     $font                        = array_merge($font, $config['fontFamily'][$fallback] ?? []);
        //     $config['fontFamily'][$type] = $font;
        // }

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

        $style = new Element('style',true);
        $style->setContent($css);
        $document->getChild('head')->addChild($style);
        
        return $document;
    }
    private function getElementsAndClasses(AbstractElement $element, array &$elements, array &$classes) {
        $elements[] = $element->getTag();
        $classes = array_merge($classes, $element->getClasses('array'));
        foreach ($element->getChildren() as $name => $child) {
            $this->getElementsAndClasses($child, $elements, $classes);
        }
    }
}