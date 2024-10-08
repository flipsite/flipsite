<?php

declare(strict_types=1);
namespace Flipsite\Components;

class Languages extends AbstractGroup
{
    protected string $tag  = 'nav';

    use Traits\SiteDataTrait;
    use Traits\PathTrait;

    public function normalize(string|int|bool|array $data): array
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
