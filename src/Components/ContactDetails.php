<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class ContactDetails extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\PathTrait;

    protected string $type = 'ul';

    public function build(array $data, array $style, array $flags, string $appearance = 'light') : void
    {
        $this->addStyle($style['container'] ?? []);
        foreach ($data as $item) {
            $li = new Element('li');
            $li->addStyle($style['li'] ?? []);
            $icon = $this->builder->build('svg', $item['icon'], $style['icon'] ?? [], $appearance);
            $li->addChild($icon);
            if (isset($item['url'])) {
                unset($item['icon']);
                $a = $this->builder->build('a', $item, $style['link'] ?? [], $appearance);
                $li->addChild($a);
            } else {
                $span = new Element('span');
                $span->setContent($item['text']);
                $li->addChild($span);
            }
            $this->addChild($li);
        }
    }

    protected function normalize($data) : array
    {
        $items = [];
        foreach ($data as $attr => $val) {
            if (isset($items[$attr])) {
                continue;
            }
            switch ($attr) {
                case 'company':
                    $items[$attr] = [
                        'icon' => 'zondicons/home',
                        'text' => $val,
                    ];
                    break;
                case 'person':
                    $items[$attr] = [
                        'icon' => 'zondicons/user',
                        'text' => $val,
                    ];
                    break;
                case 'address':
                    $addressFormatter = new \Adamlc\AddressFormat\Format();
                    $addressFormatter->setLocale($data['country']);
                    $addressFormatter['STREET_ADDRESS'] = $data['address'] ?? '';
                    $addressFormatter['LOCALITY']       = $data['city']    ?? '';
                    $addressFormatter['POSTAL_CODE']    = $data['zip']     ?? '';
                    $addressFormatter['ADMIN_AREA']     = $data['state']   ?? '';
                    $addressFormatter['COUNTRY']        = $data['country'] ?? '';
                    $address                            = $addressFormatter->formatAddress(false);
                    $items[$attr]                       = [
                        'icon' => 'zondicons/location',
                        'text' => str_replace("\n", '<br>', $address),
                    ];
                    if (isset($data['maps'])) {
                        $items[$attr]['url'] = $data['maps'];
                    }
                    break;
                case 'phone':
                    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
                    try {
                        $numberProto = $phoneUtil->parse($val, '');
                    } catch (\libphonenumber\NumberParseException $e) {
                    }
                    $items[$attr] = [
                        'icon' => 'zondicons/phone',
                        'text' => $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL),
                        'url'  => 'tel:'.$val,
                    ];
                    break;
                case 'email':
                    $items[$attr] = [
                        'icon' => 'zondicons/envelope',
                        'text' => $val,
                        'url'  => 'mailto:'.$val,
                    ];
                    break;
            }
        }
        return $items;
    }

    private function getAddress(array $data) : array
    {
    }
}
