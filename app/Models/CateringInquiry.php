<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CateringInquiry extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_phone',
        'event_type',
        'event_date',
        'guest_count',
        'budget',
        'details',
        'dietary_requirements',
        'venue_address',
        'status',
        'quoted_amount',
        'notes',
    ];

    protected $casts = [
        'event_date' => 'date',
        'guest_count' => 'integer',
        'budget' => 'decimal:2',
        'quoted_amount' => 'decimal:2',
    ];

    protected function getEventTypeLabelAttribute(): string
    {
        return match ($this->event_type) {
            'wedding' => '💒 Wedding',
            'corporate' => '🏢 Corporate',
            'birthday' => '🎂 Birthday',
            'holiday' => '🎄 Holiday',
            default => '🎉 Other',
        };
    }

    protected function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'inquiry' => 'New Inquiry',
            'quoted' => 'Quote Sent',
            'confirmed' => 'Confirmed',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }
}
