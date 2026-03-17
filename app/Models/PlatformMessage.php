<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformMessage extends Model
{
    protected $connection = 'central';

    protected $guarded = [];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    #[Scope]
    protected function unread(Builder $query): void
    {
        $query->where('is_read', false);
    }

    #[Scope]
    protected function fromAdmin(Builder $query): void
    {
        $query->where('sender_type', 'admin');
    }

    #[Scope]
    protected function fromTenant(Builder $query): void
    {
        $query->where('sender_type', 'tenant');
    }

    #[Scope]
    protected function topLevel(Builder $query): void
    {
        $query->whereNull('parent_id');
    }
}
