<?php

namespace App\Services\Security;

use App\DataTransferObjects\Settings\SettingValue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

final class CspReportContextBuilder
{
    /**
     * @return array<string, bool|int|string|null>
     */
    public function build(mixed $report): array
    {
        if (! is_array($report)) {
            return [
                'malformed' => true,
            ];
        }

        return collect(SettingValue::map(Arr::only($report, Config::array('csp.report_fields'))))
            ->filter(fn (mixed $value): bool => is_scalar($value) || $value === null)
            ->map(fn (mixed $value): bool|int|string|null => is_string($value)
                ? Str::limit($value, 2_048, '')
                : $this->scalarValue($value))
            ->all();
    }

    private function scalarValue(mixed $value): bool|int|string|null
    {
        if (is_bool($value) || is_int($value) || is_string($value) || $value === null) {
            return $value;
        }

        if (is_float($value)) {
            return (string) $value;
        }

        return null;
    }
}
