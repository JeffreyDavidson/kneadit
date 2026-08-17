<?php

use App\Support\Help\HelpRepository;
use Filament\Support\Icons\Heroicon;

beforeEach(function () {
    test()->tmp = sys_get_temp_dir() . '/help-' . uniqid();
    mkdir(test()->tmp . '/getting-started', 0755, true);
    mkdir(test()->tmp . '/billing', 0755, true);

    file_put_contents(test()->tmp . '/getting-started/setup.md', <<<'MD'
# Setup Guide

Welcome! Open **Settings** to begin.

- Step one
- Step two
MD);

    file_put_contents(test()->tmp . '/billing/plans.md', "# Plans\n\nWe have three.\n");

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
    array_map('unlink', glob(test()->tmp . '/*/*.md') ?: []);
    array_map('rmdir', glob(test()->tmp . '/*') ?: []);
    @rmdir(test()->tmp);
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
    file_put_contents(test()->tmp . '/billing/no-heading.md', "Just a paragraph, no heading.\n");

    $repo = new HelpRepository(test()->tmp);
    $article = $repo->find('billing/no-heading');

    if ($article === null) {
        throw new RuntimeException('Expected the article without a heading to exist.');
    }

    expect($article['title'])->toBe('No Heading');
});
