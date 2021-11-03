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
            unset($urlData['onclick']);
        }
        $href = $this->url($urlData['url'], $external);
        $this->setAttribute('href', $this->url($urlData['url'], $external));

        if (strpos($href, 'javascript:toggle') === 0) {
            $this->builder->dispatch(new Event('global-script', 'toggle', file_get_contents(__DIR__.'/../../js/toggle.js')));
        }
        if ($external) {
            $this->setAttribute('target', '_blank');
            $this->setAttribute('rel', 'noopener noreferrer');
        }
        unset($data['url'],$data['tel'],$data['mailto']);
        parent::build($data, $style, $appearance);
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
                    $expanded['text'] = $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::NATIONAL);
                    break;
                case 'mailto':
                    $expanded['url']  = 'mailto:'.$val;
                    $expanded['text'] = $val;
                    break;
                default:
                    $expanded[$key] = $val;
            }
        }
        return $expanded;
    }
}
