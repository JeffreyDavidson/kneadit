<?php

declare(strict_types=1);

namespace App\Services\Platform;

use App\DataTransferObjects\Settings\SettingValue;
use App\Models\Platform\PlatformSetting;
use App\Services\Settings\PlatformSettingsManager;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;
use Throwable;

class ScheduledTaskMonitor
{
    private const string KEY_PREFIX = 'scheduled_task_status:';

    public function __construct(
        private readonly PlatformSettingsManager $settings,
    ) {}

    public function started(string $task): void
    {
        $this->store($task, [
            'status' => 'running',
            'started_at' => now()->toIso8601String(),
            'finished_at' => null,
            'runtime_seconds' => null,
            'exit_code' => null,
            'error' => null,
        ]);
    }

    public function succeeded(string $task, float $runtimeSeconds, ?int $exitCode = 0): void
    {
        $this->store($task, [
            ...$this->status($task),
            'status' => $exitCode === 0 ? 'succeeded' : 'failed',
            'finished_at' => now()->toIso8601String(),
            'runtime_seconds' => round($runtimeSeconds, 3),
            'exit_code' => $exitCode,
            'error' => null,
        ]);
    }

    public function failed(string $task, Throwable|string $error, ?float $runtimeSeconds = null): void
    {
        $this->store($task, [
            ...$this->status($task),
            'status' => 'failed',
            'finished_at' => now()->toIso8601String(),
            'runtime_seconds' => $runtimeSeconds === null ? null : round($runtimeSeconds, 3),
            'exit_code' => null,
            'error' => mb_substr($error instanceof Throwable ? $error->getMessage() : $error, 0, 500),
        ]);
    }

    /** When any scheduled task last started, or null when none ever has. */
    public function lastStartedAt(): ?CarbonInterface
    {
        $latest = null;

        foreach (PlatformSetting::query()->where('key', 'like', self::KEY_PREFIX.'%')->pluck('value') as $value) {
            $startedAt = is_string($value) ? (SettingValue::map(json_decode($value, true))['started_at'] ?? null) : null;

            if (! is_string($startedAt)) {
                continue;
            }

            $startedAt = Date::parse($startedAt);

            if ($latest === null || $startedAt->gt($latest)) {
                $latest = $startedAt;
            }
        }

        return $latest;
    }

    /** Time since the task last started, or 0 when no start was recorded. */
    public function secondsSinceStart(string $task): float
    {
        $startedAt = $this->status($task)['started_at'] ?? null;

        if (! is_string($startedAt)) {
            return 0.0;
        }

        return max(0.0, Date::parse($startedAt)->diffInSeconds(now()));
    }

    /** @return array<string, mixed> */
    public function status(string $task): array
    {
        $value = $this->settings->get($this->key($task));

        if (! is_string($value)) {
            return [];
        }

        $status = json_decode($value, true);

        return SettingValue::map($status);
    }

    /** @param array<string, mixed> $status */
    private function store(string $task, array $status): void
    {
        $this->settings->set($this->key($task), json_encode($status, JSON_THROW_ON_ERROR));
    }

    private function key(string $task): string
    {
        return self::KEY_PREFIX.$task;
    }
}
