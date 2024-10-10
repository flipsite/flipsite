<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Builders\Event;

final class DidYouMean extends AbstractComponent
{
    use Traits\MarkdownTrait;
    use Traits\ClassesTrait;
    use Traits\SiteDataTrait;
    use Traits\BuilderTrait;
    use Traits\EnvironmentTrait;

    protected string $tag = 'p';

    public function build(array $data, array $style, array $options): void
    {
        $value     = $this->getMarkdownLine($data['value'] ?? '', $style['value'] ?? [], $options['appearance']);
        $value     = $this->addClassesToHtml($value, ['a', 'strong'], $style, $options['appearance']);
        $this->setContent((string)$value);
        $this->addStyle($style);

        $pages = [];
        foreach ($this->siteData->getSlugs()->getAll() as $page => $locs) {
            foreach ($locs as $loc) {
                if ($loc) {
                    $pages[] = $loc;
                }
            }
        }

        $this->setAttribute('data-did-you-mean', $data['fallback'] ?? '');
        $this->setAttribute('data-root', $this->environment->getAbsoluteUrl(''));

        $this->builder->dispatch(new Event('global-script', 'sitemap', 'const sitemap = '.json_encode($pages).';'));
        $this->builder->dispatch(new Event('ready-script', 'toggle', file_get_contents(__DIR__ . '/../../js/dist/didYouMean.min.js')));
    }

    public function normalize(string|int|bool|array $data): array
    {
        if (is_string($data)) {
            return ['value' => $data];
        }
        return $data;
    }

    public function getDefaultStyle(): array
    {
        $style     = [];
        $bodyStyle = $this->siteData->getBodyStyle();
        if (isset($bodyStyle['textColor'])) {
            $style['textColor'] = $bodyStyle['textColor'];
        }
        if (isset($bodyStyle['dark']['textColor'])) {
            $style['dark']              = [];
            $style['dark']['textColor'] = $bodyStyle['dark']['textColor'];
        }
        return $style;
    }
}
