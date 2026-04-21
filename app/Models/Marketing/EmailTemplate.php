<?php

namespace App\Models\Marketing;

use App\Enums\Marketing\EmailTemplateType;
use Database\Factories\Marketing\EmailTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property EmailTemplateType $email_type
 * @property string $subject
 * @property string $body
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
#[Fillable('email_type', 'subject', 'body')]
#[UseFactory(EmailTemplateFactory::class)]
class EmailTemplate extends Model
{
    /** @use HasFactory<EmailTemplateFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'email_type' => EmailTemplateType::class,
        ];
    }
}
