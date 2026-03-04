<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        // Get specific products to create recipes for
        $products = Product::whereIn('name', [
            'Sourdough Boule',
            'Brown Butter Chocolate Chip',
            'Chocolate Ganache Torte',
            'Red Velvet Cupcakes',
            'Apple Cinnamon Pie',
            'Classic Croissants',
            'Blueberry Lemon Muffins',
            'French Baguette',
            'Key Lime Pie',
            'Pain au Chocolat',
            'Lavender Shortbread',
            'Classic Vanilla Birthday Cake',
            'Bourbon Pecan Pie',
            'Fruit Tarts',
            'Pumpkin Spice Cheesecake'
        ])->get();

        $recipes = [
            [
                'product_name' => 'Sourdough Boule',
                'name' => 'Traditional Sourdough Boule',
                'ingredients' => [
                    ['name' => 'Sourdough starter (active)', 'quantity' => '200', 'unit' => 'g', 'cost' => '0.50'],
                    ['name' => 'Bread flour', 'quantity' => '500', 'unit' => 'g', 'cost' => '1.25'],
                    ['name' => 'Water (filtered)', 'quantity' => '350', 'unit' => 'ml', 'cost' => '0.05'],
                    ['name' => 'Sea salt (fine)', 'quantity' => '10', 'unit' => 'g', 'cost' => '0.10'],
                    ['name' => 'Olive oil', 'quantity' => '15', 'unit' => 'ml', 'cost' => '0.15'],
                ],
                'instructions' => 'Mix starter with water until dissolved. Add flour and salt, mix until shaggy dough forms. Perform 4 sets of stretch and folds over 2 hours. Bulk ferment 4-6 hours. Shape into boule, final proof 2-3 hours. Score and bake at 450°F with steam for 20 minutes, then 425°F for 25-30 minutes.',
                'prep_time_minutes' => 480, // 8 hours including fermentation
            ],
            [
                'product_name' => 'Brown Butter Chocolate Chip',
                'name' => 'Brown Butter Chocolate Chip Cookies',
                'ingredients' => [
                    ['name' => 'Butter (unsalted)', 'quantity' => '225', 'unit' => 'g', 'cost' => '1.80'],
                    ['name' => 'Brown sugar', 'quantity' => '150', 'unit' => 'g', 'cost' => '0.45'],
                    ['name' => 'Granulated sugar', 'quantity' => '100', 'unit' => 'g', 'cost' => '0.25'],
                    ['name' => 'Eggs (large)', 'quantity' => '2', 'unit' => 'pieces', 'cost' => '0.50'],
                    ['name' => 'All-purpose flour', 'quantity' => '280', 'unit' => 'g', 'cost' => '0.60'],
                    ['name' => 'Dark chocolate chips', 'quantity' => '200', 'unit' => 'g', 'cost' => '2.50'],
                    ['name' => 'Baking soda', 'quantity' => '5', 'unit' => 'g', 'cost' => '0.02'],
                    ['name' => 'Vanilla extract', 'quantity' => '10', 'unit' => 'ml', 'cost' => '0.25'],
                    ['name' => 'Salt', 'quantity' => '3', 'unit' => 'g', 'cost' => '0.02'],
                ],
                'instructions' => 'Brown butter in a saucepan until nutty and golden. Cool completely. Mix with sugars, then add eggs and vanilla. Combine dry ingredients separately, then fold into wet. Add chocolate chips. Chill dough 30 minutes. Bake at 350°F for 10-12 minutes until golden.',
                'prep_time_minutes' => 75,
            ],
            [
                'product_name' => 'Chocolate Ganache Torte',
                'name' => 'Decadent Chocolate Ganache Torte',
                'ingredients' => [
                    ['name' => 'Dark chocolate (70%)', 'quantity' => '400', 'unit' => 'g', 'cost' => '8.00'],
                    ['name' => 'Heavy cream', 'quantity' => '300', 'unit' => 'ml', 'cost' => '2.50'],
                    ['name' => 'Eggs (large)', 'quantity' => '6', 'unit' => 'pieces', 'cost' => '1.50'],
                    ['name' => 'Caster sugar', 'quantity' => '150', 'unit' => 'g', 'cost' => '0.40'],
                    ['name' => 'Butter (unsalted)', 'quantity' => '100', 'unit' => 'g', 'cost' => '0.80'],
                    ['name' => 'Cocoa powder', 'quantity' => '50', 'unit' => 'g', 'cost' => '0.75'],
                    ['name' => 'Fresh berries', 'quantity' => '200', 'unit' => 'g', 'cost' => '3.50'],
                ],
                'instructions' => 'Melt chocolate with butter. Whisk eggs and sugar until pale. Fold in chocolate mixture and cocoa. Bake in water bath at 325°F for 45 minutes. Make ganache with cream and remaining chocolate. Cool torte, top with ganache and berries.',
                'prep_time_minutes' => 120,
            ],
            [
                'product_name' => 'Red Velvet Cupcakes',
                'name' => 'Classic Red Velvet Cupcakes',
                'ingredients' => [
                    ['name' => 'All-purpose flour', 'quantity' => '250', 'unit' => 'g', 'cost' => '0.55'],
                    ['name' => 'Cocoa powder', 'quantity' => '20', 'unit' => 'g', 'cost' => '0.30'],
                    ['name' => 'Caster sugar', 'quantity' => '200', 'unit' => 'g', 'cost' => '0.50'],
                    ['name' => 'Vegetable oil', 'quantity' => '120', 'unit' => 'ml', 'cost' => '0.40'],
                    ['name' => 'Buttermilk', 'quantity' => '240', 'unit' => 'ml', 'cost' => '1.20'],
                    ['name' => 'Eggs (large)', 'quantity' => '2', 'unit' => 'pieces', 'cost' => '0.50'],
                    ['name' => 'Red food coloring', 'quantity' => '30', 'unit' => 'ml', 'cost' => '0.50'],
                    ['name' => 'Cream cheese', 'quantity' => '200', 'unit' => 'g', 'cost' => '2.50'],
                    ['name' => 'Powdered sugar', 'quantity' => '300', 'unit' => 'g', 'cost' => '0.75'],
                ],
                'instructions' => 'Mix dry ingredients. Combine wet ingredients including food coloring. Fold wet into dry until just combined. Fill cupcake liners 2/3 full. Bake at 350°F for 18-20 minutes. Cool completely. Beat cream cheese frosting and pipe onto cooled cupcakes.',
                'prep_time_minutes' => 60,
            ],
            [
                'product_name' => 'Apple Cinnamon Pie',
                'name' => 'Traditional Apple Cinnamon Pie',
                'ingredients' => [
                    ['name' => 'Granny Smith apples', 'quantity' => '1.5', 'unit' => 'kg', 'cost' => '3.00'],
                    ['name' => 'Plain flour (for pastry)', 'quantity' => '300', 'unit' => 'g', 'cost' => '0.65'],
                    ['name' => 'Butter (cold)', 'quantity' => '200', 'unit' => 'g', 'cost' => '1.60'],
                    ['name' => 'Brown sugar', 'quantity' => '100', 'unit' => 'g', 'cost' => '0.30'],
                    ['name' => 'Ground cinnamon', 'quantity' => '10', 'unit' => 'g', 'cost' => '0.20'],
                    ['name' => 'Lemon juice', 'quantity' => '30', 'unit' => 'ml', 'cost' => '0.15'],
                    ['name' => 'Cornstarch', 'quantity' => '15', 'unit' => 'g', 'cost' => '0.10'],
                    ['name' => 'Egg (for wash)', 'quantity' => '1', 'unit' => 'piece', 'cost' => '0.25'],
                ],
                'instructions' => 'Make pastry with flour, butter, and cold water. Rest 30 minutes. Slice apples, toss with sugar, cinnamon, lemon juice, and cornstarch. Roll out pastry, line pie dish. Fill with apples, top with second pastry layer. Seal edges, brush with egg wash. Bake at 425°F for 15 minutes, then 350°F for 35-40 minutes.',
                'prep_time_minutes' => 90,
            ],
            [
                'product_name' => 'Classic Croissants',
                'name' => 'Buttery French Croissants',
                'ingredients' => [
                    ['name' => 'Bread flour', 'quantity' => '500', 'unit' => 'g', 'cost' => '1.25'],
                    ['name' => 'Butter (European style)', 'quantity' => '350', 'unit' => 'g', 'cost' => '4.20'],
                    ['name' => 'Whole milk', 'quantity' => '160', 'unit' => 'ml', 'cost' => '0.50'],
                    ['name' => 'Active dry yeast', 'quantity' => '7', 'unit' => 'g', 'cost' => '0.15'],
                    ['name' => 'Sugar', 'quantity' => '50', 'unit' => 'g', 'cost' => '0.12'],
                    ['name' => 'Salt', 'quantity' => '10', 'unit' => 'g', 'cost' => '0.03'],
                    ['name' => 'Eggs (for wash)', 'quantity' => '2', 'unit' => 'pieces', 'cost' => '0.50'],
                ],
                'instructions' => 'Make dough with flour, milk, yeast, sugar, and salt. Rest overnight. Pound butter into rectangle. Encase butter in dough, perform 3 letter folds with 30-minute rests between. Roll out, cut triangles, shape croissants. Proof 2 hours. Brush with egg wash, bake at 400°F for 15-18 minutes.',
                'prep_time_minutes' => 960, // 16 hours including overnight rest
            ],
            [
                'product_name' => 'Blueberry Lemon Muffins',
                'name' => 'Fresh Blueberry Lemon Muffins',
                'ingredients' => [
                    ['name' => 'All-purpose flour', 'quantity' => '300', 'unit' => 'g', 'cost' => '0.65'],
                    ['name' => 'Fresh blueberries', 'quantity' => '200', 'unit' => 'g', 'cost' => '3.00'],
                    ['name' => 'Sugar', 'quantity' => '150', 'unit' => 'g', 'cost' => '0.40'],
                    ['name' => 'Vegetable oil', 'quantity' => '80', 'unit' => 'ml', 'cost' => '0.25'],
                    ['name' => 'Eggs (large)', 'quantity' => '2', 'unit' => 'pieces', 'cost' => '0.50'],
                    ['name' => 'Milk', 'quantity' => '180', 'unit' => 'ml', 'cost' => '0.45'],
                    ['name' => 'Lemon zest', 'quantity' => '2', 'unit' => 'lemons', 'cost' => '0.60'],
                    ['name' => 'Baking powder', 'quantity' => '12', 'unit' => 'g', 'cost' => '0.05'],
                    ['name' => 'Salt', 'quantity' => '3', 'unit' => 'g', 'cost' => '0.02'],
                ],
                'instructions' => 'Toss blueberries in flour to prevent sinking. Mix dry ingredients. Combine wet ingredients including lemon zest. Fold wet into dry until just combined, add blueberries gently. Fill muffin cups 2/3 full. Bake at 375°F for 20-25 minutes until golden.',
                'prep_time_minutes' => 45,
            ],
            [
                'product_name' => 'French Baguette',
                'name' => 'Authentic French Baguette',
                'ingredients' => [
                    ['name' => 'Bread flour', 'quantity' => '500', 'unit' => 'g', 'cost' => '1.25'],
                    ['name' => 'Water', 'quantity' => '350', 'unit' => 'ml', 'cost' => '0.05'],
                    ['name' => 'Active dry yeast', 'quantity' => '3', 'unit' => 'g', 'cost' => '0.07'],
                    ['name' => 'Salt', 'quantity' => '10', 'unit' => 'g', 'cost' => '0.03'],
                ],
                'instructions' => 'Mix flour, water, yeast, and salt into shaggy dough. Autolyse 30 minutes. Perform 4 sets of stretch and folds. Bulk ferment 3-4 hours. Divide into portions, shape into baguettes. Final proof 1-2 hours. Score deeply, bake with steam at 450°F for 25-30 minutes.',
                'prep_time_minutes' => 420, // 7 hours including fermentation
            ],
            [
                'product_name' => 'Key Lime Pie',
                'name' => 'Authentic Key Lime Pie',
                'ingredients' => [
                    ['name' => 'Graham crackers', 'quantity' => '200', 'unit' => 'g', 'cost' => '1.50'],
                    ['name' => 'Butter (melted)', 'quantity' => '80', 'unit' => 'g', 'cost' => '0.65'],
                    ['name' => 'Key lime juice', 'quantity' => '120', 'unit' => 'ml', 'cost' => '2.50'],
                    ['name' => 'Condensed milk', 'quantity' => '400', 'unit' => 'ml', 'cost' => '2.00'],
                    ['name' => 'Egg yolks', 'quantity' => '4', 'unit' => 'pieces', 'cost' => '0.50'],
                    ['name' => 'Key lime zest', 'quantity' => '2', 'unit' => 'tbsp', 'cost' => '0.30'],
                    ['name' => 'Heavy cream', 'quantity' => '200', 'unit' => 'ml', 'cost' => '1.50'],
                ],
                'instructions' => 'Crush graham crackers, mix with melted butter, press into pie pan. Bake crust at 350°F for 10 minutes. Whisk egg yolks, add condensed milk, lime juice, and zest. Pour into crust. Bake 15 minutes until set. Cool completely, chill 4 hours. Serve with whipped cream.',
                'prep_time_minutes' => 300, // 5 hours including chilling
            ],
            [
                'product_name' => 'Pain au Chocolat',
                'name' => 'Classic Pain au Chocolat',
                'ingredients' => [
                    ['name' => 'Croissant dough (prepared)', 'quantity' => '500', 'unit' => 'g', 'cost' => '3.00'],
                    ['name' => 'Dark chocolate batons', 'quantity' => '150', 'unit' => 'g', 'cost' => '3.50'],
                    ['name' => 'Eggs (for wash)', 'quantity' => '1', 'unit' => 'piece', 'cost' => '0.25'],
                ],
                'instructions' => 'Roll out croissant dough into rectangles. Place chocolate batons at one end, roll up tightly, seal seam. Place seam-side down on lined baking sheet. Proof 2 hours until doubled. Brush with egg wash. Bake at 400°F for 15-18 minutes until golden brown.',
                'prep_time_minutes' => 150,
            ],
            [
                'product_name' => 'Lavender Shortbread',
                'name' => 'Delicate Lavender Shortbread',
                'ingredients' => [
                    ['name' => 'Butter (unsalted)', 'quantity' => '250', 'unit' => 'g', 'cost' => '2.00'],
                    ['name' => 'Powdered sugar', 'quantity' => '80', 'unit' => 'g', 'cost' => '0.20'],
                    ['name' => 'All-purpose flour', 'quantity' => '300', 'unit' => 'g', 'cost' => '0.65'],
                    ['name' => 'Culinary lavender (dried)', 'quantity' => '5', 'unit' => 'g', 'cost' => '1.50'],
                    ['name' => 'Lemon zest', 'quantity' => '1', 'unit' => 'lemon', 'cost' => '0.30'],
                    ['name' => 'Salt', 'quantity' => '2', 'unit' => 'g', 'cost' => '0.01'],
                ],
                'instructions' => 'Cream butter and powdered sugar. Grind lavender finely, add to mixture with lemon zest. Mix in flour and salt until dough forms. Shape into log, wrap in plastic, chill 2 hours. Slice into rounds, bake at 325°F for 18-20 minutes until lightly golden.',
                'prep_time_minutes' => 180,
            ],
            [
                'product_name' => 'Classic Vanilla Birthday Cake',
                'name' => 'Three-Layer Vanilla Birthday Cake',
                'ingredients' => [
                    ['name' => 'All-purpose flour', 'quantity' => '400', 'unit' => 'g', 'cost' => '0.85'],
                    ['name' => 'Sugar', 'quantity' => '300', 'unit' => 'g', 'cost' => '0.75'],
                    ['name' => 'Butter (room temp)', 'quantity' => '200', 'unit' => 'g', 'cost' => '1.60'],
                    ['name' => 'Eggs (large)', 'quantity' => '4', 'unit' => 'pieces', 'cost' => '1.00'],
                    ['name' => 'Vanilla extract', 'quantity' => '15', 'unit' => 'ml', 'cost' => '0.75'],
                    ['name' => 'Milk', 'quantity' => '240', 'unit' => 'ml', 'cost' => '0.60'],
                    ['name' => 'Baking powder', 'quantity' => '15', 'unit' => 'g', 'cost' => '0.05'],
                    ['name' => 'Powdered sugar (frosting)', 'quantity' => '500', 'unit' => 'g', 'cost' => '1.25'],
                    ['name' => 'Butter (frosting)', 'quantity' => '250', 'unit' => 'g', 'cost' => '2.00'],
                ],
                'instructions' => 'Cream butter and sugar, add eggs one at a time, then vanilla. Alternate flour mixture and milk. Divide between 3 pans. Bake at 350°F for 25-30 minutes. Cool completely. Make buttercream frosting, assemble layers with frosting between and around cake.',
                'prep_time_minutes' => 120,
            ],
            [
                'product_name' => 'Bourbon Pecan Pie',
                'name' => 'Rich Bourbon Pecan Pie',
                'ingredients' => [
                    ['name' => 'Pie crust (prepared)', 'quantity' => '1', 'unit' => 'piece', 'cost' => '2.00'],
                    ['name' => 'Pecan halves', 'quantity' => '300', 'unit' => 'g', 'cost' => '6.00'],
                    ['name' => 'Dark corn syrup', 'quantity' => '200', 'unit' => 'ml', 'cost' => '1.50'],
                    ['name' => 'Brown sugar', 'quantity' => '100', 'unit' => 'g', 'cost' => '0.30'],
                    ['name' => 'Eggs (large)', 'quantity' => '3', 'unit' => 'pieces', 'cost' => '0.75'],
                    ['name' => 'Bourbon whiskey', 'quantity' => '30', 'unit' => 'ml', 'cost' => '1.00'],
                    ['name' => 'Vanilla extract', 'quantity' => '5', 'unit' => 'ml', 'cost' => '0.25'],
                    ['name' => 'Salt', 'quantity' => '2', 'unit' => 'g', 'cost' => '0.01'],
                ],
                'instructions' => 'Toast pecans lightly. Whisk eggs, add corn syrup, brown sugar, bourbon, vanilla, and salt. Arrange pecans in pie crust, pour filling over. Bake at 350°F for 50-60 minutes until center is set but still slightly jiggly. Cool completely before slicing.',
                'prep_time_minutes' => 90,
            ],
            [
                'product_name' => 'Fruit Tarts',
                'name' => 'Individual Fresh Fruit Tarts',
                'ingredients' => [
                    ['name' => 'Pâte sucrée (sweet pastry)', 'quantity' => '400', 'unit' => 'g', 'cost' => '2.50'],
                    ['name' => 'Pastry cream', 'quantity' => '300', 'unit' => 'ml', 'cost' => '2.00'],
                    ['name' => 'Mixed fresh berries', 'quantity' => '200', 'unit' => 'g', 'cost' => '4.50'],
                    ['name' => 'Kiwi fruit', 'quantity' => '2', 'unit' => 'pieces', 'cost' => '1.00'],
                    ['name' => 'Apricot jam (for glaze)', 'quantity' => '50', 'unit' => 'ml', 'cost' => '0.75'],
                    ['name' => 'Water', 'quantity' => '15', 'unit' => 'ml', 'cost' => '0.01'],
                ],
                'instructions' => 'Roll out pastry, line individual tart shells. Blind bake at 375°F for 15 minutes. Cool completely. Fill with pastry cream, arrange fresh fruit attractively on top. Heat jam with water to make glaze, brush over fruit. Chill until serving.',
                'prep_time_minutes' => 75,
            ],
            [
                'product_name' => 'Pumpkin Spice Cheesecake',
                'name' => 'Seasonal Pumpkin Spice Cheesecake',
                'ingredients' => [
                    ['name' => 'Graham cracker crumbs', 'quantity' => '200', 'unit' => 'g', 'cost' => '1.50'],
                    ['name' => 'Cream cheese', 'quantity' => '600', 'unit' => 'g', 'cost' => '7.50'],
                    ['name' => 'Pumpkin puree', 'quantity' => '400', 'unit' => 'g', 'cost' => '2.00'],
                    ['name' => 'Sugar', 'quantity' => '200', 'unit' => 'g', 'cost' => '0.50'],
                    ['name' => 'Eggs (large)', 'quantity' => '3', 'unit' => 'pieces', 'cost' => '0.75'],
                    ['name' => 'Pumpkin pie spice', 'quantity' => '10', 'unit' => 'g', 'cost' => '0.30'],
                    ['name' => 'Vanilla extract', 'quantity' => '10', 'unit' => 'ml', 'cost' => '0.50'],
                    ['name' => 'Heavy cream', 'quantity' => '100', 'unit' => 'ml', 'cost' => '0.75'],
                ],
                'instructions' => 'Press graham cracker crust into springform pan. Beat cream cheese until smooth, add sugar, pumpkin, eggs, spices, vanilla, and cream. Pour over crust. Bake in water bath at 325°F for 60-70 minutes. Cool gradually, chill overnight before serving.',
                'prep_time_minutes' => 120,
            ],
        ];

        foreach ($recipes as $recipeData) {
            $product = $products->where('name', $recipeData['product_name'])->first();
            
            if ($product) {
                $totalCost = collect($recipeData['ingredients'])->sum('cost');
                
                Recipe::create([
                    'product_id' => $product->id,
                    'name' => $recipeData['name'],
                    'ingredients' => $recipeData['ingredients'],
                    'instructions' => $recipeData['instructions'],
                    'prep_time_minutes' => $recipeData['prep_time_minutes'],
                    'cost' => $totalCost,
                ]);
            }
        }
    }
}