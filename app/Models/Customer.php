<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'notes',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function customerNotes(): HasMany
    {
        return $this->hasMany(CustomerNote::class);
    }

    public function customerProfile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class);
    }

    public function getFullAddressAttribute(): string
    {
        $address = '';
        if ($this->address) $address .= $this->address;
        if ($this->city) $address .= ($address ? ', ' : '') . $this->city;
        if ($this->state) $address .= ($address ? ', ' : '') . $this->state;
        if ($this->zip) $address .= ($address ? ' ' : '') . $this->zip;
        return $address;
    }
}
