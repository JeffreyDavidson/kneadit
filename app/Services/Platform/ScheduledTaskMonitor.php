<?php

namespace App\Services\Platform;

use App\DataTransferObjects\Settings\SettingValue;
use App\Services\Settings\PlatformSettingsManager;
use Throwable;

class ScheduledTaskMonitor
{
    public function __construct(
        private PlatformSettingsManager $settings,
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
        return "scheduled_task_status:{$task}";
    }
}
