<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

/** @property array{date: string, available: bool, remaining: int, max: int} $resource */
class CapacityResource extends JsonApiResource
{
    /**
     * @param array{date: string, available: bool, remaining: int, max: int} $resource
     */
    public function __construct(array $resource)
    {
        parent::__construct($resource);
    }

    public function toId(Request $request): string
    {
        return $this->resource['date'];
    }

    public function toType(Request $request): string
    {
        return 'capacity-slots';
    }

    /** @return array<string, mixed> */
    public function toAttributes(Request $request): array
    {
        return [
            'available' => $this->resource['available'],
            'remaining' => $this->resource['remaining'],
            'max' => $this->resource['max'],
        ];
    }
}
