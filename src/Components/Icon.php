<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Builders\Event;
use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

class Icon extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\AssetsTrait;

    protected string $tag   = 'svg';
    protected bool $oneline = true;

    public function normalize(array $data): array
    {
        if (isset($data['value'])) {
            $data['src'] = $data['value'];
            unset($data['value']);
        }
        if (isset($data['fallback']) && strpos($data['src'], '.svg') === false) {
            $data['src'] = $data['fallback'];
            unset($data['fallback']);
        }
        return $data;
    }

    public function getDefaultStyle(): array
    {
        return ['fill' => 'fill-current'];
    }

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data = $component->getData();
        try {
            $svg = $this->assets->getSvg($data['src'] ?? '');
        } catch (\Exception $e) {
            return;
        }
        $this->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        if ($svg) {
            $this->setMeta('svgHash', $svg->getHash());
            $this->setAttribute('viewBox', $svg->getViewbox());
            $this->setContent('<use xlink:href="#'.$svg->getHash().'"></use>');
            $this->builder->dispatch(new Event('svg', $svg->getHash(), $svg->getDef()));
        } else {
            $this->setAttribute('viewBox', '0 0 100 100');
            $this->setContent('<use xlink:href="#empty"></use>');
            $this->builder->dispatch(new Event('svg', 'empty', '<rect width="100%" height="100%" fill="#eee" />'));
        }
    }
}
