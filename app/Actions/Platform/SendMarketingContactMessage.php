<?php

namespace App\Actions\Platform;

use App\Mail\Platform\ContactFormMail;
use Illuminate\Support\Facades\Mail;

class SendMarketingContactMessage
{
    public function __invoke(string $name, string $email, string $body): void
    {
        Mail::to(config('mail.from.address'))->queue(
            new ContactFormMail(
                senderName: $name,
                senderEmail: $email,
                body: $body,
            ),
        );
    }
}
