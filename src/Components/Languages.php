<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

class Languages extends AbstractGroup
{
    use Traits\SiteDataTrait;
    use Traits\PathTrait;
    protected string $tag  = 'nav';

    public function normalize(array $data): array
    {
        $repeat    = [];
        $languages = $this->siteData->getLanguages();

        $active               = $this->path->getLanguage();
        $hideActiveLanguage   = $data['_options']['hideActiveLanguage'] ?? false;

        foreach ($languages as $language) {
            if (!$hideActiveLanguage || !$language->isSame($active)) {
                $repeat[] = [
                    'slug' => (string)$language,
                    'code' => (string)$language,
                    'name' => $language->getInLanguage(),
                ];
            }
        }

        return $this->normalizeRepeat($data, $repeat);
    }
}
