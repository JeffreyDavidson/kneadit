<?php

namespace Database\Seeders\Inventory;

use App\Models\Inventory\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Artisan Breads',
                'description' => 'Freshly baked breads made with traditional techniques and the finest ingredients.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Gourmet Cookies',
                'description' => 'Delectable cookies ranging from classic favorites to unique seasonal creations.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Celebration Cakes',
                'description' => 'Beautiful custom cakes perfect for birthdays, anniversaries, and special occasions.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Artisan Cupcakes',
                'description' => 'Individual treats bursting with flavor and topped with creative frostings.',
                'sort_order' => 4,
            ],
            [
                'name' => 'Classic Pies',
                'description' => 'Traditional pies with flaky crusts and seasonal fruit or cream fillings.',
                'sort_order' => 5,
            ],
            [
                'name' => 'French Pastries',
                'description' => 'Elegant pastries inspired by French patisserie traditions.',
                'sort_order' => 6,
            ],
            [
                'name' => 'Fresh Muffins',
                'description' => 'Moist and fluffy muffins with wholesome ingredients and bold flavors.',
                'sort_order' => 7,
            ],
            [
                'name' => 'Seasonal Specials',
                'description' => 'Limited-time offerings featuring seasonal ingredients and holiday themes.',
                'sort_order' => 8,
            ],
            [
                'name' => 'Custom Orders',
                'description' => 'Personalized baked goods created to your exact specifications.',
                'sort_order' => 9,
            ],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(['name' => $category['name']], [
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'is_active' => true,
                'sort_order' => $category['sort_order'],
            ]);
        }
    }
}
