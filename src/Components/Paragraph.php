<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

final class Paragraph extends AbstractComponent
{
    use Traits\MarkdownTrait;
    use Traits\SiteDataTrait;
    use Traits\DateFilterTrait;
    use Traits\PhoneFilterTrait;
    use Traits\CheckTextTrait;

    protected string $tag = 'p';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data = $component->getData();
        $data['value'] ??= $data['fallback'] ?? '';
        if (!$data['value']) {
            $this->render = false;
            return;
        }
        $data['value'] = $this->checkText($data['value'], 'Paragraph');
        if (isset($data['formatDate'])) {
            $data['value'] = $this->parseDate($data['value'], $data['formatDate']);
        }
        if (isset($data['formatPhone'])) {
            $data['value'] = $this->parsePhone($data['value'], $data['formatPhone']);
        }
        $style      = $component->getStyle();
        $magicLinks = $data['magicLinks'] ?? false;
        $html       = $this->getMarkdownLine($data['value'] ?? '', ['a', 'strong', 'em', 'code'], $style, $inherited->getAppearance(), $inherited->hasATag(), $magicLinks);
        $this->setContent((string)$html);
    }
}
