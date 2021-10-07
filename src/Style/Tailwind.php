<?php

declare(strict_types=1);
namespace Flipsite\Style;

use Flipsite\Style\Rules\AbstractRule;
use Symfony\Component\Yaml\Yaml;

final class Tailwind implements CallbackInterface
{
    private string $dark = 'media';
    private array $rules;
    private array $config;
    private array $variants  = [];
    private array $callbacks = [];

    public function __construct(array $config)
    {
        $this->rules  = Yaml::parse(file_get_contents(__DIR__.'/rules.yaml'));
        $this->config = $config;
    }

    public function call(string $property, array $args) : ?string
    {
        if (!isset($this->callbacks[$property])) {
            return null;
        }

        foreach ($this->callbacks[$property] as $callback) {
            $value = $callback($args);
            if (null !== $value) {
                return $value;
            }
        }
        return null;
    }

    public function addCallback(string $property, callable $callback) : self
    {
        if (!isset($this->callbacks[$property])) {
            $this->callbacks[$property] = [];
        }
        $this->callbacks[$property][] = $callback;
        return $this;
    }

    public function getCss(array $elements, array $classes) : string
    {
        $hasBorders = $this->hasBorders($elements, $classes);
        $css        = $this->getPreflight(
            $elements,
            $this->config['fontFamily']['sans'] ?? null,
            $this->config['fontFamily']['mono'] ?? null,
            $hasBorders,
            $this->config['borderColor']['DEFAULT'],
            $this->config['borderWidth']['DEFAULT'] ?? '1px',
        );
        foreach ($classes as $class) {
            $variant = ['DEFAULT'];
            if (mb_strpos($class, ':')) {
                $variant = explode(':', $class);
                $class   = array_pop($variant);
            }
            $this->addToVariant($variant, $class);
        }
        uasort($this->variants, function ($a, $b) {
            return $a->order() <=> $b->order();
        });
        foreach ($this->variants as $variant) {
            $css .= $variant->getCss();
        }

        // Keyframes
        $pattern = '/animation\:([a-z0-9\-\_]+)\s{1}/';
        $matches = [];
        preg_match_all($pattern, $css, $matches);
        if (count($matches[1])) {
            $css .= $this->addKeyframes($matches[1]);
        }
        return $css;
    }

    public function getRules(string $className, ?string &$childCombinator, ?string &$pseudoElement) : ?string
    {
        if (isset($this->rules[$className])) {
            return $this->rules[$className];
        }
        $class = $this->getRule($className);
        if (null === $class) {
            return null;
        }
        $childCombinator = $class->getChildCombinator();
        $pseudoElement   = $class->getPseudoElement();
        return $class->getDeclarations();
    }

    private function hasBorders(array $elements, array $classes) : bool
    {
        if (in_array('hr', $elements)) {
            return true;
        }
        foreach ($classes as $class) {
            if (false !== mb_strpos($class, 'border')) {
                return true;
            }
        }
        return false;
    }

    private function getRule(string $className) : ?AbstractRule
    {
        if (!mb_strlen($className)) {
            return null;
        }
        $negative = false;
        $args     = explode('-', $className);
        if ('' === $args[0]) { //negative
            array_shift($args);
            $negative = true;
        }
        if (!isset($args[0])) {
            return null;
        }
        $first = ucfirst($args[0]);
        if (isset($args[2])) {
            $third     = ucfirst((string) $args[2]);
            $second    = ucfirst((string) $args[1]);
            $ruleClass = 'Flipsite\Style\Rules\Rule'.$first.$second.$third;
            if (class_exists($ruleClass)) {
                array_shift($args);
                array_shift($args);
                array_shift($args);
                return new $ruleClass($args, $negative, $this->config, $this);
            }
        }
        if (isset($args[1])) {
            $second    = ucfirst((string) $args[1]);
            $ruleClass = 'Flipsite\Style\Rules\Rule'.$first.$second;
            if (class_exists($ruleClass)) {
                array_shift($args);
                array_shift($args);
                return new $ruleClass($args, $negative, $this->config, $this);
            }
        }
        $ruleClass = 'Flipsite\Style\Rules\Rule'.$first;
        if (class_exists($ruleClass)) {
            array_shift($args);
            return new $ruleClass($args, $negative, $this->config, $this);
        }
        return null;
    }

    private function addToVariant(array $variants, string $class) : void
    {
        $variantId = implode(':', $variants);
        if (!isset($this->variants[$variantId])) {
            $this->variants[$variantId] = $this->createVariant($variants);
        }

        $this->variants[$variantId]->addClass($class);
    }

    private function getPreflight(array $elements, ?array $sansFonts = null, ?array $monoFonts = null, bool $hasBorders = false, ?string $borderColor = null, ?string $borderWidth = null)
    {
        $css = '*,::before,::after{box-sizing:border-box;';
        if ($hasBorders) {
            $css .= 'border-width:0;border-style:solid;';
            $css .= 'border-color:'.$borderColor;
        }
        $css .= '}';
        $preflight = new Preflight();
        $css .= $preflight->getCss($elements);
        $css = str_replace('fontFamily.sans', implode(',', $sansFonts), $css);
        $css = str_replace('fontFamily.mono', implode(',', $monoFonts), $css);
        if ($hasBorders) {
            $css = str_replace('borderWidth.DEFAULT', $borderWidth, $css);
        }
        return $css;
    }

    private function createVariant(array $variants) : Variant
    {
        $variant = new Variant($this);
        foreach ($variants as $variantId) {
            if ('DEFAULT' === $variantId) {
                continue;
            }
            if (isset($this->config['screens'][$variantId])) {
                $type = new Variants\ResponsiveType($variantId, intval($this->config['screens'][$variantId]));
            } elseif ('dark' === $variantId) {
                $type = new Variants\DarkType($this->dark);
            } else {
                // Change foo-bar to FooBar
                $variantId = explode('-', $variantId);
                foreach ($variantId as &$word) {
                    $word = ucfirst(mb_strtolower($word));
                }
                $variantId = implode('', $variantId);
                $class     = 'Flipsite\Style\Variants\\'.$variantId.'Type';
                if (!class_exists($class)) {
                    $class     = 'Flipsite\Style\Variants\\CustomStateType';
                }
                $type = new $class($variantId);
            }
            $variant->addType($type);
        }
        return $variant;
    }

    private function addKeyframes(array $keyframes) : string
    {
        $css = '';
        foreach ($keyframes as $keyframe) {
            $css .= ' @keyframes '.$keyframe.'{';
            foreach ($this->config['keyframes'][$keyframe] as $at => $properties) {
                $css .= $at.'{';
                $declarations = [];
                foreach ($properties as $attr => $value) {
                    $declarations[] = $attr.':'.$value;
                }
                $css .= implode(';', $declarations);
                $css .= '}';
            }

            $css .= '}';
        }
        return $css;
    }
}
