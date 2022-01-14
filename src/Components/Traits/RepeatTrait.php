<?php

declare(strict_types=1);
namespace Flipsite\Components\Traits;

trait RepeatTrait
{
    protected function expandRepeat(array $data, array $tpl) : array
    {
        $expanded = [];
        foreach ($data as $key => $item) {
            if (is_string($item)) {
                $item = ['self' => $item];
            }
            $item['key'] = $key;
            $expanded[]  = $this->attachDataToTpl($tpl, new \Adbar\Dot($item));
        }
        return $expanded;
    }

    protected function attachDataToTpl(array $tpl, \Adbar\Dot $data)
    {
        foreach ($tpl as $attr => &$value) {
            if (is_array($value)) {
                $value = $this->attachDataToTpl($value, $data);
            } elseif (false !== mb_strpos((string)$value, '{')) {
                $matches = [];
                preg_match_all('/\{([^\{\}]+)\}/', $value, $matches);
                foreach ($matches[1] as $match) {
                    $replaceWith = $data->get($match);
                    if (is_array($replaceWith)) {
                        $value = $replaceWith;
                    } elseif ($replaceWith !== null) {
                        $value = str_replace('{'.$match.'}', (string)$replaceWith, (string)$value);
                    }
                }
            }
        }
        return $tpl;
    }

    protected function addTplDefaultData(array $data, array $default) : array
    {
        foreach ($default as $attr => $val) {
            if (!isset($data[$attr])) {
                $data[$attr] = $val;
            }
        }
        return $data;
    }
}
