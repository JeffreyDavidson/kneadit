<?php

declare(strict_types=1);

namespace App\Models\Marketing;

use App\Enums\Marketing\EmailTemplateType;
use Database\Factories\Marketing\EmailTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property EmailTemplateType $email_type
 * @property string $subject
 * @property string $body
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable('email_type', 'subject', 'body')]
#[UseFactory(EmailTemplateFactory::class)]
class EmailTemplate extends Model
{
    /** @use HasFactory<EmailTemplateFactory> */
    use HasFactory;

    #[\Override]
    protected function casts(): array
    {
        return [
            'email_type' => EmailTemplateType::class,
        ];
    }
}
