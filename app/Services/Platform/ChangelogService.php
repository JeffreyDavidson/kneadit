<?php

namespace App\Services\Platform;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChangelogService
{
    private const REPO = 'JeffreyDavidson/kneadit';

    /**
     * Get changelog entries, cached with stale-while-revalidate.
     *
     * @return Collection<int, array{date: string, version: string, title: string, items: array<int, string>}>
     */
    public function entries(): Collection
    {
        return Cache::flexible('changelog_entries', [3600, 7200], fn () => $this->fetchFromGitHub());
    }

    /**
     * @return Collection<int, array{date: string, version: string, title: string, items: array<int, string>}>
     */
    private function fetchFromGitHub(): Collection
    {
        try {
            $response = Http::withToken(config('services.github.token'))
                ->accept('application/vnd.github+json')
                ->get('https://api.github.com/repos/' . self::REPO . '/releases', [
                    'per_page' => 30,
                ]);

            if (! $response->successful()) {
                Log::warning('GitHub Releases API failed', [
                    'status' => $response->status(),
                ]);

                return $this->fallback();
            }

            /** @var array<int, array<string, mixed>> $releases */
            $releases = $response->json() ?? [];

            return collect($releases)
                ->reject(fn (array $release) => $release['draft'] ?? false)
                ->map(fn (array $release) => [
                    'date' => substr((string) ($release['published_at'] ?? $release['created_at'] ?? ''), 0, 10),
                    'version' => ltrim((string) ($release['tag_name'] ?? ''), 'v'),
                    'title' => (string) ($release['name'] ?? $release['tag_name'] ?? 'Release'),
                    'items' => $this->parseBodyToItems((string) ($release['body'] ?? '')),
                ])
                ->values();
        } catch (\Throwable $e) {
            Log::warning('GitHub Releases API error', [
                'error' => $e->getMessage(),
            ]);

            return $this->fallback();
        }
    }

    /**
     * Parse a GitHub Release markdown body into an array of bullet items.
     *
     * @return array<int, string>
     */
    private function parseBodyToItems(string $body): array
    {
        if (empty($body)) {
            return [];
        }

        return collect(explode("\n", $body))
            ->map(fn (string $line) => trim($line))
            ->filter(fn (string $line) => str_starts_with($line, '- ') || str_starts_with($line, '* '))
            ->map(fn (string $line) => ltrim($line, '-* '))
            ->values()
            ->all();
    }

    /**
     * Fallback to config-based changelog if GitHub API is unavailable.
     *
     * @return Collection<int, array{date: string, version: string, title: string, items: array<int, string>}>
     */
    private function fallback(): Collection
    {
        /** @var array<int, array{date: string, version: string, title: string, items: array<int, string>}> $configured */
        $configured = config('changelog', []);

        return collect($configured);
    }
}
