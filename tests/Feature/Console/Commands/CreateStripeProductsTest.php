<?php

use Laravel\Cashier\Cashier;

// Cashier::stripe() is a static method that creates a real Stripe client.
// Mocking requires a dedicated Stripe test double or HTTP fake.
test('stripe create-products command creates products for each plan')
    ->todo();
