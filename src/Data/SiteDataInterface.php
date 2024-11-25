<?php

declare(strict_types=1);
namespace Flipsite\Data;

use Flipsite\Utils\Language;
use Flipsite\Content\Collection;

interface SiteDataInterface
{
    public function getDefaultLanguage(): Language;

    public function getLanguages(): array;

    public function getName(): string;

    public function getTitle(?Language $language = null): ?string;

    public function getDescription(?Language $language = null): ?string;

    public function getShare(): ?string;

    public function getThemeColor(): ?string;

    public function getAppleAppId(): ?string;

    public function getSocial(): ?array;

    public function getSlugs(): Slugs;

    public function getExpanded(string $page): ?array;

    public function getFavicon(): null|string|array;

    public function getIntegrations(): ?array;

    public function getRedirects(): ?array;

    public function getCompile(): ?array;

    public function getPublish(): ?array;

    public function getSections(string $page, ?Language $language = null): array;

    public function getColors(): array;

    public function getFonts(): array;

    public function getThemeSettings(): array;

    public function getAppearance(?string $page = null): string;

    public function getHtmlStyle(?string $page = null): array;

    public function getBodyStyle(?string $page = null): array;

    public function getComponentStyle(string $component): array;

    public function getMeta(string $page, ?Language $language = null): ?array;

    public function getPageMeta(string $page, ?Language $language = null): ?array;

    public function getHiddenPages(): array;

    public function getPageName(string $page, ?Language $language = null);

    public function getCode(string $position, string $page, bool $fallback): ?string;

    public function getCollectionIds(): array;

    public function getCollection(string $collectionId, ?Language $language = null): ?Collection;

    public function getModifiedTimestamp(): int;
}
