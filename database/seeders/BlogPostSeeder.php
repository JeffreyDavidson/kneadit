<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        if (BlogPost::count() > 0) {
            return;
        }

        $posts = [
            [
                'title' => 'The Secret to Perfect Sourdough: Our Journey',
                'slug' => 'secret-to-perfect-sourdough',
                'excerpt' => 'After years of experimenting with different flours, hydration levels, and fermentation times, we\'ve finally perfected our signature sourdough recipe. Here\'s what we learned along the way.',
                'body' => '<p>Sourdough is more than just bread — it\'s a living, breathing process that requires patience, attention, and a whole lot of love. When we first started our bakery, we knew sourdough would be at the heart of everything we do.</p><p>The journey to our perfect loaf took over two years of daily baking, note-taking, and countless taste tests. We experimented with local wheat varieties, different hydration levels (from 65% all the way up to 85%), and fermentation schedules that ranged from 12 to 72 hours.</p><p>What we discovered is that the best sourdough isn\'t about following a recipe — it\'s about developing an intuition for the dough. The way it feels, the way it smells, the tiny bubbles that form on the surface during bulk fermentation.</p><p>Our starter, affectionately named "Betty," is now over 3 years old and produces the most consistently tangy, complex flavor we\'ve ever achieved. She\'s fed twice daily with a 50/50 blend of organic bread flour and whole wheat.</p><h2>Our Tips for Home Bakers</h2><ul><li><strong>Temperature matters more than time.</strong> Your kitchen temperature affects fermentation dramatically.</li><li><strong>Don\'t rush the bulk ferment.</strong> Let the dough tell you when it\'s ready.</li><li><strong>Steam is your friend.</strong> A Dutch oven creates the perfect steamy environment for an incredible crust.</li><li><strong>Practice patience.</strong> Let the loaf cool completely before cutting — the crumb is still setting.</li></ul>',
                'is_published' => true,
                'published_at' => now()->subDays(3),
                'author_name' => 'The Baker',
                'tags' => ['Recipes', 'Sourdough', 'Behind the Scenes'],
            ],
            [
                'title' => '5 Reasons to Choose a Local Bakery Over Store-Bought',
                'slug' => '5-reasons-choose-local-bakery',
                'excerpt' => 'We might be biased, but there are some seriously good reasons to skip the supermarket bread aisle and support your local baker instead.',
                'body' => '<p>In a world of mass-produced everything, choosing a local bakery is a small act of rebellion — and your taste buds will thank you for it.</p><h2>1. Freshness You Can Taste</h2><p>Our bread is baked the same day you buy it. No preservatives, no shelf-life extenders, just flour, water, salt, and time.</p><h2>2. You Know Your Baker</h2><p>When you buy from us, you\'re not just a transaction. We know your name, your favorite order, and that you like your cinnamon rolls extra gooey.</p><h2>3. Better Ingredients</h2><p>We source locally whenever possible. Our flour comes from regional mills, our eggs from free-range farms within 50 miles, and our butter is always real, always European-style.</p><h2>4. Supporting Your Community</h2><p>Every dollar spent at a local bakery circulates through your community — supporting local jobs, local suppliers, and the neighborhood character that makes your town special.</p><h2>5. It Just Tastes Better</h2><p>There\'s no getting around it. Bread that was mixed, shaped, proofed, and baked by human hands just has something that factory bread never will.</p>',
                'is_published' => true,
                'published_at' => now()->subDays(7),
                'author_name' => 'The Baker',
                'tags' => ['Community', 'Tips'],
            ],
            [
                'title' => 'Behind the Scenes: A Day in Our Kitchen',
                'slug' => 'behind-the-scenes-day-in-kitchen',
                'excerpt' => 'Ever wondered what happens before the doors open? Our day starts at 4 AM with flour-dusted counters and the smell of fresh bread filling the kitchen.',
                'body' => '<p>The alarm goes off at 3:45 AM. By 4:00, the ovens are preheating and the first batches of dough — shaped and proofed overnight — are ready to bake.</p><p>By 5:30, the kitchen smells like heaven. Golden sourdough loaves cooling on wire racks, croissants puffing up in the oven, and cinnamon rolls getting their signature cream cheese frosting.</p><p>The morning rush starts around 7, and there\'s nothing better than seeing a customer\'s face light up when they walk in and breathe in that warm, yeasty aroma.</p><p>By noon, we\'re already prepping for tomorrow — mixing new doughs, shaping baguettes, and experimenting with seasonal specials. It\'s hard work, but there\'s nowhere else we\'d rather be.</p>',
                'is_published' => true,
                'published_at' => now()->subDays(14),
                'author_name' => 'The Baker',
                'tags' => ['Behind the Scenes', 'Kitchen Life'],
            ],
            [
                'title' => 'Spring Menu Preview: What\'s Coming Soon',
                'slug' => 'spring-menu-preview',
                'excerpt' => 'Spring is right around the corner, and we\'re excited to share some of the seasonal treats we\'ve been developing in the kitchen.',
                'body' => '<p>As the weather warms up, so does our imagination. We\'ve been busy testing new recipes that celebrate the best of spring flavors.</p><h2>Coming Soon</h2><ul><li><strong>Lemon Lavender Scones</strong> — light, flaky, with a subtle floral note</li><li><strong>Strawberry Rhubarb Galette</strong> — rustic, beautiful, and perfectly sweet-tart</li><li><strong>Honey Oat Bread</strong> — soft, slightly sweet, and perfect for sandwiches</li><li><strong>Key Lime Cupcakes</strong> — tangy, creamy, and topped with graham cracker crumble</li></ul><p>We\'ll be rolling these out over the next few weeks. Follow us on social media to be the first to know when each item drops!</p>',
                'is_published' => true,
                'published_at' => now()->subDays(21),
                'author_name' => 'The Baker',
                'tags' => ['Seasonal', 'Menu Updates'],
            ],
            [
                'title' => 'Our Favorite Cookie Decorating Tips for Beginners',
                'slug' => 'cookie-decorating-tips-beginners',
                'excerpt' => 'You don\'t need to be a professional to make beautiful decorated cookies. Here are our go-to tips for stunning results at home.',
                'body' => '<p>Cookie decorating can seem intimidating, but with a few simple techniques, anyone can create showstopping cookies at home.</p><h2>Start with Good Royal Icing</h2><p>The foundation of great cookie decorating is smooth, well-mixed royal icing. Use meringue powder (not raw egg whites) for consistency and food safety.</p><h2>Outline First, Then Flood</h2><p>Use a thicker consistency to pipe your outlines, let them set for a minute, then fill in with a thinner "flood" consistency icing. This gives you clean, defined shapes.</p><h2>Less is More</h2><p>Some of the most beautiful cookies are the simplest. A single-color base with one accent detail can be more elegant than an over-decorated cookie.</p><h2>Let Each Layer Dry</h2><p>Patience is key. Let each layer of icing dry completely (at least 2-4 hours) before adding the next. This prevents bleeding and keeps your design crisp.</p>',
                'is_published' => true,
                'published_at' => now()->subDays(30),
                'author_name' => 'The Baker',
                'tags' => ['Tips', 'Recipes', 'Cookies'],
            ],
            [
                'title' => 'Why We Choose Organic Flour (And You Should Too)',
                'slug' => 'why-we-choose-organic-flour',
                'excerpt' => 'Making the switch to organic flour was one of the best decisions we ever made for our bakery — and our customers can taste the difference.',
                'body' => '<p>When we first opened, we used conventional all-purpose flour like most bakeries. It worked fine. The bread was good. But we knew it could be better.</p><p>After experimenting with organic stone-ground flour from a local mill, the difference was undeniable. The flavor was deeper, nuttier, more complex. The dough handled differently — more alive, more responsive.</p><p>Yes, organic flour costs more. But for us, the quality difference is worth every penny. Our customers notice it too — it\'s one of the most common compliments we receive.</p>',
                'is_published' => true,
                'published_at' => now()->subDays(45),
                'author_name' => 'The Baker',
                'tags' => ['Ingredients', 'Behind the Scenes'],
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::create($post);
        }
    }
}
