<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class A extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\UrlTrait;

    protected string $tag = 'a';

    public function with(ComponentData $data) : void
    {
        $componentData = $this->expand($data->get());

        $this->addStyle($data->getStyle());
        $external = false;
        if (isset($componentData['onclick'])) {
            $this->setAttribute('onclick', $componentData['onclick']);
            unset($componentData['onclick']);
        }
        $href = $this->url($componentData['url'], $external);
        $this->setAttribute('href', $this->url($componentData['url'], $external));

        if (strpos($href, 'javascript:toggle') === 0) {
            $this->builder->dispatch(new Event('global-script', 'toggle', file_get_contents(__DIR__.'/../../js/toggle.js')));
        }
        if ($external) {
            $this->setAttribute('target', '_blank');
            $this->setAttribute('rel', 'noopener noreferrer');
        }
        unset($componentData['url']);
        $components = $this->builder->build($componentData, $data->getStyle(), $data->getAppearance());
        $this->addChildren($components);
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
