<?php

namespace App\Filament\Pages\Platform;

use App\Support\Help\HelpRepository;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Config;
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

    /** @return array<int, array{title: string, slug: string, icon: mixed, color: string, articles: array<int, array{title: string, slug: string, content: string}>}> */
    public function getTopics(): array
    {
        return resolve(HelpRepository::class)->topics();
    }

    /** @return array<int, array{topic: string, title: string, slug: string}> */
    public function getPopularArticles(): array
    {
        $repo = resolve(HelpRepository::class);
        $titles = collect($this->getTopics())->keyBy('slug');
        $articles = [];

        foreach (Config::array('help.popular', []) as $path) {
            if (! is_string($path)) {
                continue;
            }

            $article = $repo->find($path);

            if ($article === null) {
                continue;
            }

            $topic = $titles->get($article['topicSlug']);
            $articles[] = [
                'topic' => is_array($topic) ? $topic['title'] : '',
                'title' => $article['title'],
                'slug' => "{$article['topicSlug']}/{$article['slug']}",
            ];
        }

        return $articles;
    }
}
