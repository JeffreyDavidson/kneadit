<?php

namespace App\Http\Resources;

use App\Models\Customers\WaitlistEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

/** @mixin WaitlistEntry */
class WaitlistEntryResource extends JsonApiResource
{
    /** @var array<string, class-string> */
    protected array $relationships = [
        'product' => ProductResource::class,
    ];

    public function toId(Request $request): string
    {
        return (string) $this->resource->getKey();
    }

    public function toType(Request $request): string
    {
        return 'waitlist-entries';
    }

    /** @return array<string, mixed> */
    public function toAttributes(Request $request): array
    {
        return [
            'customer_name' => $this->resource->customer_name,
            'customer_email' => $this->resource->customer_email,
            'requested_date' => $this->resource->requested_date?->toDateString(),
            'status' => $this->resource->status->value,
        ];
    }
}
