<?php

use Filament\Support\Icons\Heroicon;

/*
|--------------------------------------------------------------------------
| Tenant Help Center
|--------------------------------------------------------------------------
|
| Topics displayed on the in-product Help Center (filament.admin.pages.help-center).
| Articles themselves live as Markdown files at:
|     resources/help/{topic-slug}/{article-slug}.md
|
| The first `# Heading` in each markdown file becomes the article title; the
| rest of the body renders as the article content. To add a new topic:
|   1. Add an entry below with a kebab-case slug
|   2. mkdir resources/help/{slug}
|   3. Drop in {article-slug}.md files
|
*/

return [

    'topics' => [
        'getting-started' => [
            'title' => 'Getting Started',
            'icon' => Heroicon::OutlinedRocketLaunch,
            'color' => 'honey',
            'sort' => 1,
        ],
        'managing-orders' => [
            'title' => 'Managing Orders',
            'icon' => Heroicon::OutlinedClipboardDocumentList,
            'color' => 'brand-600',
            'sort' => 2,
        ],
        'storefront' => [
            'title' => 'Storefront',
            'icon' => Heroicon::OutlinedBuildingStorefront,
            'color' => 'emerald-700',
            'sort' => 3,
        ],
        'finances' => [
            'title' => 'Finances',
            'icon' => Heroicon::OutlinedBanknotes,
            'color' => 'indigo-500',
            'sort' => 4,
        ],
        'marketing' => [
            'title' => 'Marketing',
            'icon' => Heroicon::OutlinedMegaphone,
            'color' => 'pink-500',
            'sort' => 5,
        ],
        'tools' => [
            'title' => 'Tools',
            'icon' => Heroicon::OutlinedWrenchScrewdriver,
            'color' => 'cyan-600',
            'sort' => 6,
        ],
        'billing' => [
            'title' => 'Billing',
            'icon' => Heroicon::OutlinedCreditCard,
            'color' => 'violet-600',
            'sort' => 7,
        ],
        'faq' => [
            'title' => 'FAQ',
            'icon' => Heroicon::OutlinedChatBubbleLeftRight,
            'color' => 'amber-600',
            'sort' => 8,
        ],
    ],

    /*
    | Articles featured on the Help Center landing page above the topic grid.
    | Each entry is a "{topic-slug}/{article-slug}" path matching a markdown file.
    */
    'popular' => [
        'getting-started/setting-up-your-store',
        'managing-orders/using-quick-order',
        'storefront/customizing-your-theme',
        'billing/plans-and-pricing',
    ],

];
