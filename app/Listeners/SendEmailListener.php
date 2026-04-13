<?php

namespace App\Listeners;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

abstract class SendEmailListener extends QueuedListener
{
    abstract protected function getRecipient(object $event): ?string;

    abstract protected function getMailable(object $event): Mailable;

    /** @return array<string, mixed> */
    abstract protected function getFailureContext(object $event): array;

    final public function handle(object $event): void
    {
        $recipient = $this->getRecipient($event);

        if (! $recipient) {
            return;
        }

        $pending = Mail::to($recipient);

        if ($this->shouldQueueMail()) {
            $pending->queue($this->getMailable($event));
        } else {
            $pending->send($this->getMailable($event));
        }
    }

    final public function failed(object $event, \Throwable $exception): void
    {
        Log::warning(class_basename($this) . ' failed', [
            ...$this->getFailureContext($event),
            'error' => $exception->getMessage(),
        ]);
    }

    protected function shouldQueueMail(): bool
    {
        return false;
    }
}
