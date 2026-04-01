<?php

namespace Database\Seeders;

use App\Enums\WaitlistStatus;
use App\Models\Product;
use App\Models\WaitlistEntry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;

class WaitlistEntrySeeder extends Seeder
{
    public function run(): void
    {
        if (WaitlistEntry::query()->count() > 0) {
            return;
        }

        $products = Product::query()->whereIn('name', [
            'Chocolate Ganache Torte',
            'Classic Vanilla Birthday Cake',
            'Wedding Cake Consultation',
            'Custom Birthday Cake',
            'Pumpkin Spice Cheesecake',
        ])->get();

        $waitlistEntries = [
            [
                'customer_name' => 'Jennifer Adams',
                'customer_email' => 'jadams@email.com',
                'customer_phone' => '(407) 555-1234',
                'product_name' => 'Chocolate Ganache Torte',
                'requested_date' => Date::now()->addDays(3),
                'notes' => 'Need for anniversary dinner party - serves 8 people',
                'status' => WaitlistStatus::Waiting,
            ],
            [
                'customer_name' => 'Mark Thompson',
                'customer_email' => 'mthompson@yahoo.com',
                'customer_phone' => '(863) 555-5678',
                'product_name' => 'Classic Vanilla Birthday Cake',
                'requested_date' => Date::now()->addDays(5),
                'notes' => 'Son\'s 10th birthday - would like blue and green decorations',
                'status' => WaitlistStatus::Notified,
            ],
            [
                'customer_name' => 'Sarah Wilson',
                'customer_email' => 'swilson@outlook.com',
                'customer_phone' => '(352) 555-9012',
                'product_name' => 'Wedding Cake Consultation',
                'requested_date' => Date::now()->addDays(7),
                'notes' => 'Planning June wedding, 150 guests. Prefer vanilla with fresh flowers.',
                'status' => WaitlistStatus::Converted,
            ],
            [
                'customer_name' => 'David Garcia',
                'customer_email' => 'dgarcia.fl@gmail.com',
                'customer_phone' => '(407) 555-3456',
                'product_name' => 'Custom Birthday Cake',
                'requested_date' => Date::now()->addDays(10),
                'notes' => 'Wife\'s 40th birthday - chocolate cake with raspberry filling',
                'status' => WaitlistStatus::Waiting,
            ],
            [
                'customer_name' => 'Lisa Rodriguez',
                'customer_email' => 'lrodriguez@hotmail.com',
                'customer_phone' => '(863) 555-7890',
                'product_name' => 'Pumpkin Spice Cheesecake',
                'requested_date' => Date::now()->addDays(14),
                'notes' => 'Fall dinner party dessert - need it to serve 12',
                'status' => WaitlistStatus::Removed,
            ],
        ];

        foreach ($waitlistEntries as $entryData) {
            $product = $products->where('name', $entryData['product_name'])->first();

            if ($product) {
                WaitlistEntry::query()->create([
                    'customer_name' => $entryData['customer_name'],
                    'customer_email' => $entryData['customer_email'],
                    'customer_phone' => $entryData['customer_phone'],
                    'product_id' => $product->id,
                    'requested_date' => $entryData['requested_date'],
                    'notes' => $entryData['notes'],
                    'status' => $entryData['status'],
                ]);
            }
        }
    }
}
