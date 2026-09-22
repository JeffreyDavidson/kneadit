<?php

$basePath = base_path();
$forgeReleasePath = str_contains($basePath, '/current/') || str_contains($basePath, '/releases/');
$forgeProjectRoot = $forgeReleasePath
    ? preg_replace('#/(current|releases/\d+)$#', '', $basePath)
    : null;

$defaultPath = $forgeProjectRoot !== null
    ? "{$forgeProjectRoot}/backups"
    : storage_path('app/backups');

$backupPath = env('BACKUP_PATH', $defaultPath);

if (! is_string($backupPath) || $backupPath === '') {
    $backupPath = $defaultPath;
}

return [
    'path' => $backupPath,
];
