<?php

declare(strict_types=1);
namespace Flipsite\Components\Traits;

use Flipsite\Utils\ArrayHelper;

trait RepeatTrait
{
    protected function expandRepeat(array $data, array $tpl, bool $unsetNotFound = true) : array
    {
        unset($tpl['_unsetEmpty']);
        $expanded = [];
        foreach ($data as $key => $item) {
            if (is_string($item)) {
                $item = ['self' => $item];
            }
            $item['key'] = $key;
            $expanded[]  = $this->attachDataToTpl($tpl, new \Adbar\Dot($item), $unsetNotFound);
        }
        return $expanded;
    }

    protected function attachDataToTpl(array $tpl, \Adbar\Dot $data, bool $unsetNotFound = true)
    {
        $unset  = [];
        $subTpl = null;
        $unset  = [];
        unset($tpl['_unsetEmpty']);
        foreach ($tpl as $attr => &$value) {
            if ($attr === 'tpl') {
                $subTpl = $value;
            } elseif (is_array($value)) {
                $value = $this->attachDataToTpl($value, $data, $unsetNotFound);
            } elseif (false !== mb_strpos((string)$value, '{')) {
                $matches = [];
                preg_match_all('/\{([^\{\}]+)\}/', $value, $matches);
                foreach ($matches[1] as $match) {
                    $replaceWith = $data->get($match);
                    if (is_array($replaceWith)) {
                        $value = $replaceWith;
                    } elseif ($replaceWith !== null) {
                        $value = str_replace('{'.$match.'}', (string)$replaceWith, (string)$value);
                    } elseif ('self' === $match && $value == '{self}') {
                        $value = $data->get();
                    } elseif ($unsetNotFound) {
                        $unset[] = $attr;
                    } else {
                        $value = str_replace('{'.$match.'}', (string)$replaceWith, '');
                    }
                }
            }
        }
        if ($subTpl) {
            unset($tpl['tpl']);
            $tpl =  ArrayHelper::merge($tpl, $subTpl);
        }
        foreach ($unset as $u) {
            unset($tpl[$u]);
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
