<?php

namespace App\Actions\Customers;

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerNote;
use App\Models\Customers\CustomerProfile;
use App\Models\Customers\CustomerReminder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Erases a customer's personal data while keeping the customer row and their
 * orders, which the bakery needs for its accounting. The name, email, phone,
 * address, birthday and notes are replaced, staff notes and reminders about them
 * are deleted (loyalty points and referrals stay for accounting), the customer is
 * unsubscribed from marketing and signed out everywhere, and the records held
 * under their email are removed (see EraseCustomerPersonalRecords). Running it
 * again does nothing.
 */
class AnonymiseCustomer
{
    public function __construct(private readonly EraseCustomerPersonalRecords $erase) {}

    public static function placeholderName(int $customerId): string
    {
        return "Deleted customer #{$customerId}";
    }

    public static function placeholderEmail(int $customerId): string
    {
        return "deleted+{$customerId}@invalid";
    }

    public function isAnonymised(Customer $customer): bool
    {
        return $customer->email === self::placeholderEmail($customer->id);
    }

    public function __invoke(Customer $customer): void
    {
        if ($this->isAnonymised($customer)) {
            return;
        }

        $email = $customer->email;

        DB::transaction(function () use ($customer, $email): void {
            // A password nobody knows replaces the real one, so the sessions
            // that were signed in with the old one end at their next request.
            $customer->forceFill([
                'name' => self::placeholderName($customer->id),
                'email' => self::placeholderEmail($customer->id),
                'password' => Str::random(64),
                'remember_token' => null,
                'email_verified_at' => null,
                'phone' => null,
                'address' => null,
                'city' => null,
                'state' => null,
                'zip' => null,
                'birthday' => null,
                'notes' => null,
                'marketing_opted_out_at' => $customer->marketing_opted_out_at ?? now(),
            ])->save();

            // Staff free text about the person and their reorder reminders go;
            // loyalty points and referrals stay tied to the id for accounting.
            CustomerProfile::query()->where('customer_id', $customer->id)->delete();
            CustomerNote::query()->where('customer_id', $customer->id)->delete();
            CustomerReminder::query()->where('customer_id', $customer->id)->delete();

            ($this->erase)($customer->id, $email);
        });
    }
}
