<?php

declare(strict_types=1);
namespace Flipsite\Style;

use Flipsite\Style\Variants\AbstractType;

class Variant
{
    /**
     * @var array<string>
     */
    protected array $classes = [];
    private Tailwind $tailwind;

    /**
     * @var array<AbstractType>
     */
    private array $types = [];

    public function __construct(Tailwind $tailwind)
    {
        $this->tailwind = $tailwind;
    }

    public function addType(AbstractType $type) : void
    {
        $this->types[] = $type;
    }

    public function addClass(string $class) : void
    {
        $this->classes[] = $class;
    }

    public function getCss() : string
    {
        $css          = '';
        $mediaQueries = [];
        $pseudo       = '';
        $prefix       = '';
        $parent       = '';
        foreach ($this->types as $type) {
            $parent .= $type->getParent();
            $prefix .= $type->getPrefix().'\:';
            if (2 === mb_strlen($prefix)) {
                $prefix = '';
            }
            $mediaQuery = $type->getMediaQuery();
            if (null !== $mediaQuery) {
                $mediaQueries[] = $mediaQuery;
            }
            $pseudo .= $type->getPseudo();
        }
        if (count($mediaQueries)) {
            $css .= '@media'.$mediaQueries[0].'{';
        }
        $css .= $this->getRulesets($parent, $prefix, $pseudo);
        if (count($mediaQueries)) {
            $css .= '}';
        }

        return $css;
    }

    public function order() : int
    {
        return !isset($this->types[0]) ? 100 : $this->types[0]->order();
    }

    protected function getRulesets(string $parent, string $prefix, string $pseudo) : string
    {
        $css    = '';
        $escape = ['/', '.', '|'];
        foreach ($this->classes as $class) {
            $childCombinator = null;
            $pseudoElement   = null;
            $rules           = $this->tailwind->getRules($class, $childCombinator, $pseudoElement);
            if (null === $rules) {
                continue;
            }
            if ($parent) {
                $css .= '.'.$parent;
            }
            $css .= '.';
            $css .= $prefix;
            $cls = $class;
            foreach ($escape as $char) {
                $cls = str_replace($char, '\\'.$char, $cls);
            }
            $css .= $cls;
            $css .= $pseudo;
            if ($pseudoElement) {
                $css .= $pseudoElement;
            }
            if (null !== $childCombinator) {
                $css .= '>'.$childCombinator;
            }
            $css .= '{'.$rules.'}';
        }
        return $css;
    }
}
