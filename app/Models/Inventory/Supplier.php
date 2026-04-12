<?php

namespace App\Models\Inventory;

use App\Casts\PhoneNumberCast;
use App\Models\Concerns\LogsActivity;
use Database\Factories\Inventory\SupplierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property-read Collection<int, Ingredient> $ingredients
 * @property-read int|null $ingredients_count
 *
 * @method static \Database\Factories\SupplierFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier query()
 *
 * @property-read Pivot|null $pivot
 *
 * @mixin \Eloquent
 */
#[Fillable('name', 'contact_name', 'email', 'phone', 'website', 'address', 'notes', 'is_active')]
class Supplier extends Model
{
    /** @use HasFactory<SupplierFactory> */
    use HasFactory;

    use LogsActivity;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'phone' => PhoneNumberCast::class,
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

    protected static function newFactory(): SupplierFactory
    {
        return SupplierFactory::new();
    }
}
