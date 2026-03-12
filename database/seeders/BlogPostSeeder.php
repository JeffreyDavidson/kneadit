<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'title' => 'Cottage Food Laws by State: The Complete 2026 Guide',
                'slug' => 'cottage-food-laws-by-state',
                'category' => 'laws',
                'meta_title' => 'Cottage Food Laws by State (2026) — Complete Guide | KneadIt',
                'meta_description' => 'Every state\'s cottage food law in one place. Revenue limits, labeling requirements, allowed products, and what you need to know to sell legally from home.',
                'excerpt' => 'Every state allows some form of cottage food sales, but the rules vary wildly. Here\'s what you need to know about your state\'s laws before you start selling.',
                'body' => <<<'HTML'
<h2>What Are Cottage Food Laws?</h2>
<p>Cottage food laws allow individuals to prepare and sell certain types of food from their home kitchens without needing a commercial kitchen, food handler's license, or health department inspection. Every U.S. state has some version of these laws, but the details — what you can sell, how much, and where — vary significantly.</p>

<h2>Key Factors That Vary by State</h2>
<p>Before you start selling, you need to understand your state's rules in these areas:</p>
<ul>
<li><strong>Annual revenue cap</strong> — Ranges from $25,000 (some states) to unlimited (others). Florida's cap is $250,000.</li>
<li><strong>Allowed products</strong> — Most states allow baked goods, candies, and jams. Some allow more.</li>
<li><strong>Labeling requirements</strong> — Nearly all states require labels with your name, address, and a "made in a home kitchen" disclaimer.</li>
<li><strong>Where you can sell</strong> — Some states limit you to farmers markets and direct sales. Others allow online ordering and delivery.</li>
<li><strong>Registration</strong> — Some states require registration or a permit. Others don't.</li>
</ul>

<h2>Florida Cottage Food (Example)</h2>
<p>Florida has one of the most generous cottage food laws in the country:</p>
<ul>
<li><strong>Revenue cap:</strong> $250,000/year</li>
<li><strong>License required:</strong> No</li>
<li><strong>Allowed products:</strong> Baked goods, candies, dried fruits, popcorn, granola, and more</li>
<li><strong>Online sales:</strong> Yes, with direct delivery</li>
<li><strong>Labeling:</strong> Required — name, address, "Made in a Home Kitchen" statement, allergens</li>
</ul>

<h2>How to Check Your State</h2>
<p>The best resource is your state's Department of Agriculture website. Search for "[your state] cottage food law" and look for the official guidance document. The <a href="https://forrager.com/law/">Forrager cottage food law directory</a> is also an excellent community-maintained resource.</p>

