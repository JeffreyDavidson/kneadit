<?php

namespace App\Pipes\Orders;

use App\Models\Customer;
use Closure;

class ResolveCustomer
{
    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        $payload->customer = Customer::query()->updateOrCreate(
            ['email' => $payload->data->customerEmail],
            array_filter([
                'name' => $payload->data->customerName,
                'phone' => $payload->data->customerPhone,
                'birthday' => $payload->data->customerBirthday,
            ], fn (mixed $v) => $v !== null),
        );

        return $next($payload);
    }
}
