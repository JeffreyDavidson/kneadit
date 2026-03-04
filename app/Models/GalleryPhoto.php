<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryPhoto extends Model
{
    protected $fillable = [
        'title',
        'image_path',
        'category',
        'sort_order',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
