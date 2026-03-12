<?php

namespace Tests\Feature;

use Tests\CentralTestCase;

class ChangelogTest extends CentralTestCase
{
    public function test_changelog_page_loads(): void
    {
        $response = $this->get('/changelog');

        $response->assertStatus(200);
    }

    public function test_changelog_displays_version_entries(): void
    {
        $response = $this->get('/changelog');

        $response->assertSee('1.4.0');
        $response->assertSee('1.0.0');
    }

    public function test_changelog_displays_entry_titles(): void
    {
        $response = $this->get('/changelog');

        $response->assertSee('KneadIt Launch');
    }

    public function test_changelog_config_has_entries(): void
    {
        $entries = config('changelog');

        $this->assertNotEmpty($entries);
        $this->assertArrayHasKey('date', $entries[0]);
        $this->assertArrayHasKey('version', $entries[0]);
        $this->assertArrayHasKey('title', $entries[0]);
        $this->assertArrayHasKey('items', $entries[0]);
    }

    public function test_changelog_link_in_footer(): void
    {
        $layout = file_get_contents(resource_path('views/blog/layout.blade.php'));

        $this->assertStringContainsString('/changelog', $layout);
    }
}
