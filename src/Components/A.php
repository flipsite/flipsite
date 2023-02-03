<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class A extends AbstractGroup
{
    use Traits\UrlTrait;

    protected string $tag = 'a';

    public function normalize(string|int|bool|array $data) : array
    {
        // TODO until nav is improved
        if (isset($data['url'])) {
            $data['action'] = 'page';
            $data['target'] = $data['url'];
            unset($data['url']);
        }

        $href = null;
        $replace = [];
        $data['attr'] ??= [];
        switch ($data['action'] ?? []) {
            case 'page':
            case 'url':
            case 'url-blank':
                $external = false;
                $href = $this->url($data['target'], $external);
                if ($external) {
                    $data['attr']['rel'] = 'noopener noreferrer';
                    if ('url-blank' === $data['action']) {
                        $data['attr']['target'] = '_blank';
                    }
                }
                break;
            case 'tel':
                $tel = '+'.trim($data['target'], '+');
                $phoneUtil  = \libphonenumber\PhoneNumberUtil::getInstance();
                try {
                    $numberProto = $phoneUtil->parse($tel, '');
                } catch (\libphonenumber\NumberParseException $e) {
                }
                $href = 'tel:'.$tel;
                $replace = [
                    '{{tel}}' => $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::NATIONAL),
                    '{{tel.int}}' => $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL),
                    '{{tel.e164}}' => $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::E164),
                ];
                break;
            case 'mailto':
                $href = 'mailto:'.$data['target'];
                $replace = [
                    '{{email}}' => $data['target']
                ];
                break;
            default:
                $href = '#';
        }
        $data['_attr']['href'] = $href;
        unset($data['action']);
        unset($data['traget']);
        return $data;
    }
}
