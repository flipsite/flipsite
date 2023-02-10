<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Group extends AbstractGroup
{
    use Traits\UrlTrait;

    protected string $tag = 'div';

    public function normalize(string|int|bool|array $data) : array
    {
        if (!isset($data['_action'],$data['_target'])) {
            return $data;
        }
        $replace   = [];
        $this->tag = 'a';
        switch ($data['_action'] ?? []) {
            case 'page':
            case 'url':
            case 'url-blank':
                $external              = false;
                $data['_attr']['href'] = $this->url($data['_target'], $external);
                if ($external) {
                    $data['attr']['rel'] = 'noopener noreferrer';
                    if ('url-blank' === $data['_action']) {
                        $data['attr']['target'] = '_blank';
                    }
                }
                break;
            case 'tel':
                $phoneUtil  = \libphonenumber\PhoneNumberUtil::getInstance();
                try {
                    $tel         = '+'.trim($data['_target'], '+');
                    $numberProto = $phoneUtil->parse($tel, '');
                    $replace     = [
                        '{{tel}}'      => $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::NATIONAL),
                        '{{tel.int}}'  => $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL),
                        '{{tel.e164}}' => $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::E164),
                    ];
                } catch (\libphonenumber\NumberParseException $e) {
                }
                $data['_attr']['href'] = 'tel:'.$tel;
                break;
            case 'mailto':
                $data['_attr']['href'] = 'mailto:'.$data['_target'];
                $replace               = [
                    '{{email}}' => $data['_target']
                ];
                break;
            case 'scroll':
                $data['_attr']['href'] = '#'.trim($data['_target'], '#');
                break;
            case 'submit':
                $this->tag             = 'button';
                $data['_attr']['type'] = 'submit';
                break;
            default:
                $data['_attr']['href'] = '#';
        }
        unset($data['_action'], $data['_target']);
        return $data;
    }
}
