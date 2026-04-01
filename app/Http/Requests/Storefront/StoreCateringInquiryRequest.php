<?php

namespace App\Http\Requests\Storefront;

use App\Enums\CateringEventType;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCateringInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:255'],
            'event_type' => ['required', Rule::in(CateringEventType::cases())],
            'event_date' => ['required', 'date', 'after_or_equal:' . now()->addDays((int) app(TenantSettings::class)->cateringLeadTimeDays)->format('Y-m-d')],
            'guest_count' => ['required', 'integer', 'min:' . (int) app(TenantSettings::class)->cateringMinimumGuests],
            'budget' => ['nullable', 'string', 'max:255'],
            'details' => ['required', 'string', 'max:5000'],
            'dietary_requirements' => ['nullable', 'string', 'max:2000'],
            'venue_address' => ['nullable', 'string', 'max:500'],
        ];
    }
}
