<?php

namespace App\Models\Platform;

use App\Models\Staff\User;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string $coupon_id
 * @property string $promotion_code_id
 * @property int|null $percent_off
 * @property int|null $amount_off_cents
 * @property string $duration
 * @property int|null $duration_in_months
 * @property int $max_redemptions
 * @property Carbon|null $expires_at
 * @property string|null $tenant_id
 * @property string|null $name
 * @property int|null $created_by_user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $createdBy
 * @property-read Tenant|null $tenant
 */
#[Table('platform_promo_codes')]
#[Connection('central')]
#[Fillable(
    'code',
    'coupon_id',
    'promotion_code_id',
    'percent_off',
    'amount_off_cents',
    'duration',
    'duration_in_months',
    'max_redemptions',
    'expires_at',
    'tenant_id',
    'name',
    'created_by_user_id',
)]
class PlatformPromoCode extends Model
{
    protected function casts(): array
    {
        return [
            'percent_off' => 'integer',
            'amount_off_cents' => 'integer',
            'duration_in_months' => 'integer',
            'max_redemptions' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
