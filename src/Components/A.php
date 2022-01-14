<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class A extends AbstractGroup
{
    use Traits\UrlTrait;

    protected string $tag = 'a';

    public function build(array $data, array $style, string $appearance) : void
    {
        $urlData  = $this->expand($data);
        $external = false;
        if (isset($urlData['onclick'])) {
            $this->setAttribute('onclick', $urlData['onclick']);
            if (strpos($urlData['onclick'], 'javascript:toggle') === 0) {
                $this->builder->dispatch(new Event('global-script', 'toggle', file_get_contents(__DIR__.'/../../js/toggle.js')));
            }
            unset($urlData['onclick']);
        }
        $href = isset($urlData['url']) ? $this->url((string)$urlData['url'], $external) : '';
        $this->setAttribute('href', $href);

        if ($external) {
            $this->setAttribute('target', '_blank');
            $this->setAttribute('rel', 'noopener noreferrer');
        }
        parent::build($urlData, $style, $appearance);
    }

    public function normalize(string|int|bool|array $data) : array
    {
        if (!is_array($data)) {
            return ['text' => $data, 'url' => '#'];
        }
        return $data;
    }

    private function expand(array $data) : array
    {
        $expanded = [];
        foreach ($data as $key => $val) {
            switch ($key) {
                case 'tel':
                    $expanded['url'] = 'tel:'.$val;
                    $phoneUtil       = \libphonenumber\PhoneNumberUtil::getInstance();
                    try {
                        $numberProto = $phoneUtil->parse($val, '');
                    } catch (\libphonenumber\NumberParseException $e) {
                    }
                    $format = \libphonenumber\PhoneNumberFormat::NATIONAL;
                    if (in_array('international', $data['flags'])) {
                        $format = \libphonenumber\PhoneNumberFormat::INTERNATIONAL;
                    } elseif (in_array('e164', $data['flags'])) {
                        $format = \libphonenumber\PhoneNumberFormat::E164;
                    }
                    $number = $phoneUtil->format($numberProto, $format);
                    if (isset($data['text'])) {
                        $expanded['text'] = sprintf($data['text'], $number);
                    } else {
                        $expanded['text'] = $number;
                    }
                    break;
                case 'mailto':
                    $expanded['url']  = 'mailto:'.$val;
                    if (isset($data['text'])) {
                        $expanded['text'] = sprintf($data['text'], $val);
                    } else {
                        $expanded['text'] = $val;
                    }
                    break;
                default:
                    $expanded[$key] = $val;
            }
        }
        return $expanded;
    }
}
