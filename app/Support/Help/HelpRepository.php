<?php

namespace App\Support\Help;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Symfony\Component\Finder\SplFileInfo;

class HelpRepository
{
    private string $basePath;

    private GithubFlavoredMarkdownConverter $converter;

    public function __construct(?string $basePath = null, ?GithubFlavoredMarkdownConverter $converter = null)
    {
        $this->basePath = $basePath ?? resource_path('help');
        $this->converter = $converter ?? new GithubFlavoredMarkdownConverter([
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
        ]);
    }

    /**
     * Return all topics in the shape the Help Center blade view expects:
     *   [{title, slug, icon, color, articles: [{title, slug, content}]}]
     *
     * Topics are listed per config('help.topics'); articles are auto-discovered
     * from `resources/help/{topic-slug}/*.md` and sorted alphabetically.
     *
     * @return array<int, array{title: string, slug: string, icon: mixed, color: string, articles: array<int, array{title: string, slug: string, content: string}>}>
     */
    public function topics(): array
    {
        /** @var array<string, array{title: string, icon: mixed, color: string, sort: int}> $topics */
        $topics = Config::array('help.topics', []);

        $rows = [];
        foreach ($topics as $slug => $meta) {
            $rows[] = $meta + ['slug' => (string) $slug];
        }

        usort($rows, fn (array $a, array $b): int => $a['sort'] <=> $b['sort']);

        return array_map(fn (array $topic): array => [
            'title' => $topic['title'],
            'slug' => $topic['slug'],
            'icon' => $topic['icon'],
            'color' => $topic['color'],
            'articles' => $this->articlesFor($topic['slug']),
        ], $rows);
    }

    /**
     * Return a single article identified by "{topic-slug}/{article-slug}",
     * or null if the file doesn't exist.
     *
     * @return array{title: string, slug: string, content: string, topicSlug: string}|null
     */
    public function find(string $path): ?array
    {
        [$topicSlug, $articleSlug] = array_pad(explode('/', $path, 2), 2, null);

        if ($topicSlug === null || $articleSlug === null) {
            return null;
        }

        $file = "{$this->basePath}/{$topicSlug}/{$articleSlug}.md";

        if (! File::isFile($file)) {
            return null;
        }

        $parsed = $this->parse($file);

        return [
            'title' => $parsed['title'],
            'slug' => $articleSlug,
            'content' => $parsed['content'],
            'topicSlug' => $topicSlug,
        ];
    }

    /**
     * @return array<int, array{title: string, slug: string, content: string}>
     */
    private function articlesFor(string $topicSlug): array
    {
        $dir = "{$this->basePath}/{$topicSlug}";

        if (! File::isDirectory($dir)) {
            return [];
        }

        return collect(File::files($dir))
            ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'md')
            ->sortBy(fn (SplFileInfo $file): string => $file->getFilename())
            ->map(function (SplFileInfo $file): array {
                $parsed = $this->parse($file->getPathname());

                return [
                    'title' => $parsed['title'],
                    'slug' => Str::beforeLast($file->getFilename(), '.md'),
                    'content' => $parsed['content'],
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Extract the article title (first H1) and render the rest as HTML.
     *
     * @return array{title: string, content: string}
     */
    private function parse(string $file): array
    {
        $raw = File::get($file);

        // First H1 becomes the article title; strip it from the body so it
        // isn't duplicated when the converter renders the rest.
        $title = '';
        if (preg_match('/^#\s+(.+)$/m', $raw, $match)) {
            $title = trim($match[1]);
            $raw = (string) preg_replace('/^#\s+.+$/m', '', $raw, 1);
        }

        return [
            'title' => $title !== '' ? $title : Str::headline(Str::beforeLast(basename($file), '.md')),
            'content' => trim((string) $this->converter->convert(trim($raw))),
        ];
    }
}
