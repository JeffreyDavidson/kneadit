<?php

namespace Database\Seeders\Customers;

use App\Models\Engagement\Review;
use App\Models\Inventory\Product;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        if (Review::query()->count() > 0) {
            return;
        }

        $products = Product::all();

        $customerNames = [
            'Sarah Johnson', 'Mike Davis', 'Jennifer Wilson', 'David Brown', 'Lisa Martinez',
            'Chris Anderson', 'Amanda White', 'Ryan Thompson', 'Michelle Garcia', 'Kevin Lee',
            'Nicole Taylor', 'Brandon Clark', 'Rachel Miller', 'Steven Moore', 'Ashley Lewis',
            'Daniel Harris', 'Emily Jackson', 'Tyler Rodriguez', 'Samantha Adams', 'Jordan Smith',
            'Megan Campbell', 'Andrew Wright', 'Stephanie Turner', 'Matthew Phillips', 'Lauren Evans',
        ];

        $customerEmails = [
            'sarah.j@email.com', 'mike.davis@gmail.com', 'jwilson@yahoo.com', 'dbrown2024@hotmail.com', 'lisa.m@outlook.com',
            'c.anderson@email.com', 'amanda.white@gmail.com', 'ryan.t@yahoo.com', 'mgarcia@hotmail.com', 'kevin.lee@outlook.com',
            'nicole.taylor@email.com', 'bclark@gmail.com', 'rachel.miller@yahoo.com', 'steven.m@hotmail.com', 'ashley.l@outlook.com',
            'daniel.harris@email.com', 'emily.jackson@gmail.com', 'tyler.r@yahoo.com', 'samantha.a@hotmail.com', 'jordan.smith@outlook.com',
            'megan.c@email.com', 'andrew.wright@gmail.com', 'stephanie.t@yahoo.com', 'matthew.p@hotmail.com', 'lauren.e@outlook.com',
        ];

        $reviews = [
            [
                'rating' => 5,
                'comment' => 'Absolutely incredible! The sourdough has the perfect tangy flavor and crispy crust. Will definitely be ordering again!',
            ],
            [
                'rating' => 5,
                'comment' => 'Best chocolate chip cookies I\'ve ever had! The brown butter makes such a difference. My family devoured them in minutes.',
            ],
            [
                'rating' => 4,
                'comment' => 'Beautiful cake for my daughter\'s birthday. The decorating was exactly what we requested and it tasted amazing.',
            ],
            [
                'rating' => 5,
                'comment' => 'The red velvet cupcakes are to die for! Moist cake and the cream cheese frosting is perfection.',
            ],
            [
                'rating' => 4,
                'comment' => 'Great apple pie! Reminds me of my grandmother\'s recipe. The crust was perfectly flaky.',
            ],
            [
                'rating' => 5,
                'comment' => 'These croissants are as good as any I\'ve had in France. Buttery, flaky, and absolutely divine!',
            ],
            [
                'rating' => 4,
                'comment' => 'Love the blueberry muffins! Fresh berries and just the right amount of sweetness.',
            ],
            [
                'rating' => 5,
                'comment' => 'The French baguette has an amazing crust and perfect crumb. Best bread in Central Florida!',
            ],
            [
                'rating' => 4,
                'comment' => 'Key lime pie was tart and creamy - exactly how it should be. Great presentation too.',
            ],
            [
                'rating' => 5,
                'comment' => 'Pain au chocolat was incredible! The chocolate was rich and the pastry was perfectly laminated.',
            ],
            [
                'rating' => 4,
                'comment' => 'Unique lavender shortbread - subtle floral notes and buttery texture. Very sophisticated!',
            ],
            [
                'rating' => 5,
                'comment' => 'Vanilla birthday cake was a huge hit at our party. Three perfect layers and beautiful buttercream.',
            ],
            [
                'rating' => 4,
                'comment' => 'Bourbon pecan pie is rich and delicious. The bourbon adds a nice depth of flavor.',
            ],
            [
                'rating' => 5,
                'comment' => 'The fruit tarts are works of art! Beautiful presentation and the pastry cream is silky smooth.',
            ],
            [
                'rating' => 3,
                'comment' => 'Pumpkin cheesecake was good but a bit too sweet for my taste. Nice spice blend though.',
            ],
            [
                'rating' => 5,
                'comment' => 'Amazing quality and service! Every item we\'ve ordered has exceeded expectations.',
            ],
            [
                'rating' => 4,
                'comment' => 'The seasonal specials are always creative and delicious. Love supporting this local business!',
            ],
            [
                'rating' => 5,
                'comment' => 'Ordered for a corporate event and everyone was raving about the pastries. Professional service too!',
            ],
            [
                'rating' => 4,
                'comment' => 'Great variety of options and accommodating for dietary restrictions. Appreciate the personal touch!',
            ],
            [
                'rating' => 5,
                'comment' => 'The attention to detail is incredible. You can taste the quality ingredients in every bite.',
            ],
            [
                'rating' => 4,
                'comment' => 'Delivery was right on time and everything arrived in perfect condition. Thank you!',
            ],
            [
                'rating' => 5,
                'comment' => 'Been a customer for months now and the consistency is outstanding. Never disappoints!',
            ],
            [
                'rating' => 3,
                'comment' => 'Good products but wish there were more gluten-free options available.',
            ],
            [
                'rating' => 4,
                'comment' => 'Love the farm-to-table approach with local ingredients. You can taste the difference!',
            ],
            [
                'rating' => 5,
                'comment' => 'KneadIt has ruined me for store-bought baked goods. Everything is so fresh and flavorful!',
            ],
        ];

        foreach ($reviews as $index => $reviewData) {
            $product = $products->random();
            $name = $customerNames[$index % count($customerNames)];
            $email = $customerEmails[$index % count($customerEmails)];

            // Most reviews are approved, a few pending
            $isApproved = random_int(0, 100) < 85; // 85% approved

            // 2-3 featured reviews (only from 5-star reviews)
            $isFeatured = false;
            if ($reviewData['rating'] == 5 && random_int(0, 100) < 12) { // ~12% chance for 5-star reviews
                $isFeatured = true;
            }

            Review::query()->create([
                'customer_name' => $name,
                'customer_email' => $email,
                'product_id' => $product->id,
                'rating' => $reviewData['rating'],
                'comment' => $reviewData['comment'],
                'is_approved' => $isApproved,
                'is_featured' => $isFeatured,
            ]);
        }
    }
}
