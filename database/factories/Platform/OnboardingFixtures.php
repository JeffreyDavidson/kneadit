<?php

namespace Database\Factories\Platform;

use App\Enums\Orders\PaymentMethod;
use App\Enums\Staff\DayOfWeek;
use App\Models\Inventory\Category;
use App\Models\Platform\Tenant;

/**
 * Realistic mock input for each onboarding wizard step. Used by
 * TenantFactory states (onboarded, partiallyOnboarded) to replay
 * the wizard's per-step save() methods with believable data.
 *
 * One method per step. When a wizard step grows new required input,
 * add it here so the factory state stays in sync with the wizard.
 */
class OnboardingFixtures
{
    /** @return array<string, mixed> */
    public static function welcome(Tenant $tenant): array
    {
        return [
            'owner_name' => $tenant->name,
            'bakery_name' => $tenant->store_name ?: ($tenant->name . "'s Bakery"),
        ];
    }

    /** @return array<string, mixed> */
    public static function branding(): array
    {
        return [
            'color_primary' => fake()->randomElement([
                '#A78BFA', // lavender
                '#92400E', // bread brown
                '#15803D', // forest green
                '#EC4899', // macaron pink
                '#DC2626', // italian red
                '#0891B2', // ocean teal
            ]),
            'color_secondary' => '#1c1410',
            'store_logo' => 'logos/demo-bakery.png',
        ];
    }

    /** @return array<string, mixed> */
    public static function businessHours(): array
    {
        $data = [];
        foreach (DayOfWeek::cases() as $day) {
            $data[$day->value] = true;
            $data["{$day->value}_open"] = $day === DayOfWeek::Saturday ? '06:00' : '07:00';
            $data["{$day->value}_close"] = in_array($day, [DayOfWeek::Friday, DayOfWeek::Saturday]) ? '19:00' : '18:00';
        }

        return $data;
    }

    /** @return array<string, mixed> */
    public static function contact(Tenant $tenant): array
    {
        return [
            'email' => $tenant->email ?: fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress() . ', ' . fake()->city() . ', ' . fake()->stateAbbr() . ' ' . fake()->postcode(),
        ];
    }

    /** @return array<string, mixed> */
    public static function delivery(): array
    {
        return [
            'delivery_enabled' => true,
            'delivery_radius' => '15',
            'delivery_fee' => '5.00',
            'free_delivery_over' => true,
            'free_delivery_threshold' => '50.00',
            'delivery_minimum_order' => '25.00',
            'pickup_enabled' => true,
            'pickup_instructions' => 'Pick up at our front counter during business hours.',
        ];
    }

    /** @return array<string, mixed> */
    public static function compliance(): array
    {
        return [
            'cottage_food_state' => 'FL',
            'revenue_cap' => '50000',
            'license_number' => fake()->bothify('CFL-####-???'),
            'allergy_disclaimer' => 'Please be aware that our bakery uses wheat, eggs, dairy, nuts, and soy. While we take precautions to prevent cross-contamination, we cannot guarantee that any item is completely free from allergens.',
            'acknowledged' => true,
        ];
    }

    /** @return array<string, mixed> */
    public static function payments(): array
    {
        return [
            'payment_methods' => [
                PaymentMethod::Cash->value,
                PaymentMethod::PayPal->value,
            ],
            'paypal_client_id' => 'AYxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
            'paypal_client_secret' => 'EHxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
            'paypal_sandbox' => true,
        ];
    }

    /**
     * Requires a Category to already exist in the tenant DB
     * (CategorySeeder runs as part of db:seed before this is called).
     *
     * @return array<string, mixed>
     */
    public static function product(): array
    {
        $category = Category::query()->first() ?? Category::factory()->create();

        return [
            'name' => 'Signature Chocolate Chip Cookie',
            'description' => 'Our classic chocolate chip cookie, made fresh daily with premium ingredients.',
            'price' => '3.50',
            'category_id' => $category->id,
        ];
    }
}
