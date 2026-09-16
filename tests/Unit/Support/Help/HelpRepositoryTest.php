<?php

use App\Support\Help\HelpRepository;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    test()->tmp = sys_get_temp_dir() . '/help-' . uniqid();
    File::ensureDirectoryExists(test()->tmp . '/getting-started');
    File::ensureDirectoryExists(test()->tmp . '/billing');

    File::put(test()->tmp . '/getting-started/setup.md', <<<'MD'
# Setup Guide

Welcome! Open **Settings** to begin.

- Step one
- Step two
MD);

    File::put(test()->tmp . '/billing/plans.md', "# Plans\n\nWe have three.\n");

    config()->set('help.topics', [
        'getting-started' => [
            'title' => 'Getting Started',
            'icon' => Heroicon::OutlinedRocketLaunch,
            'color' => 'honey',
            'sort' => 1,
        ],
        'billing' => [
            'title' => 'Billing',
            'icon' => Heroicon::OutlinedCreditCard,
            'color' => 'violet-600',
            'sort' => 2,
        ],
    ]);
});

afterEach(function () {
    File::deleteDirectory(test()->tmp);
});

test('topics() returns config-ordered topics with discovered articles', function () {
    $repo = new HelpRepository(test()->tmp);

    $topics = $repo->topics();

    expect($topics)->toHaveCount(2)
        ->and($topics[0]['slug'])->toBe('getting-started')
        ->and($topics[0]['title'])->toBe('Getting Started')
        ->and($topics[0]['articles'])->toHaveCount(1)
        ->and($topics[0]['articles'][0]['slug'])->toBe('setup')
        ->and($topics[0]['articles'][0]['title'])->toBe('Setup Guide')
        ->and($topics[0]['articles'][0]['content'])->toContain('<strong>Settings</strong>')
        ->and($topics[1]['slug'])->toBe('billing');
});

test('articles are alphabetized and limited to Markdown files', function () {
    File::put(test()->tmp . '/billing/z-last.md', "# Last\n");
    File::put(test()->tmp . '/billing/a-first.md', "# First\n");
    File::put(test()->tmp . '/billing/ignored.txt', 'Not a help article');

    $topics = (new HelpRepository(test()->tmp))->topics();

    expect($topics[1]['articles'])->toHaveCount(3)
        ->and(array_column($topics[1]['articles'], 'slug'))
        ->toBe(['a-first', 'plans', 'z-last']);
});

test('find() returns parsed article or null', function () {
    $repo = new HelpRepository(test()->tmp);

    $hit = $repo->find('billing/plans');

    if ($hit === null) {
        throw new RuntimeException('Expected the billing article to exist.');
    }

    expect($hit['title'])->toBe('Plans')
        ->and($hit['topicSlug'])->toBe('billing');

    expect($repo->find('billing/missing'))->toBeNull();
    expect($repo->find('nonsense'))->toBeNull();
});

test('articles missing an H1 fall back to a slug-derived title', function () {
    File::put(test()->tmp . '/billing/no-heading.md', "Just a paragraph, no heading.\n");

    $repo = new HelpRepository(test()->tmp);
    $article = $repo->find('billing/no-heading');

    if ($article === null) {
        throw new RuntimeException('Expected the article without a heading to exist.');
    }

    expect($article['title'])->toBe('No Heading');
});
