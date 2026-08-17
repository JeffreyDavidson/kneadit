<?php

namespace App\Services\Platform;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChangelogService
{
    private const string REPO = 'JeffreyDavidson/kneadit';

    /**
     * Get changelog entries, cached with stale-while-revalidate.
     *
     * @return Collection<int, array{date: string, version: string, title: string, items: array<int, string>}>
     */
    public function entries(): Collection
    {
        // Cache as a primitive array, not a Collection. config(cache.serializable_classes)
        // is false (Laravel's gadget-chain default), so any cached object hydrates as
        // __PHP_Incomplete_Class on read. Same class of bug as the Gallery cache (#302).
        return collect(Cache::flexible(
            'changelog_entries',
            [3600, 7200],
            fn () => $this->fetchFromGitHub()->all(),
        ));
    }

    /**
     * @return Collection<int, array{date: string, version: string, title: string, items: array<int, string>}>
     */
    private function fetchFromGitHub(): Collection
    {
        try {
            $response = Http::withToken(Config::string('services.github.token'))
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

            $releases = $response->json() ?? [];

            if (! is_array($releases)) {
                return $this->fallback();
            }

            $entries = [];

            foreach ($releases as $release) {
                if (! is_array($release) || ($release['draft'] ?? false) === true) {
                    continue;
                }

                $publishedAt = $this->stringValue($release['published_at'] ?? $release['created_at'] ?? '');
                $tagName = $this->stringValue($release['tag_name'] ?? '');
                $name = $this->stringValue($release['name'] ?? $tagName);
                $body = $this->stringValue($release['body'] ?? '');

                $entries[] = [
                    'date' => substr($publishedAt, 0, 10),
                    'version' => ltrim($tagName, 'v'),
                    'title' => $name !== '' ? $name : 'Release',
                    'items' => $this->parseBodyToItems($body),
                ];
            }

            return collect($entries);
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

    private function stringValue(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    /**
     * Fallback to config-based changelog if GitHub API is unavailable.
     *
     * @return Collection<int, array{date: string, version: string, title: string, items: array<int, string>}>
     */
    private function fallback(): Collection
    {
        $configured = Config::array('changelog', []);
        $entries = [];

        foreach ($configured as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $items = $entry['items'] ?? [];
            $normalizedItems = [];

            if (is_array($items)) {
                foreach ($items as $key => $item) {
                    if (is_int($key) && is_string($item)) {
                        $normalizedItems[$key] = $item;
                    }
                }
            }

            $entries[] = [
                'date' => $this->stringValue($entry['date'] ?? ''),
                'version' => $this->stringValue($entry['version'] ?? ''),
                'title' => $this->stringValue($entry['title'] ?? ''),
                'items' => $normalizedItems,
            ];
        }

        return collect($entries);
    }
}
