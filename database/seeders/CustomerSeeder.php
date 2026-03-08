<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Maria Rodriguez',
                'email' => 'maria.rodriguez@gmail.com',
                'phone' => '(863) 555-0123',
                'address' => '1245 Magnolia Lane',
                'city' => 'Davenport',
                'state' => 'FL',
                'zip' => '33837',
            ],
            [
                'name' => 'James Thompson',
                'email' => 'jthompson@hotmail.com',
                'phone' => '(407) 555-0987',
                'address' => '567 Oak Street',
                'city' => 'Kissimmee',
                'state' => 'FL',
                'zip' => '34741',
            ],
            [
                'name' => 'Jennifer Williams',
                'email' => 'jwilliams2024@yahoo.com',
                'phone' => '(863) 555-0456',
                'address' => '890 Pine Avenue',
                'city' => 'Winter Haven',
                'state' => 'FL',
                'zip' => '33880',
            ],
            [
                'name' => 'Robert Johnson',
                'email' => 'rob.johnson@outlook.com',
                'phone' => '(352) 555-0234',
                'address' => '234 Sunset Drive',
                'city' => 'Clermont',
                'state' => 'FL',
                'zip' => '34711',
            ],
            [
                'name' => 'Sarah Davis',
                'email' => 'sdavis.fl@gmail.com',
                'phone' => '(407) 555-0567',
                'address' => '789 Lily Pad Lane',
                'city' => 'Kissimmee',
                'state' => 'FL',
                'zip' => '34746',
            ],
            [
                'name' => 'Michael Chen',
                'email' => 'mchen@email.com',
                'phone' => '(863) 555-0789',
                'address' => '456 Orange Blossom Trail',
                'city' => 'Davenport',
                'state' => 'FL',
                'zip' => '33896',
            ],
            [
                'name' => 'Lisa Martinez',
                'email' => 'lisa.martinez.fl@yahoo.com',
                'phone' => '(352) 555-0345',
                'address' => '321 Cypress Court',
                'city' => 'Clermont',
                'state' => 'FL',
                'zip' => '34714',
            ],
            [
                'name' => 'David Anderson',
                'email' => 'danderson@gmail.com',
                'phone' => '(863) 555-0678',
                'address' => '654 Palmetto Street',
                'city' => 'Winter Haven',
                'state' => 'FL',
                'zip' => '33884',
            ],
            [
                'name' => 'Amanda Thompson',
                'email' => 'athompson@hotmail.com',
                'phone' => '(407) 555-0891',
                'address' => '987 Hibiscus Way',
                'city' => 'Kissimmee',
                'state' => 'FL',
                'zip' => '34743',
            ],
            [
                'name' => 'Christopher Garcia',
                'email' => 'cgarcia2024@outlook.com',
                'phone' => '(863) 555-0123',
                'address' => '147 Azalea Circle',
                'city' => 'Davenport',
                'state' => 'FL',
                'zip' => '33837',
            ],
            [
                'name' => 'Rachel Brown',
                'email' => 'rachel.brown@gmail.com',
                'phone' => '(352) 555-0456',
                'address' => '258 Gardenia Road',
                'city' => 'Clermont',
                'state' => 'FL',
                'zip' => '34715',
            ],
            [
                'name' => 'Steven Miller',
                'email' => 'smiller@email.com',
                'phone' => '(863) 555-0789',
                'address' => '369 Jasmine Lane',
                'city' => 'Winter Haven',
                'state' => 'FL',
                'zip' => '33881',
            ],
            [
                'name' => 'Nicole Wilson',
                'email' => 'nwilson.florida@yahoo.com',
                'phone' => '(407) 555-0234',
                'address' => '741 Rose Avenue',
                'city' => 'Kissimmee',
                'state' => 'FL',
                'zip' => '34747',
            ],
            [
                'name' => 'Kevin Taylor',
                'email' => 'ktaylor@gmail.com',
                'phone' => '(863) 555-0567',
                'address' => '852 Violet Street',
                'city' => 'Davenport',
                'state' => 'FL',
                'zip' => '33896',
            ],
            [
                'name' => 'Ashley Moore',
                'email' => 'amoore@hotmail.com',
                'phone' => '(352) 555-0890',
                'address' => '963 Daffodil Drive',
                'city' => 'Clermont',
                'state' => 'FL',
                'zip' => '34712',
            ],
            [
                'name' => 'Daniel Jackson',
                'email' => 'djackson.fl@outlook.com',
                'phone' => '(863) 555-0345',
                'address' => '159 Tulip Boulevard',
                'city' => 'Winter Haven',
                'state' => 'FL',
                'zip' => '33883',
            ],
            [
                'name' => 'Emily White',
                'email' => 'ewhite@email.com',
                'phone' => '(407) 555-0678',
                'address' => '357 Orchid Terrace',
                'city' => 'Kissimmee',
                'state' => 'FL',
                'zip' => '34744',
            ],
            [
                'name' => 'Ryan Harris',
                'email' => 'rharris2024@gmail.com',
                'phone' => '(863) 555-0912',
                'address' => '468 Sunflower Street',
                'city' => 'Davenport',
                'state' => 'FL',
                'zip' => '33837',
            ],
            [
                'name' => 'Michelle Clark',
                'email' => 'mclark@yahoo.com',
                'phone' => '(352) 555-0135',
                'address' => '579 Begonia Court',
                'city' => 'Clermont',
                'state' => 'FL',
                'zip' => '34713',
            ],
            [
                'name' => 'Brandon Lewis',
                'email' => 'blewis.florida@hotmail.com',
                'phone' => '(863) 555-0246',
                'address' => '680 Camellia Way',
                'city' => 'Winter Haven',
                'state' => 'FL',
                'zip' => '33882',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(
                ['email' => $customer['email']],
                $customer
            );
        }
    }
}