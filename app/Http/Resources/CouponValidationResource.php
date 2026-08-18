<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class CouponValidationResource extends JsonApiResource
{
    /**
     * @param array{code: string, valid: bool, discount_amount: float, type: ?string, value: ?float} $resource
     */
    public function __construct(array $resource)
    {
        parent::__construct($resource);
    }

    public function toId(Request $request): string
    {
        return $this->resource['code'];
    }

    public function toType(Request $request): string
    {
        return 'coupon-validations';
    }

    /** @return array<string, mixed> */
    public function toAttributes(Request $request): array
    {
        return [
            'valid' => $this->resource['valid'],
            'discount_amount' => $this->resource['discount_amount'],
            'type' => $this->resource['type'],
            'value' => $this->resource['value'],
        ];
    }
}
