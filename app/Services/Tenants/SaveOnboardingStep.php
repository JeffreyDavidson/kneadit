<?php

namespace App\Services\Tenants;

use App\Models\Inventory\Product;
use Illuminate\Support\Str;

class SaveOnboardingStep
{
    public function welcome(string $bakeryName, string $ownerName): void
    {
        settings(['store_name' => $bakeryName]);

        $tenant = tenant();
        if ($tenant) {
            $tenant->name = $ownerName;
            $tenant->store_name = $bakeryName;
            $tenant->save();
        }
    }

    public function contact(string $email, ?string $phone, ?string $address): void
    {
        settings(['store_email' => $email]);
        settings(['store_phone' => $phone]);
        settings(['store_address' => $address]);
    }

    public function branding(string $primaryColor, string $secondaryColor, ?string $logoPath): void
    {
        settings(['brand_color_primary' => $primaryColor]);
        settings(['brand_color_secondary' => $secondaryColor]);

        $tenant = tenant();
        if ($tenant) {
            $tenant->brand_color_primary = $primaryColor;
            $tenant->brand_color_secondary = $secondaryColor;

            if ($logoPath) {
                settings(['store_logo' => $logoPath]);
                $tenant->store_logo = $logoPath;
            }

            $tenant->save();
        }
    }

    public function product(string $name, ?string $description, string $price, string $categoryId): void
    {
        $slug = Str::slug($name);

        Product::query()->updateOrCreate(['slug' => $slug], [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'category_id' => $categoryId,
            'is_active' => true,
        ]);
    }

    /**
     * @param array<string, array{open: string, close: string}> $hours
     */
    public function businessHours(array $hours): void
    {
        settings(['operating_hours' => json_encode($hours)]);
    }

    public function compliance(string $state, string $revenueCap, ?string $licenseNumber, string $allergyDisclaimer, bool $acknowledged): void
    {
        settings(['cottage_food_state' => $state]);
        settings(['revenue_cap' => $revenueCap]);
        settings(['license_number' => $licenseNumber]);
        settings(['allergy_disclaimer' => $allergyDisclaimer]);
        settings(['compliance_acknowledged' => $acknowledged ? '1' : '0']);
    }

    public function delivery(bool $deliveryEnabled, ?string $radius, ?string $fee, ?string $freeThreshold, ?string $minimumOrder, bool $pickupEnabled, ?string $pickupInstructions): void
    {
        settings(['delivery_enabled' => $deliveryEnabled ? '1' : '0']);
        settings(['delivery_radius' => $radius]);
        settings(['delivery_fee' => $fee]);
        settings(['free_delivery_threshold' => $freeThreshold]);
        settings(['delivery_minimum_order' => $minimumOrder]);
        settings(['pickup_enabled' => $pickupEnabled ? '1' : '0']);
        settings(['pickup_instructions' => $pickupInstructions]);
    }

    /**
     * @param array<int, string> $methods
     */
    public function payments(array $methods, ?string $paypalClientId, ?string $paypalClientSecret, bool $paypalSandbox): void
    {
        settings(['payment_methods' => json_encode($methods)]);
        settings(['payment_method' => $methods[0] ?? 'cash']);

        if (in_array('paypal', $methods)) {
            settings(['paypal_client_id' => $paypalClientId]);
            settings(['paypal_client_secret' => $paypalClientSecret]);
            settings(['paypal_sandbox' => $paypalSandbox ? '1' : '0']);

            $tenant = tenant();
            if ($tenant) {
                if ($paypalClientId) {
                    $tenant->paypal_client_id = $paypalClientId;
                }
                if ($paypalClientSecret) {
                    $tenant->paypal_client_secret = $paypalClientSecret;
                }
                $tenant->save();
            }
        }
    }

    public function complete(): void
    {
        settings(['onboarding_completed_at' => now()->toISOString()]);
    }
}
