<?php

namespace App\Services;

use App\Models\GiftCard;
use Illuminate\Support\Str;

class GiftCardService
{
    public function checkBalance(string $code): ?GiftCard
    {
        return GiftCard::query()->where('code', Str::upper(Str::replace('-', '', $code)))->first()
            ?? GiftCard::query()->where('code', Str::upper(trim($code)))->first();
    }

    public function generateCode(): string
    {
        $maxAttempts = 10;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $raw = Str::upper(Str::random(16));
            $raw = (string) preg_replace('/[^A-Z0-9]/', '', $raw . Str::random(4));
            $raw = substr($raw, 0, 16);
            $code = implode('-', str_split($raw, 4));

            if (! GiftCard::query()->where('code', $code)->exists()) {
                return $code;
            }
        }

        throw new \RuntimeException('Unable to generate unique gift card code after ' . $maxAttempts . ' attempts.');
    }
}
