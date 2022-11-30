<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class A extends AbstractGroup
{
    use Traits\UrlTrait;

    protected string $tag = 'a';

    public function build(array $data, array $style, string $appearance) : void
    {
        // Old fallback
        if (isset($data['url'])) {
            $data['href'] = $data['url'];
            unset($data['url']);
        }
        $urlData  = $this->expand($data);
        $external = false;
        $href     = isset($urlData['href']) ? $this->url((string)$urlData['href'], $external) : '';
        if ($href) {
            $this->setAttribute('href', $href);
            if ($external) {
                $this->setAttribute('target', '_blank');
                $this->setAttribute('rel', 'noopener noreferrer');
            }
        } else {
            $this->tag = 'div';
        }
        parent::build($urlData, $style, $appearance);
    }

    public function normalize(string|int|bool|array $data) : array
    {
        if (!is_array($data)) {
            return ['text' => $data, 'href' => '#'];
        }
        return $data;
    }

    private function expand(array $data) : array
    {
        $expanded = [];
        foreach ($data as $key => $val) {
            switch ($key) {
                case 'tel':
                    $expanded['href'] = 'tel:'.$val;
                    $phoneUtil       = \libphonenumber\PhoneNumberUtil::getInstance();
                    try {
                        $numberProto = $phoneUtil->parse($val, '');
                    } catch (\libphonenumber\NumberParseException $e) {
                    }
                    $format = \libphonenumber\PhoneNumberFormat::NATIONAL;
                    // if (in_array('international', [])) {
                    //     $format = \libphonenumber\PhoneNumberFormat::INTERNATIONAL;
                    // } elseif (in_array('e164', [])) {
                    //     $format = \libphonenumber\PhoneNumberFormat::E164;
                    // }
                    $number = $phoneUtil->format($numberProto, $format);
                    if (isset($data['text'])) {
                        $expanded['text'] = sprintf($data['text'], $number);
                    } else {
                        $expanded['text'] = $number;
                    }
                    break;
                case 'mailto':
                    $expanded['href']  = 'mailto:'.$val;
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
