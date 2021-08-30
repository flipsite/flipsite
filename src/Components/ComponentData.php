<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class ComponentData
{
    private array $flags = [];
    private ?string $id  = null;
    private ?string $tag = null;
    private array $data  = [];
    private array $style = [];
    private string $appearance;

    public function __construct(array $flags, $data, array $style, string $appearance)
    {
        $this->appearance = $appearance;
        $dataStyle        = false;
        if (is_bool($data)) {
            $this->data = ['value' => true];
        } elseif (is_string($data)) {
            $this->data = ['value' => $data];
        } elseif (ArrayHelper::isAssociative($data)) {
            $this->id  = $data['id']    ?? null;
            $this->tag = $data['tag']   ?? null;
            $dataStyle = $data['style'] ?? false;
            unset($data['id'],$data['tag'],$data['style']);
            $this->data = $data;
        } else {
            $this->data = $data;
        }
        if ('dark' === $appearance || in_array('dark', $flags)) {
            $this->appearance = 'dark';
            $style = ArrayHelper::merge($style, $style['dark'] ?? []);
        }
        unset($style['dark']);
        $flags = array_diff($flags, ['dark']);

        if (isset($data['variant'])) {
            $variant = $data['variant'];
            if (isset($style['variants'][$variant])) {
                $style        = ArrayHelper::merge($style, $style['variants'][$variant]);
                $flags        = array_diff($flags, [$variant]);
                $variantFound = true;
            }
        } else {
            $variantFound = false;
            foreach ($flags as $variant) {
                if (isset($style['variants'][$variant])) {
                    $style        = ArrayHelper::merge($style, $style['variants'][$variant]);
                    $flags        = array_diff($flags, [$variant]);
                    $variantFound = true;
                }
            }
            if (!$variantFound && isset($style['variants']['DEFAULT'])) {
                $style = ArrayHelper::merge($style, $style['variants']['DEFAULT']);
            }
        }
        if (is_array($dataStyle)) {
            $style = ArrayHelper::merge($style, $dataStyle);
        } elseif (is_string($dataStyle)) {
            $this->data['style'] = $dataStyle;
        }
        unset($style['variants']);

        if (null === $this->tag && isset($style['tag'])) {
            $this->tag = $style['tag'];
        }
        unset($style['tag']);
        $this->style = $style;
        $this->flags = $flags;
    }

    public function get(?string $key = null, bool $unset = false)
    {
        if (null !== $key) {
            $data = $this->data[$key] ?? null;
            if ($unset) {
                unset($this->data[$key]);
            }
            return $data;
        }
        return $this->data;
    }

    public function unset(string $key)
    {
        unset($this->data[$key]);
    }

    public function getFlags() : array
    {
        return $this->flags;
    }

    public function getTag() : ?string
    {
        return $this->tag;
    }

    public function getId() : ?string
    {
        return $this->id;
    }

    public function getStyle(?string $key = null) : array|string
    {
        if (null !== $key) {
            return $this->style[$key] ?? [];
        }
        return $this->style;
    }

    public function getAppearance() : string
    {
        return $this->appearance;
    }
}
