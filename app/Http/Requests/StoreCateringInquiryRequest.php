<?php

namespace App\Http\Requests;

use App\Enums\CateringEventType;
use App\Models\Setting;
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
            'event_date' => ['required', 'date', 'after_or_equal:'.now()->addDays((int) Setting::get('catering_lead_time_days', '14'))->format('Y-m-d')],
            'guest_count' => ['required', 'integer', 'min:'.(int) Setting::get('catering_minimum_guests', '10')],
            'budget' => ['nullable', 'string', 'max:255'],
            'details' => ['required', 'string'],
            'dietary_requirements' => ['nullable', 'string'],
            'venue_address' => ['nullable', 'string'],
        ];
    }
}
