<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

final class Date extends AbstractComponent
{
    use Traits\PathTrait;
    use Traits\DateFilterTrait;
    protected string $tag  = 'time';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data = $component->getData();
        $dateStr = $data['value'] ?? date('Y-m-d');

        $date = $this->parseDate(
            $dateStr,
            $this->path->getLanguage(),
            $data['format'] ?? 'none'
        );

        if (isset($data['content'])) {
            $this->setContent(str_replace('[date]', $date, $data['content']));
        } else {
            $this->setContent($date);
        }
    }
}
