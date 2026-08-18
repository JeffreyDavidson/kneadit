<?php

namespace App\Filament\Pages\Platform;

use App\Support\Help\HelpRepository;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Url;

class HelpCenter extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?string $navigationLabel = 'Help';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.platform.help-center';

    /** Deep-link target: `?article={topic-slug}/{article-slug}` opens that article on mount. */
    #[Url(as: 'article')]
    public ?string $articlePath = null;

    /** @return array<int, mixed> */
    public function getTopics(): array
    {
        return resolve(HelpRepository::class)->topics();
    }

    /** @return array<int, array{topic: string, title: string, slug: string}> */
    public function getPopularArticles(): array
    {
        $repo = resolve(HelpRepository::class);
        $titles = collect($this->getTopics())->keyBy('slug');

        return collect((array) config('help.popular', []))
            ->map(fn (string $path) => $repo->find($path))
            ->filter()
            ->map(fn (array $a) => [
                'topic' => $titles[$a['topicSlug']]['title'] ?? '',
                'title' => $a['title'],
                'slug' => "{$a['topicSlug']}/{$a['slug']}",
            ])
            ->values()
            ->all();
    }
}
