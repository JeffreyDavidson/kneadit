<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
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
        $this->token = config('services.forge.token', '');
        $this->serverId = config('services.forge.server_id', '');
        $this->siteId = config('services.forge.site_id', '');
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

            $site = $response->json('site');
            $currentAliases = $site['aliases'] ?? [];

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

            $site = $response->json('site');
            $currentAliases = $site['aliases'] ?? [];
            $currentAliases = array_values(array_filter($currentAliases, fn (string $a) => $a !== $domain));

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
}
