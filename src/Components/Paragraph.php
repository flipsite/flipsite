<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

final class Paragraph extends AbstractComponent
{
    use Traits\MarkdownTrait;
    use Traits\ClassesTrait;
    use Traits\SiteDataTrait;

    protected string $tag = 'p';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data  = $component->getData();
        $html  = $this->getMarkdownLine($data['value'] ?? '');
        $html  = $this->addClassesToHtml($html, ['a', 'strong', 'em', 'code'], $component->getStyle(), $inherited->getAppearance());
        $this->setContent((string)$html);
    }
}
