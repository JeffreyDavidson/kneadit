<?php

namespace App\Models;

use App\Traits\LogsActivity;

use Illuminate\Database\Eloquent\Model;

class EmailCampaign extends Model
{
    use LogsActivity;

    protected $fillable = [
        'subject',
        'body',
        'recipient_count',
        'sent_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }
}
