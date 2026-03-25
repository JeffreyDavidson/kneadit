<?php

namespace App\Models;

use App\Enums\ReferralStatus;
use Database\Factories\ReferralFactory;
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
 * @method static \Database\Factories\ReferralFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral whereReferralCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral whereReferredEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral whereReferredTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral whereReferrerTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral whereRewardMonths($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Referral extends Model
{
    /** @use HasFactory<ReferralFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'referrer_tenant_id',
        'referred_tenant_id',
        'referral_code',
        'referred_email',
        'status',
        'reward_months',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReferralStatus::class,
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
