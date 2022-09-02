<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

abstract class AbstractElement
{
    protected bool $empty       = false;
    protected bool $oneline     = false;
    protected string $tag       = '';
    protected array $children   = [];
    protected string $content   = '';
    protected array $attributes = [];
    protected array $style      = [];
    protected bool $render      = true;
    private ?string $cache      = null;

    public function addStyle(null|array|string $style) : self
    {
        if (null === $style) {
            return $this;
        }
        if (is_string($style)) {
            $style = ['_' => $style];
        }
        $this->cache = null;
        $this->style = ArrayHelper::merge($this->style, $style);
        return $this;
    }

    public function getTag() : string
    {
        return $this->tag;
    }

    public function childCount() : int
    {
        return count($this->children);
    }

    public function attributeCount() : int
    {
        return count($this->attributes) + count($this->style) ? 1 : 0;
    }

    public function getChild(string $name) : ?AbstractElement
    {
        return $this->children[$name] ?? null;
    }

    public function getChildren() : array
    {
        return $this->children;
    }

    public function getAttribute(string $attr)
    {
        return $this->attributes[$attr] ?? null;
    }

    public function setAttribute(string $attr, $value, bool $append = false) : self
    {
        $this->cache = null;
        if (null === $value) {
            unset($this->attributes[$attr]);
        } elseif ($append && isset($this->attributes[$attr])) {
            $this->attributes[$attr] .= ' '.$value;
        } else {
            $this->attributes[$attr] = $value;
        }
        return $this;
    }

    public function setAttributes(array $attributes, bool $append = false) : self
    {
        foreach ($attributes as $attr => $value) {
            $this->setAttribute($attr, $value, $append);
        }
        return $this;
    }

    public function setContent(string $content) : self
    {
        $this->cache   = null;
        $this->content = $content;
        return $this;
    }

    public function appendContent(string $content) : self
    {
        $this->cache = null;
        $this->content .= $content;
        return $this;
    }

    public function prependChild(?AbstractElement $child = null, ?string $name = null) : self
    {
        if (null === $child) {
            return $this;
        }
        $this->cache = null;
        $children    = [];
        if (null !== $name) {
            $children[$name] = $child;
        } else {
            $children[] = $child;
        }
        $this->children = array_merge($children, $this->children);
        return $this;
    }

    public function addChild(?AbstractElement $child = null, ?string $name = null) : self
    {
        $this->cache = null;
        if (null === $child) {
            return $this;
        }
        if (null !== $name) {
            $this->children[$name] = $child;
        } else {
            $this->children[] = $child;
        }
        return $this;
    }

    public function replaceChildren(array $children) : self
    {
        $this->cache = null;
        $this->children = $children;
        return $this;
    }

    public function addChildren(array $children) : self
    {
        foreach ($children as $child) {
            $this->addChild($child);
        }
        return $this;
    }

    public function render(int $indentation = 2, int $level = 0, bool $oneline = false) : ?string
    {
        if (!$this->render) {
            return null;
        }
        if ($this->cache) {
            return $this->cache;
        }
        $html = '';
        $i    = str_repeat(' ', $indentation * $level);
        if ('' === $this->tag) {
            $html .= $i.wordwrap($this->content, 80, $i."\n");
            $html .= "\n";
            return $html;
        }
        $html = $i.'<'.$this->tag.$this->renderAttributes().'>';
        if ($this->empty) {
            return $html."\n";
        }
        if (!$this->oneline && !$oneline) {
            $html .= "\n";
        }
        if (count($this->children)) {
            foreach ($this->children as $name => $child) {
                $html .= $child->render($indentation, $level + 1);
            }
        } else {
            if (!$this->oneline && !$oneline) {
                $ii = str_repeat(' ', $indentation * ($level + 1));
                $html .= $ii.wordwrap($this->content, 120, "\n".$ii)."\n";
            } else {
                $html .= $this->content;
            }
        }
        if (!$this->oneline && !$oneline) {
            $html .= $i.'</'.$this->tag.'>'."\n";
        } else {
            $html .= '</'.$this->tag.'>'."\n";
        }
        return $this->cache = $html;
    }

    protected function renderAttributes() : string
    {
        if (count($this->style)) {
            $class = $this->getClasses();
            if (mb_strlen($class)) {
                $this->setAttribute('class', $class);
            }
        }
        $html = '';
        foreach ($this->attributes as $attr => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $html .= ' '.$attr;
                }
            } else {
                if (is_array($value)) {
                    $value = htmlentities(json_encode($value), ENT_QUOTES, 'UTF-8');
                }
                $html .= ' '.$attr.'="'.$value.'"';
            }
        }
        return $html;
    }

    private function getClasses() : string
    {
        $classes = [];
        sort($this->style);
        foreach ($this->style as $attr => $class) {
            if (is_string($class)) {
                $classes_ = explode(' ', trim($class));
                $classes  = array_merge($classes, $classes_);
            }
        }
        $classes = array_unique($classes);
        $before  = [];
        $after   = [];
        foreach ($classes as $class) {
            if (false !== mb_strpos($class, 'transform')) {
                $before[] = $class;
            } elseif (false !== mb_strpos($class, 'transition')) {
                $before[] = $class;
            } else {
                $after[] = $class;
            }
        }
        return trim(implode(' ', array_merge($before, $after)));
    }
}
