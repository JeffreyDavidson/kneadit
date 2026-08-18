<?php

declare(strict_types=1);

test('inline script and style blocks carry a CSP nonce', function () {
    $viewRoot = dirname(__DIR__, 2) . '/resources/views';
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($viewRoot, FilesystemIterator::SKIP_DOTS),
    );
    $violations = [];

    foreach ($iterator as $file) {
        if (! $file instanceof SplFileInfo || $file->getExtension() !== 'php') {
            continue;
        }

        $contents = file_get_contents($file->getPathname());

        if ($contents === false) {
            throw new RuntimeException("Unable to read {$file->getPathname()}.");
        }

        preg_match_all('/<(script|style)\b[^>]*>/i', $contents, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as [$tag, $offset]) {
            if (str_starts_with(strtolower($tag), '<script') && preg_match('/\bsrc\s*=/i', $tag) === 1) {
                continue;
            }

            if (str_contains($tag, '@cspnonce')) {
                continue;
            }

            $relative = str_replace($viewRoot . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $line = substr_count(substr($contents, 0, $offset), "\n") + 1;
            $violations[] = "{$relative}:{$line}";
        }
    }

    expect($violations)->toBeEmpty(
        "Inline script and style blocks require @cspnonce:\n" . implode("\n", $violations),
    );
});
