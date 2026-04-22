<?php

namespace App\Models\Customers;

use App\Builders\Customers\ReferralQueryBuilder;
use App\Enums\Customers\ReferralStatus;
use App\Models\Platform\Tenant;
use Database\Factories\Customers\ReferralFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $referrer_tenant_id
 * @property string|null $referred_tenant_id
 * @property string $referral_code
 * @property string|null $referred_email
 * @property ReferralStatus $status
 * @property int $reward_months
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Tenant|null $referred
 * @property-read Tenant $referrer
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral query()
 *
 * @mixin \Eloquent
 */
#[Connection('central')]
#[Fillable('referrer_tenant_id', 'referred_tenant_id', 'referral_code', 'referred_email', 'status', 'reward_months')]
#[UseEloquentBuilder(ReferralQueryBuilder::class)]
#[UseFactory(ReferralFactory::class)]
class Referral extends Model
{
    /** @use HasFactory<ReferralFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ReferralStatus::class,
            'reward_months' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'referrer_tenant_id');
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function referred(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'referred_tenant_id');
    }
}
