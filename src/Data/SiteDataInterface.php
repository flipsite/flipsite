<?php

declare(strict_types=1);

namespace Flipsite\Data;

use Flipsite\Utils\Language;
use Flipsite\Content\ContentSchema;

interface SiteDataInterface
{
    public function getDefaultLanguage(): Language;
    public function getLanguages(): array;
    public function getName(): string;
    public function getTitle(Language $language): ?string;
    public function getDescription(Language $language): ?string;
    public function getShare(): ?string;
    public function getSocial(): ?array;
    public function getSlugs(): Slugs;
    public function getFavicon(): null|string|array;
    public function getIntegrations(): ?array;
    public function getSections(string $page, Language $language): array;
    public function getColors(): array;
    public function getFonts(): array;
    public function getHtmlStyle(): array;
    public function getBodyStyle(string $page): array;
    public function getComponentStyle(string $component): array;
    public function getMeta(string $page, Language $language);
    public function getPageName(string $page, Language $language);
    public function getCode(string $position, string $page, bool $fallback): ?string;
    public function getContentSchemas(): array;
    public function getContentItems(ContentSchema $schema): array;
    public function getModifiedTimestamp() : int;
}
