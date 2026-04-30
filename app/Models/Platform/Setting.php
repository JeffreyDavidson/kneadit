<?php

namespace App\Models\Platform;

use App\Observers\LogsActivityObserver;
use App\Policies\Operations\SettingPolicy;
use Database\Factories\Platform\SettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting query()
 *
 * @mixin \Eloquent
 */
#[Fillable('key', 'value')]
#[ObservedBy(LogsActivityObserver::class)]
#[UseFactory(SettingFactory::class)]
#[UsePolicy(SettingPolicy::class)]
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;
}
