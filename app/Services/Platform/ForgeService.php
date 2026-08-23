<?php

namespace App\Services\Platform;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ForgeService
{
    protected string $baseUrl = 'https://forge.laravel.com/api';

    protected string $token;

    protected string $organization;

    protected string $serverId;

    protected string $siteId;

    public function __construct()
    {
        $this->token = $this->configString('services.forge.token');
        $this->organization = $this->configString('services.forge.organization');
        $this->serverId = $this->configString('services.forge.server_id');
        $this->siteId = $this->configString('services.forge.site_id');
    }

    public static function isConfigured(): bool
    {
        return ! empty(config('services.forge.token'))
            && ! empty(config('services.forge.organization'))
            && ! empty(config('services.forge.server_id'))
            && ! empty(config('services.forge.site_id'));
    }

    protected function request(): PendingRequest
    {
        return Http::timeout(10)->connectTimeout(3)->retry(3, 100)->withToken($this->token)
            ->accept('application/vnd.api+json')
            ->contentType('application/vnd.api+json')
            ->baseUrl($this->baseUrl);
    }

    /**
     * Add a custom domain to the Forge site.
     */
    public function addDomainAlias(string $domain): bool
    {
        try {
            if ($this->findDomainId($domain) !== null) {
                return true;
            }

            $response = $this->request()->post(
                $this->domainsPath(),
                [
                    'name' => $domain,
                    'allow_wildcard_subdomains' => false,
                    'www_redirect_type' => 'none',
                ],
            );

            if (! $response->successful()) {
                Log::error('Forge: failed to add domain', [
                    'domain' => $domain,
                    'status' => $response->status(),
                ]);

                return false;
            }

            Log::info('Forge: domain added', ['domain' => $domain]);

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
            $domainId = $this->findDomainId($domain);

            if ($domainId === null) {
                Log::error('Forge: domain not found for SSL request', ['domain' => $domain]);

                return false;
            }

            $response = $this->request()->post(
                "{$this->domainsPath()}/{$domainId}/certificates",
                ['type' => 'letsencrypt'],
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
     * Remove a custom domain from the Forge site.
     */
    public function removeDomainAlias(string $domain): bool
    {
        try {
            $domainId = $this->findDomainId($domain);

            if ($domainId === null) {
                return true;
            }

            return $this->request()
                ->delete("{$this->domainsPath()}/{$domainId}")
                ->successful();
        } catch (\Throwable $e) {
            Log::error('Forge: removeDomainAlias failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    private function configString(string $key): string
    {
        return Config::string($key, '');
    }

    private function domainsPath(): string
    {
        return "/orgs/{$this->organization}/servers/{$this->serverId}/sites/{$this->siteId}/domains";
    }

    private function findDomainId(string $domain): ?string
    {
        $cursor = null;

        do {
            $response = $this->request()->get($this->domainsPath(), [
                'page' => Arr::whereNotNull([
                    'size' => 100,
                    'cursor' => $cursor,
                ]),
            ])->throw();

            $records = $response->json('data');
            $record = collect(is_array($records) ? $records : [])->first(
                fn (mixed $record): bool => is_array($record)
                    && Arr::get($record, 'attributes.name') === $domain,
            );

            if (is_array($record)) {
                $id = Arr::get($record, 'id');

                if (is_int($id) || is_string($id)) {
                    return (string) $id;
                }
            }

            $nextCursor = $response->json('meta.next_cursor');
            $cursor = is_string($nextCursor) && $nextCursor !== '' ? $nextCursor : null;
        } while ($cursor !== null);

        return null;
    }
}
