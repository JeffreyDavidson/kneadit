<?php

namespace App\Actions\Marketing;

use App\Enums\Marketing\EmailTemplateType;
use App\Models\Marketing\EmailTemplate;

class ResetEmailTemplate
{
    public function __invoke(EmailTemplateType $type): void
    {
        EmailTemplate::query()->where('email_type', $type)->delete();
    }
}
