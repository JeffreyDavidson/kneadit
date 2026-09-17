<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\DataTransferObjects\Settings\SettingValue;
use Illuminate\Foundation\Http\FormRequest;

class CheckCapacityRequest extends FormRequest
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
            'date' => ['required', 'date'],
        ];
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function validationData(): array
    {
        return array_merge(SettingValue::map(parent::validationData()), [
            'date' => $this->route('date'),
        ]);
    }
}
