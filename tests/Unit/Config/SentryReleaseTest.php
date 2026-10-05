<?php

use Illuminate\Support\Env;

/** Loads config/sentry.php with the given environment variables set, then puts the environment back. */
function sentryReleaseWith(array $variables): mixed
{
    $environment = Env::getRepository();

    foreach ($variables as $name => $value) {
        $environment->set($name, $value);
    }

    try {
        $sentry = require config_path('sentry.php');

        return $sentry['release'];
    } finally {
        foreach (array_keys($variables) as $name) {
            $environment->clear($name);
        }
    }
}

test('the Sentry release is the deployed commit that Nightwatch reports', function () {
    expect(sentryReleaseWith(['FORGE_DEPLOY_COMMIT' => '5f85657b0c1d']))->toBe('5f85657b0c1d');
});

test('the Sentry release follows the same deployment id as Nightwatch', function (string $variable) {
    expect(sentryReleaseWith([$variable => 'deploy-123']))->toBe('deploy-123');
})->with(['NIGHTWATCH_DEPLOY', 'LARAVEL_CLOUD_DEPLOY_UUID', 'FORGE_DEPLOY_COMMIT', 'VAPOR_COMMIT_HASH']);

test('an explicit Nightwatch deployment id wins over the commit, as it does for Nightwatch', function () {
    expect(sentryReleaseWith(['NIGHTWATCH_DEPLOY' => 'night-1', 'FORGE_DEPLOY_COMMIT' => '5f85657b0c1d']))->toBe('night-1');
});

test('SENTRY_RELEASE overrides the deployed commit', function () {
    expect(sentryReleaseWith(['SENTRY_RELEASE' => 'v1.48.0', 'FORGE_DEPLOY_COMMIT' => '5f85657b0c1d']))->toBe('v1.48.0');
});

test('the Sentry release is empty when nothing identifies the deploy', function () {
    expect(sentryReleaseWith([]))->toBeNull();
});