<h2>Getting Started</h2>
<p>Once you understand your state's rules, you need a way to manage orders, track finances, and stay compliant with revenue caps. That's exactly what KneadIt was built for — specifically for cottage food bakers like you.</p>
HTML,
            ],
            [
                'title' => 'How to Price Your Baked Goods (Without Undercharging)',
                'slug' => 'how-to-price-baked-goods',
                'category' => 'tips',
                'meta_title' => 'How to Price Baked Goods for Profit — Cottage Food Pricing Guide | KneadIt',
                'meta_description' => 'Stop guessing your prices. Learn the formula for pricing baked goods that covers ingredients, labor, overhead, and profit — so you actually make money.',
                'excerpt' => 'The #1 mistake cottage food bakers make is underpricing. Here\'s the exact formula to price your products for real profit.',
                'body' => <<<'HTML'
<h2>The Pricing Problem</h2>
<p>Most home bakers price their products based on what they'd pay at a grocery store. This is a mistake. You're not a factory — you're an artisan making small-batch, handcrafted products. Your prices should reflect that.</p>

<h2>The Formula</h2>
<p>Here's a simple pricing formula that works for cottage food businesses:</p>
<blockquote><strong>Price = (Ingredient Cost × 3) + (Labor Hours × Your Hourly Rate) + Packaging</strong></blockquote>
<p>The 3x multiplier on ingredients covers waste, overhead (electricity, gas, equipment wear), and profit margin.</p>

<h3>Example: A Dozen Cookies</h3>
<ul>
<li>Ingredients: $4.50</li>
<li>Ingredient cost × 3: $13.50</li>
<li>Labor: 1.5 hours × $20/hr = $30.00</li>
<li>Packaging: $1.50</li>
<li><strong>Price: $45.00 per dozen</strong></li>
</ul>
<p>That might feel high, but artisan cookies at farmers markets regularly sell for $4-6 each ($48-72/dozen). You're not competing with Chips Ahoy.</p>

<h2>Common Pricing Mistakes</h2>
<ul>
<li><strong>Forgetting labor</strong> — Your time has value. If you're not paying yourself, you have a hobby, not a business.</li>
<li><strong>Ignoring overhead</strong> — Gas, electricity, equipment depreciation, and vehicle costs for delivery all add up.</li>
<li><strong>Race to the bottom</strong> — Competing on price with other home bakers is a losing game. Compete on quality and service.</li>
<li><strong>Not raising prices</strong> — Ingredient costs go up. Review prices quarterly.</li>
</ul>

<h2>Track Your Real Costs</h2>
<p>You can't price correctly if you don't know your true costs. KneadIt's recipe cost calculator lets you input every ingredient and see your exact cost per item — so you can price with confidence, not guesswork.</p>
HTML,
            ],
            [
                'title' => '5 Tools Every Cottage Food Baker Needs in 2026',
                'slug' => 'tools-cottage-food-baker-needs',
                'category' => 'guides',
                'meta_title' => '5 Essential Tools for Cottage Food Bakers (2026) | KneadIt',
                'meta_description' => 'The essential tools and software every home baker needs to run a profitable cottage food business. From order management to accounting.',
                'excerpt' => 'Running a home bakery takes more than a good oven. Here are the 5 tools that separate hobby bakers from profitable businesses.',
                'body' => <<<'HTML'
<h2>1. Order Management System</h2>
<p>Spreadsheets work until they don't. Once you're juggling more than a few orders per week, you need a real system. You need to see what's ordered, when it's due, and whether it's been paid — at a glance.</p>
<p>Look for something built specifically for home bakers, not a generic small business tool. Generic tools make you adapt your workflow to the software. Baker-specific tools adapt to how you actually work.</p>

<h2>2. Recipe Cost Calculator</h2>
<p>If you can't tell me exactly how much a batch of your sourdough costs to make — ingredients, packaging, everything — you're probably undercharging. A good cost calculator breaks down each recipe to the penny.</p>

<h2>3. Financial Tracking</h2>
<p>Cottage food businesses have tax obligations. You need to track income, expenses, and (depending on your state) sales tax. At minimum, track these expense categories:</p>
<ul>
<li>Ingredients (Cost of Goods Sold)</li>
<li>Packaging and labels</li>
<li>Delivery costs (gas, mileage)</li>
<li>Farmers market booth fees</li>
<li>Marketing and website costs</li>
<li>Equipment purchases</li>
</ul>

<h2>4. Customer Communication</h2>
<p>Order confirmations, pickup reminders, "your order is ready" texts — these touchpoints build trust and reduce no-shows. Even a simple email template system makes you look professional.</p>

<h2>5. A Professional Storefront</h2>
<p>Instagram DMs are not an order system. A proper online menu where customers can browse products, see prices, and place orders reduces back-and-forth and makes you look legitimate.</p>

<h3>The All-in-One Option</h3>
<p>KneadIt combines all five of these into a single platform built specifically for cottage food bakers. Orders, recipes, finances, customer emails, and a beautiful storefront — without cobbling together five different tools.</p>
HTML,
            ],
            [
                'title' => 'How to Start a Home Bakery Business: Step by Step',
                'slug' => 'how-to-start-home-bakery-business',
                'category' => 'guides',
                'meta_title' => 'How to Start a Home Bakery Business (2026 Guide) | KneadIt',
                'meta_description' => 'Complete step-by-step guide to starting a cottage food bakery from home. From checking your state laws to getting your first customers.',
                'excerpt' => 'Thinking about turning your baking hobby into a business? Here\'s everything you need to do, step by step.',
                'body' => <<<'HTML'
<h2>Step 1: Check Your State's Cottage Food Law</h2>
<p>Before anything else, look up your state's cottage food regulations. You need to know what products you're allowed to sell, any revenue limits, labeling requirements, and whether you need to register.</p>

<h2>Step 2: Decide What to Sell</h2>
<p>Start with what you're known for. If everyone raves about your sourdough, lead with that. Don't try to sell everything — a focused menu of 5-10 items is better than 50 mediocre options.</p>
<p><strong>Pro tip:</strong> Pick products with good margins. Custom decorated cakes have high labor costs. Cookies, bread, and brownies scale much better.</p>

<h2>Step 3: Calculate Your Prices</h2>
<p>Use the formula: <strong>(Ingredients × 3) + Labor + Packaging = Price</strong>. Don't underprice yourself. You're not a grocery store.</p>

<h2>Step 4: Set Up Your Business</h2>
<ul>
<li><strong>Business name</strong> — Pick something memorable. Check that it's not taken.</li>
<li><strong>Labels</strong> — Most states require cottage food labels. Include your name, address, ingredients, allergens, and "Made in a Home Kitchen."</li>
<li><strong>Payment method</strong> — Set up PayPal, Venmo, or a card reader. Don't be cash-only.</li>
<li><strong>Order system</strong> — Get something to track orders from day one. Even a simple spreadsheet beats nothing.</li>
</ul>

<h2>Step 5: Get Your First Customers</h2>
<ul>
<li><strong>Friends and family</strong> — Your first customers are people who already love your baking.</li>
<li><strong>Social media</strong> — Instagram and Facebook are free. Post photos of your products regularly.</li>
<li><strong>Farmers markets</strong> — Great for exposure and building a customer base.</li>
<li><strong>Word of mouth</strong> — Deliver an amazing product and people will talk.</li>
</ul>

<h2>Step 6: Scale Smart</h2>
<p>Once orders start coming in, you need systems. Manual order tracking breaks down fast. Financial tracking becomes critical for taxes. A proper order management platform lets you focus on baking instead of admin work.</p>
HTML,
            ],
            [
                'title' => 'Cottage Food Labeling Requirements: What Goes on Your Labels',
                'slug' => 'cottage-food-labeling-requirements',
                'category' => 'laws',
                'meta_title' => 'Cottage Food Label Requirements — What You Must Include | KneadIt',
                'meta_description' => 'What every cottage food baker needs on their product labels. Allergens, disclaimers, ingredients, and state-specific requirements explained.',
                'excerpt' => 'Getting your labels right isn\'t optional — it\'s the law. Here\'s exactly what needs to be on every product you sell.',
                'body' => <<<'HTML'
<h2>Why Labels Matter</h2>
<p>Every state that allows cottage food sales requires some form of labeling. Labels protect your customers (allergen info) and protect you (legal compliance). Getting them wrong can result in fines or losing your ability to sell.</p>

<h2>What Most States Require</h2>
<p>While specific requirements vary, most states require these elements:</p>

<h3>1. Product Name</h3>
<p>What it is: "Chocolate Chip Cookies," "Sourdough Bread," etc.</p>

<h3>2. Ingredients List</h3>
<p>Listed in order of weight (most to least), just like commercial products. Include everything — even water and salt.</p>

<h3>3. Allergen Information</h3>
<p>The Big 9 allergens must be clearly identified: milk, eggs, fish, shellfish, tree nuts, peanuts, wheat, soybeans, and sesame.</p>

<h3>4. Net Weight</h3>
<p>The weight of the product (not including packaging).</p>

<h3>5. Your Name and Address</h3>
<p>The name and home address of the cottage food operation. A PO Box is not acceptable in most states.</p>

<h3>6. "Made in a Home Kitchen" Disclaimer</h3>
<p>Most states require a statement like: <em>"Made in a home kitchen that has not been inspected by the Department of Health."</em> The exact wording varies — check your state's requirements.</p>

<h3>7. Date</h3>
<p>Production date, sell-by date, or best-by date depending on your state.</p>

<h2>Label Design Tips</h2>
<ul>
<li>Use a clear, readable font (minimum 10pt for most text)</li>
<li>Print on waterproof labels if your products might get condensation</li>
<li>Consider professional label printing — it makes a huge difference in perceived quality</li>
<li>Keep a template so every batch is consistent</li>
</ul>

<h2>Where to Get Labels</h2>
<p>Avery labels + a home printer work fine for starting out. As you grow, services like Sticker Mule or Avery WePrint offer professional-quality labels at reasonable prices.</p>
HTML,
            ],
            [
                'title' => 'Farmers Market Tips: How to Sell More at Your Booth',
                'slug' => 'farmers-market-tips-sell-more',
                'category' => 'tips',
                'meta_title' => 'Farmers Market Tips for Bakers — Sell More at Your Booth | KneadIt',
                'meta_description' => 'Proven strategies to increase sales at farmers markets. Booth setup, pricing display, sampling strategy, and customer engagement tips for cottage food bakers.',
                'excerpt' => 'Your booth setup and strategy matter as much as your products. Here\'s how to maximize sales at every market.',
                'body' => <<<'HTML'
<h2>Before the Market</h2>

<h3>Know Your Inventory</h3>
<p>Track what sells and what doesn't. After a few markets, you'll know exactly how much to bring. Bringing too much means waste. Too little means missed sales.</p>

<h3>Prep for Speed</h3>
<p>Pre-package everything. Have bags, boxes, and labels ready. The faster you can complete a transaction, the more customers you can serve during the rush.</p>

<h2>Booth Setup</h2>

<h3>Height Creates Interest</h3>
<p>Use risers, crates, and tiered displays to create visual interest. A flat table is boring. Height draws the eye and makes your booth look more abundant.</p>

<h3>Signage That Sells</h3>
<p>Every product needs a clear sign with name and price. People won't ask — they'll just walk by. Chalkboard signs or printed cards both work well.</p>

<h3>Samples Convert</h3>
<p>If your state allows samples, offer them. Cut cookies into quarters, slice bread into pieces. A customer who tastes your product is 5x more likely to buy.</p>

<h2>During the Market</h2>
<ul>
<li><strong>Stand up and greet people.</strong> "Hi, would you like to try our sourdough?" beats sitting behind your table on your phone.</li>
<li><strong>Tell your story.</strong> People buy from people. "I've been baking sourdough for 10 years with my grandmother's starter" is compelling.</li>
<li><strong>Accept cards.</strong> Many people don't carry cash. A simple Square reader pays for itself in one market.</li>
<li><strong>Collect emails.</strong> A simple signup sheet or QR code to your order page turns one-time market customers into repeat online customers.</li>
</ul>

<h2>After the Market</h2>
<p>Track everything: what sold, what didn't, total revenue, and any customer feedback. This data drives better decisions for next week.</p>
<p>Follow up with email subscribers. Let them know they can order online for pickup or delivery — don't make them wait until next week's market.</p>
HTML,
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::updateOrCreate(
                ['slug' => $post['slug']],
                array_merge($post, [
                    'is_published' => true,
                    'published_at' => now()->subDays(rand(1, 30)),
                ])
            );
        }
    }
}
