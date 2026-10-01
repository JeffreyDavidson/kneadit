<?php

namespace App\Services\Platform;

use App\Services\Platform\Contracts\ForgeClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class HttpForgeClient implements ForgeClient
{
    private string $token;

    private string $organization;

    private string $serverId;

    private string $siteId;

    public function __construct()
    {
        $this->token = $this->configString('services.forge.token');
        $this->organization = $this->configString('services.forge.organization');
        $this->serverId = $this->configString('services.forge.server_id');
        $this->siteId = $this->configString('services.forge.site_id');
    }

    public function addDomainAlias(string $domain): bool
    {
        try {
            if ($this->findDomainId($domain) !== null) {
                return true;
            }

            $response = $this->request()->post($this->domainsPath(), [
                'name' => $domain,
                'allow_wildcard_subdomains' => false,
                'www_redirect_type' => 'none',
            ]);

            if (! $response->successful()) {
                Log::error('Forge: failed to add domain', $this->failureContext($domain, $response));

                return false;
            }

            Log::info('Forge: domain added', ['domain' => $domain]);

            return true;
        } catch (Throwable $e) {
            Log::error('Forge: addDomainAlias failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

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
                Log::error('Forge: SSL request failed', $this->failureContext($domain, $response));

                return false;
            }

            Log::info('Forge: SSL certificate requested', ['domain' => $domain]);

            return true;
        } catch (Throwable $e) {
            Log::error('Forge: obtainSslCertificate failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function removeDomainAlias(string $domain): bool
    {
        try {
            $domainId = $this->findDomainId($domain);

            if ($domainId === null) {
                return true;
            }

            $response = $this->request()->delete("{$this->domainsPath()}/{$domainId}");

            if (! $response->successful()) {
                Log::error('Forge: failed to remove domain', $this->failureContext($domain, $response));

                return false;
            }

            return true;
        } catch (Throwable $e) {
            Log::error('Forge: removeDomainAlias failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    private function request(): PendingRequest
    {
        return Http::timeout(10)->connectTimeout(3)->retry(3, 100, $this->isTransientFailure(...), throw: false)->withToken($this->token)
            ->accept('application/vnd.api+json')
            ->contentType('application/vnd.api+json')
            ->baseUrl('https://forge.laravel.com/api');
    }

    /**
     * Only connection errors, 5xx and 429 responses are worth retrying; a 4xx will never succeed.
     */
    private function isTransientFailure(Throwable $exception): bool
    {
        if ($exception instanceof ConnectionException) {
            return true;
        }

        return $exception instanceof RequestException
            && ($exception->response->serverError() || $exception->response->status() === 429);
    }

    /**
     * @return array{domain: string, status: int, body: string}
     */
    private function failureContext(string $domain, Response $response): array
    {
        return ['domain' => $domain, 'status' => $response->status(), 'body' => $response->body()];
    }

    private function findDomainId(string $domain): ?string
    {
        $cursor = null;

        do {
            $response = $this->request()->get($this->domainsPath(), [
                'page' => Arr::whereNotNull(['size' => 100, 'cursor' => $cursor]),
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

    private function configString(string $key): string
    {
        $value = Config::get($key);

        return is_string($value) ? $value : '';
    }

    private function domainsPath(): string
    {
        return "/orgs/{$this->organization}/servers/{$this->serverId}/sites/{$this->siteId}/domains";
    }
}
