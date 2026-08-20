<?php

namespace App\Services\Settings;

use App\DataTransferObjects\Settings\SettingValue;
use App\Models\Platform\Setting;
use App\Models\Platform\Tenant;

class SettingsManager extends AbstractSettingsManager
{
    public function __construct(
        private TenantSettingCipher $cipher,
    ) {}

    protected function cacheKey(): string
    {
        $tenant = tenant();

        return $tenant instanceof Tenant ? $tenant->id : 'central';
    }

    protected function modelClass(): string
    {
        return Setting::class;
    }

    public function pageContent(string $page, string $key, mixed $default = ''): mixed
    {
        $content = $this->pageContentData();
        $pageContent = $content[$page] ?? null;

        return is_array($pageContent) ? ($pageContent[$key] ?? $default) : $default;
    }

    /** @return array<string, mixed> */
    public function pageContentAll(string $page): array
    {
        $content = $this->pageContentData();
        $pageContent = $content[$page] ?? null;

        return SettingValue::map($pageContent);
    }

    /** @return array<string, mixed> */
    private function pageContentData(): array
    {
        $json = $this->get('page_content', '{}');

        if (! is_string($json)) {
            return [];
        }

        return SettingValue::decodedMap($json);
    }

    protected function valueForStorage(string $key, mixed $value): mixed
    {
        return $this->cipher->encrypt($key, $value);
    }

    protected function valueFromStorage(string $key, mixed $value): mixed
    {
        return $this->cipher->decrypt($key, $value);
    }
}
