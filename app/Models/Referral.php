<?php

namespace App\Models;

use App\Enums\ReferralStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
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
