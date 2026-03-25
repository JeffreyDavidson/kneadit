<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all()->keyBy('name');

        $products = [
            // Artisan Breads
            [
                'name' => 'Sourdough Boule',
                'category' => 'Artisan Breads',
                'description' => 'Traditional sourdough bread with a crispy crust and tangy, chewy interior. Made with our 7-day starter.',
                'price' => 8.50,
                'cost' => 5.10,
                'is_featured' => true,
            ],
            [
                'name' => 'Rustic Country Loaf',
                'category' => 'Artisan Breads',
                'description' => 'A hearty whole grain bread with seeds and nuts, perfect for sandwiches or toast.',
                'price' => 7.25,
                'cost' => 4.35,
                'is_featured' => false,
            ],
            [
                'name' => 'French Baguette',
                'category' => 'Artisan Breads',
                'description' => 'Classic French baguette with a golden crust and airy, light interior.',
                'price' => 4.50,
                'cost' => 2.70,
                'is_featured' => false,
            ],
            [
                'name' => 'Honey Wheat Sandwich Loaf',
                'category' => 'Artisan Breads',
                'description' => 'Soft, slightly sweet wheat bread enriched with local honey.',
                'price' => 6.00,
                'cost' => null,
                'is_featured' => false,
            ],

            // Gourmet Cookies
            [
                'name' => 'Brown Butter Chocolate Chip',
                'category' => 'Gourmet Cookies',
                'description' => 'Our signature cookies with nutty brown butter and premium dark chocolate chips.',
                'price' => 3.25,
                'cost' => 1.95,
                'is_featured' => true,
            ],
            [
                'name' => 'Lavender Shortbread',
                'category' => 'Gourmet Cookies',
                'description' => 'Delicate buttery shortbread infused with culinary lavender and lemon zest.',
                'price' => 3.75,
                'cost' => 2.25,
                'is_featured' => false,
            ],
            [
                'name' => 'Oatmeal Cranberry Walnut',
                'category' => 'Gourmet Cookies',
                'description' => 'Chewy oatmeal cookies loaded with dried cranberries and toasted walnuts.',
                'price' => 3.00,
                'cost' => null,
                'is_featured' => false,
            ],
            [
                'name' => 'Double Fudge Brownie Cookies',
                'category' => 'Gourmet Cookies',
                'description' => 'Rich, fudgy cookies that combine the best of brownies and cookies.',
                'price' => 3.50,
                'cost' => 2.10,
                'is_featured' => false,
            ],

            // Celebration Cakes
            [
                'name' => 'Chocolate Ganache Torte',
                'category' => 'Celebration Cakes',
                'description' => 'Decadent chocolate cake layered with silky ganache and fresh berries.',
                'price' => 45.00,
                'cost' => 27.00,
                'is_featured' => true,
            ],
            [
                'name' => 'Classic Vanilla Birthday Cake',
                'category' => 'Celebration Cakes',
                'description' => 'Three-layer vanilla cake with buttercream frosting. Custom decorating available.',
                'price' => 38.00,
                'cost' => 22.80,
                'is_featured' => true,
            ],
            [
                'name' => 'Red Velvet Layer Cake',
                'category' => 'Celebration Cakes',
                'description' => 'Southern classic with cream cheese frosting and a hint of cocoa.',
                'price' => 42.00,
                'cost' => null,
                'is_featured' => false,
            ],
            [
                'name' => 'Lemon Blueberry Celebration Cake',
                'category' => 'Celebration Cakes',
                'description' => 'Light lemon cake with fresh blueberries and lemon cream cheese frosting.',
                'price' => 40.00,
                'cost' => 24.00,
                'is_featured' => false,
            ],

            // Artisan Cupcakes
            [
                'name' => 'Red Velvet Cupcakes',
                'category' => 'Artisan Cupcakes',
                'description' => 'Moist red velvet cupcakes topped with signature cream cheese frosting.',
                'price' => 4.25,
                'cost' => 2.55,
                'is_featured' => true,
            ],
            [
                'name' => 'Salted Caramel Chocolate Cupcakes',
                'category' => 'Artisan Cupcakes',
                'description' => 'Rich chocolate cupcakes with salted caramel filling and buttercream.',
                'price' => 4.75,
                'cost' => 2.85,
                'is_featured' => false,
            ],
            [
                'name' => 'Vanilla Bean Cupcakes',
                'category' => 'Artisan Cupcakes',
                'description' => 'Classic vanilla cupcakes made with real vanilla beans and fluffy frosting.',
                'price' => 3.75,
                'cost' => null,
                'is_featured' => false,
            ],
            [
                'name' => 'Strawberry Lemonade Cupcakes',
                'category' => 'Artisan Cupcakes',
                'description' => 'Bright lemon cupcakes with strawberry filling and pink lemonade frosting.',
                'price' => 4.50,
                'cost' => 2.70,
                'is_featured' => false,
            ],

            // Classic Pies
            [
                'name' => 'Apple Cinnamon Pie',
                'category' => 'Classic Pies',
                'description' => 'Traditional apple pie with Granny Smith apples, cinnamon, and flaky pastry.',
                'price' => 18.00,
                'cost' => 10.80,
                'is_featured' => true,
            ],
            [
                'name' => 'Key Lime Pie',
                'category' => 'Classic Pies',
                'description' => 'Tart and creamy Key lime filling in a graham cracker crust.',
                'price' => 16.50,
                'cost' => 9.90,
                'is_featured' => false,
            ],
            [
                'name' => 'Bourbon Pecan Pie',
                'category' => 'Classic Pies',
                'description' => 'Rich pecan pie enhanced with a hint of Kentucky bourbon.',
                'price' => 20.00,
                'cost' => 12.00,
                'is_featured' => false,
            ],
            [
                'name' => 'Fresh Berry Galette',
                'category' => 'Classic Pies',
                'description' => 'Rustic free-form pie with seasonal berries and a buttery crust.',
                'price' => 15.00,
                'cost' => null,
                'is_featured' => false,
            ],

            // French Pastries
            [
                'name' => 'Classic Croissants',
                'category' => 'French Pastries',
                'description' => 'Buttery, flaky croissants made with traditional laminated dough technique.',
                'price' => 3.75,
                'cost' => 2.25,
                'is_featured' => true,
            ],
            [
                'name' => 'Pain au Chocolat',
                'category' => 'French Pastries',
                'description' => 'Croissant dough wrapped around premium dark chocolate batons.',
                'price' => 4.25,
                'cost' => 2.55,
                'is_featured' => false,
            ],
            [
                'name' => 'Fruit Tarts',
                'category' => 'French Pastries',
                'description' => 'Individual pastry shells filled with pastry cream and fresh seasonal fruit.',
                'price' => 6.50,
                'cost' => 3.90,
                'is_featured' => false,
            ],
            [
                'name' => 'Éclairs',
                'category' => 'French Pastries',
                'description' => 'Choux pastry filled with vanilla pastry cream and topped with chocolate glaze.',
                'price' => 5.00,
                'cost' => null,
                'is_featured' => false,
            ],

            // Fresh Muffins
            [
                'name' => 'Blueberry Lemon Muffins',
                'category' => 'Fresh Muffins',
                'description' => 'Tender muffins bursting with fresh blueberries and bright lemon zest.',
                'price' => 3.50,
                'cost' => 2.10,
                'is_featured' => true,
            ],
            [
                'name' => 'Chocolate Chip Banana Muffins',
                'category' => 'Fresh Muffins',
                'description' => 'Moist banana muffins studded with mini chocolate chips.',
                'price' => 3.25,
                'cost' => 1.95,
                'is_featured' => false,
            ],
            [
                'name' => 'Morning Glory Muffins',
                'category' => 'Fresh Muffins',
                'description' => 'Hearty muffins with carrots, raisins, walnuts, and warm spices.',
                'price' => 3.75,
                'cost' => null,
                'is_featured' => false,
            ],
            [
                'name' => 'Orange Cranberry Muffins',
                'category' => 'Fresh Muffins',
                'description' => 'Zesty orange muffins with tart cranberries and a subtle orange glaze.',
                'price' => 3.50,
                'cost' => 2.10,
                'is_featured' => false,
            ],

            // Seasonal Specials
            [
                'name' => 'Pumpkin Spice Cheesecake',
                'category' => 'Seasonal Specials',
                'description' => 'Creamy pumpkin cheesecake with warm spices and graham crust.',
                'price' => 28.00,
                'cost' => 16.80,
                'is_featured' => true,
            ],
            [
                'name' => 'Gingerbread Cookies',
                'category' => 'Seasonal Specials',
                'description' => 'Traditional gingerbread cookies with royal icing decoration.',
                'price' => 4.00,
                'cost' => 2.40,
                'is_featured' => false,
            ],
            [
                'name' => 'Easter Lamb Cake',
                'category' => 'Seasonal Specials',
                'description' => 'Adorable lamb-shaped vanilla cake covered in coconut "wool".',
                'price' => 25.00,
                'cost' => null,
                'is_featured' => false,
            ],

            // Custom Orders
            [
                'name' => 'Wedding Cake Consultation',
                'category' => 'Custom Orders',
                'description' => 'Personalized wedding cake design and tasting consultation.',
                'price' => 65.00,
                'cost' => 39.00,
                'is_featured' => true,
            ],
            [
                'name' => 'Custom Birthday Cake',
                'category' => 'Custom Orders',
                'description' => 'Fully customized birthday cake with your choice of flavors and decorations.',
                'price' => 55.00,
                'cost' => 33.00,
                'is_featured' => false,
            ],
            [
                'name' => 'Corporate Catering Platter',
                'category' => 'Custom Orders',
                'description' => 'Assorted pastries and baked goods perfect for office meetings and events.',
                'price' => 45.00,
                'cost' => null,
                'is_featured' => false,
            ],
        ];

        foreach ($products as $product) {
            $category = $categories[$product['category']];

            Product::query()->updateOrCreate(['name' => $product['name']], [
                'slug' => Str::slug($product['name']),
                'description' => $product['description'],
                'price' => $product['price'],
                'cost' => $product['cost'],
                'category_id' => $category->id,
                'is_active' => true,
                'is_featured' => $product['is_featured'],
            ]);
        }
    }
}
