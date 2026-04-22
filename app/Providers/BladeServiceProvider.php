<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Blade::directive('money', fn (string $expression) => "<?php \$__money = {$expression}; echo \$__money instanceof \\App\\ValueObjects\\Money ? \$__money->formatted() : '\$' . number_format((float) \$__money, 2); ?>");

        Blade::directive('number', fn (string $expression) => "<?php \$__numberArgs = [{$expression}]; echo \\Illuminate\\Support\\Number::format((float) \$__numberArgs[0], (int) (\$__numberArgs[1] ?? 0)); ?>");

        Blade::directive('time', fn (string $expression) => "<?php echo \\Carbon\\Carbon::createFromFormat('H:i', {$expression})->format('g:i A'); ?>");
    }
}
