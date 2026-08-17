<?php

namespace App\Services\Platform;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ForgeService
{
    protected string $baseUrl = 'https://forge.laravel.com/api/v1';

    protected string $token;

    protected string $serverId;

    protected string $siteId;

    public function __construct()
    {
        $this->token = $this->configString('services.forge.token');
        $this->serverId = $this->configString('services.forge.server_id');
        $this->siteId = $this->configString('services.forge.site_id');
    }

    public static function isConfigured(): bool
    {
        return ! empty(config('services.forge.token'))
            && ! empty(config('services.forge.server_id'))
            && ! empty(config('services.forge.site_id'));
    }

    protected function request(): PendingRequest
    {
        return Http::timeout(10)->connectTimeout(3)->retry(3, 100)->withToken($this->token)
            ->acceptJson()
            ->baseUrl($this->baseUrl);
    }

    /**
     * Add a custom domain alias to the site's nginx config.
     */
    public function addDomainAlias(string $domain): bool
    {
        try {
            // Get current site config
            $response = $this->request()->get("/servers/{$this->serverId}/sites/{$this->siteId}");

            if (! $response->successful()) {
                Log::error('Forge: failed to get site', ['status' => $response->status()]);

                return false;
            }

            $currentAliases = $this->aliasesFromSite($response->json('site'));

            if (in_array($domain, $currentAliases)) {
                return true; // Already added
            }

            $currentAliases[] = $domain;

            // Update site aliases
            $updateResponse = $this->request()->put(
                "/servers/{$this->serverId}/sites/{$this->siteId}",
                ['aliases' => $currentAliases],
            );

            if (! $updateResponse->successful()) {
                Log::error('Forge: failed to add alias', [
                    'domain' => $domain,
                    'status' => $updateResponse->status(),
                ]);

                return false;
            }

            Log::info('Forge: domain alias added', ['domain' => $domain]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Forge: addDomainAlias failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Request an SSL certificate for a custom domain.
     */
    public function obtainSslCertificate(string $domain): bool
    {
        try {
            $response = $this->request()->post(
                "/servers/{$this->serverId}/sites/{$this->siteId}/certificates/letsencrypt",
                ['domains' => [$domain]],
            );

            if (! $response->successful()) {
                Log::error('Forge: SSL request failed', [
                    'domain' => $domain,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            Log::info('Forge: SSL certificate requested', ['domain' => $domain]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Forge: obtainSslCertificate failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Remove a domain alias from the site.
     */
    public function removeDomainAlias(string $domain): bool
    {
        try {
            $response = $this->request()->get("/servers/{$this->serverId}/sites/{$this->siteId}");

            if (! $response->successful()) {
                return false;
            }

            $currentAliases = array_values(array_filter(
                $this->aliasesFromSite($response->json('site')),
                fn (string $alias): bool => $alias !== $domain,
            ));

            $updateResponse = $this->request()->put(
                "/servers/{$this->serverId}/sites/{$this->siteId}",
                ['aliases' => $currentAliases],
            );

            return $updateResponse->successful();
        } catch (\Throwable $e) {
            Log::error('Forge: removeDomainAlias failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    private function configString(string $key): string
    {
        return Config::string($key, '');
    }

    /** @return list<string> */
    private function aliasesFromSite(mixed $site): array
    {
        if (! is_array($site)) {
            return [];
        }

        $aliases = $site['aliases'] ?? null;

        if (! is_array($aliases)) {
            return [];
        }

        return array_values(array_filter($aliases, is_string(...)));
    }
}
