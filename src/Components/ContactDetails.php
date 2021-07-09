<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class ContactDetails extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\PathTrait;

    protected string $tag = 'ul';

    public function with(ComponentData $data) : void
    {
        $this->addStyle($data->getStyle('container'));
        $style = $data->getStyle();
        unset($style['container']);
        foreach ($this->normalize($data->get()) as $item) {
            $li = new Element('li');
            $li->addStyle($data->getStyle('li'));
            $components = $this->builder->build($item, $data->getStyle(), $data->getAppearance());
            $li->addChildren($components);

            // if (isset($item['url'])) {
            //     unset($item['icon']);
            //     $a = $this->builder->build('a', $item, ['a' => $style['link'] ?? []], $appearance);
            //     $li->addChild($a);
            // } else {
            //     $span = new Element('span');
            //     $span->setContent($item['text']);
            //     $li->addChild($span);
            // }
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
                        'text' => str_replace("\n", ', ', $address),
                    ];
                    if (isset($data['maps'])) {
                        $items[$attr]['url'] = $data['maps'];
                    }
                    break;
                case 'phone':
                    $phones    = is_array($data['phone']) && !ArrayHelper::isAssociative($data['phone']) ? $data['phone'] : [$data['phone']];
                    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
                    foreach ($phones as $i => $phone) {
                        if (is_string($phone)) {
                            $phone = ['number' => $phone];
                        }
                        try {
                            $numberProto = $phoneUtil->parse($phone['number'], '');
                        } catch (\libphonenumber\NumberParseException $e) {
                        }
                        $items['phone'.$i] = [
                            'icon' => 'zondicons/phone',
                            'text' => $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL),
                            'url'  => 'tel:'.$phone['number'],
                        ];
                    }
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
        unset($items['country'],$items['zip'],$items['maps'],$items['city']);
        foreach ($items as $key => $item) {
            if (isset($item['url'])) {
                $item['a'] = [
                    'text' => $item['text'],
                    'url' => $item['url'],
                ];
                unset($item['text'],$item['url']);
                $items[$key] = $item;
            }
        }
        return $items;
    }
}
