<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Supplier extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name',
        'contact_name',
        'email',
        'phone',
        'website',
        'address',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsToMany<Ingredient, $this, Pivot>
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'ingredient_supplier')
            ->withPivot('unit_price', 'minimum_order', 'lead_time_days', 'sku')
            ->withTimestamps();
    }
}
