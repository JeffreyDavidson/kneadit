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
                'meta_title' => 'Cottage Food Laws by State (2026): Complete Guide | KneadIt',
                'meta_description' => 'Every state\'s cottage food law in one place. Revenue limits, labeling requirements, allowed products, and what you need to know to sell legally from home.',
                'excerpt' => 'Every state allows some form of cottage food sales, but the rules vary wildly. Here\'s what you need to know about your state\'s laws before you start selling.',
                'body' => <<<'HTML'
<h2>What Are Cottage Food Laws?</h2>
<p>Cottage food laws allow individuals to prepare and sell certain types of food from their home kitchens without needing a commercial kitchen, food handler's license, or health department inspection. Every U.S. state has some version of these laws, but the details (what you can sell, how much, and where) vary significantly.</p>

<h2>Key Factors That Vary by State</h2>
<p>Before you start selling, you need to understand your state's rules in these areas:</p>
<ul>
<li><strong>Annual revenue cap:</strong> Ranges from $25,000 (some states) to unlimited (others). Florida's cap is $250,000.</li>
<li><strong>Allowed products:</strong> Most states allow baked goods, candies, and jams. Some allow more.</li>
<li><strong>Labeling requirements:</strong> Nearly all states require labels with your name, address, and a "made in a home kitchen" disclaimer.</li>
<li><strong>Where you can sell:</strong> Some states limit you to farmers markets and direct sales. Others allow online ordering and delivery.</li>
<li><strong>Registration:</strong> Some states require registration or a permit. Others don't.</li>
</ul>

<h2>Florida Cottage Food (Example)</h2>
<p>Florida has one of the most generous cottage food laws in the country:</p>
<ul>
<li><strong>Revenue cap:</strong> $250,000/year</li>
<li><strong>License required:</strong> No</li>
<li><strong>Allowed products:</strong> Baked goods, candies, dried fruits, popcorn, granola, and more</li>
<li><strong>Online sales:</strong> Yes, with direct delivery</li>
<li><strong>Labeling:</strong> Required. Name, address, "Made in a Home Kitchen" statement, allergens</li>
</ul>

<h2>How to Check Your State</h2>
<p>The best resource is your state's Department of Agriculture website. Search for "[your state] cottage food law" and look for the official guidance document. The <a href="https://forrager.com/law/">Forrager cottage food law directory</a> is also an excellent community-maintained resource.</p>

<h2>Getting Started</h2>
<p>Once you understand your state's rules, you need a way to manage orders, track finances, and stay compliant with revenue caps. That's exactly what KneadIt was built for, specifically for cottage food bakers like you.</p>
HTML,
            ],
            [
                'title' => 'How to Price Your Baked Goods (Without Undercharging)',
                'slug' => 'how-to-price-baked-goods',
                'category' => 'tips',
                'meta_title' => 'How to Price Baked Goods for Profit: Cottage Food Pricing Guide | KneadIt',
                'meta_description' => 'Stop guessing your prices. Learn the formula for pricing baked goods that covers ingredients, labor, overhead, and profit, so you actually make money.',
                'excerpt' => 'The #1 mistake cottage food bakers make is underpricing. Here\'s the exact formula to price your products for real profit.',
                'body' => <<<'HTML'
<h2>The Pricing Problem</h2>
<p>Most home bakers price their products based on what they'd pay at a grocery store. This is a mistake. You're not a factory. You're an artisan making small-batch, handcrafted products. Your prices should reflect that.</p>

<h2>The Formula</h2>
<p>Here's a simple pricing formula that works for cottage food businesses:</p>
<blockquote><strong>Price = (Ingredient Cost × 3) + (Labor Hours × Your Hourly Rate) + Packaging</strong></blockquote>
<p>The 3x multiplier on ingredients covers waste, overhead (electricity, gas, equipment wear), and profit margin.</p>

<h3>Example: A Dozen Cookies</h3>
<ul>
<li>Ingredients: $4.50</li>
<li>Ingredient cost × 3: $13.50</li>
<li>Labor: 1.5 hours × your hourly rate (at minimum, pay yourself what you'd earn at a day job)</li>
<li>Packaging: $1.50</li>
<li><strong>Price: $45.00 per dozen</strong></li>
</ul>
<p>That might feel high, but artisan cookies at farmers markets regularly sell for $4-6 each ($48-72/dozen). You're not competing with Chips Ahoy.</p>

<h2>Common Pricing Mistakes</h2>
<ul>
<li><strong>Forgetting labor:</strong> Your time has value. If you're not paying yourself, you have a hobby, not a business.</li>
<li><strong>Ignoring overhead:</strong> Gas, electricity, equipment depreciation, and vehicle costs for delivery all add up.</li>
<li><strong>Race to the bottom:</strong> Competing on price with other home bakers is a losing game. Compete on quality and service.</li>
<li><strong>Not raising prices:</strong> Ingredient costs go up. Review prices quarterly.</li>
</ul>

<h2>Track Your Real Costs</h2>
<p>You can't price correctly if you don't know your true costs. KneadIt's recipe cost calculator lets you input every ingredient and see your exact cost per item, so you can price with confidence, not guesswork.</p>
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
<p>Spreadsheets work until they don't. Once you're juggling more than a few orders per week, you need a real system. You need to see what's ordered, when it's due, and whether it's been paid, all at a glance.</p>
<p>Look for something built specifically for home bakers, not a generic small business tool. Generic tools make you adapt your workflow to the software. Baker-specific tools adapt to how you actually work.</p>

<h2>2. Recipe Cost Calculator</h2>
<p>If you can't tell me exactly how much a batch of your sourdough costs to make (ingredients, packaging, everything), you're probably undercharging. A good cost calculator breaks down each recipe to the penny.</p>

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
<p>Order confirmations, pickup reminders, "your order is ready" texts. These touchpoints build trust and reduce no-shows. Even a simple email template system makes you look professional.</p>

<h2>5. A Professional Storefront</h2>
<p>Instagram DMs are not an order system. A proper online menu where customers can browse products, see prices, and place orders reduces back-and-forth and makes you look legitimate.</p>

<h3>The All-in-One Option</h3>
<p>KneadIt combines all five of these into a single platform built specifically for cottage food bakers. Orders, recipes, finances, customer emails, and a beautiful storefront, without cobbling together five different tools.</p>
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
<p>Start with what you're known for. If everyone raves about your sourdough, lead with that. Don't try to sell everything. A focused menu of 5-10 items is better than 50 mediocre options.</p>
<p><strong>Pro tip:</strong> Pick products with good margins. Custom decorated cakes have high labor costs. Cookies, bread, and brownies scale much better.</p>

<h2>Step 3: Calculate Your Prices</h2>
<p>Use the formula: <strong>(Ingredients × 3) + Labor + Packaging = Price</strong>. Don't underprice yourself. You're not a grocery store.</p>

<h2>Step 4: Set Up Your Business</h2>
<ul>
<li><strong>Business name:</strong> Pick something memorable. Check that it's not taken.</li>
<li><strong>Labels:</strong> Most states require cottage food labels. Include your name, address, ingredients, allergens, and "Made in a Home Kitchen."</li>
<li><strong>Payment method:</strong> Set up PayPal, Venmo, or a card reader. Don't be cash-only.</li>
<li><strong>Order system:</strong> Get something to track orders from day one. Even a simple spreadsheet beats nothing.</li>
</ul>

<h2>Step 5: Get Your First Customers</h2>
<ul>
<li><strong>Friends and family:</strong> Your first customers are people who already love your baking.</li>
<li><strong>Social media:</strong> Instagram and Facebook are free. Post photos of your products regularly.</li>
<li><strong>Farmers markets:</strong> Great for exposure and building a customer base.</li>
<li><strong>Word of mouth:</strong> Deliver an amazing product and people will talk.</li>
</ul>

<h2>Step 6: Scale Smart</h2>
<p>Once orders start coming in, you need systems. Manual order tracking breaks down fast. Financial tracking becomes critical for taxes. A proper order management platform lets you focus on baking instead of admin work.</p>
HTML,
            ],
            [
                'title' => 'Cottage Food Labeling Requirements: What Goes on Your Labels',
                'slug' => 'cottage-food-labeling-requirements',
                'category' => 'laws',
                'meta_title' => 'Cottage Food Label Requirements: What You Must Include | KneadIt',
                'meta_description' => 'What every cottage food baker needs on their product labels. Allergens, disclaimers, ingredients, and state-specific requirements explained.',
                'excerpt' => 'Getting your labels right isn\'t optional; it\'s the law. Here\'s exactly what needs to be on every product you sell.',
                'body' => <<<'HTML'
<h2>Why Labels Matter</h2>
<p>Every state that allows cottage food sales requires some form of labeling. Labels protect your customers (allergen info) and protect you (legal compliance). Getting them wrong can result in fines or losing your ability to sell.</p>

<h2>What Most States Require</h2>
<p>While specific requirements vary, most states require these elements:</p>

<h3>1. Product Name</h3>
<p>What it is: "Chocolate Chip Cookies," "Sourdough Bread," etc.</p>

<h3>2. Ingredients List</h3>
<p>Listed in order of weight (most to least), just like commercial products. Include everything, even water and salt.</p>

<h3>3. Allergen Information</h3>
<p>The Big 9 allergens must be clearly identified: milk, eggs, fish, shellfish, tree nuts, peanuts, wheat, soybeans, and sesame.</p>

<h3>4. Net Weight</h3>
<p>The weight of the product (not including packaging).</p>

<h3>5. Your Name and Address</h3>
<p>The name and home address of the cottage food operation. A PO Box is not acceptable in most states.</p>

<h3>6. "Made in a Home Kitchen" Disclaimer</h3>
<p>Most states require a statement like: <em>"Made in a home kitchen that has not been inspected by the Department of Health."</em> The exact wording varies, so check your state's requirements.</p>

<h3>7. Date</h3>
<p>Production date, sell-by date, or best-by date depending on your state.</p>

<h2>Label Design Tips</h2>
<ul>
<li>Use a clear, readable font (minimum 10pt for most text)</li>
<li>Print on waterproof labels if your products might get condensation</li>
<li>Consider professional label printing. It makes a huge difference in perceived quality</li>
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
                'meta_title' => 'Farmers Market Tips for Bakers: Sell More at Your Booth | KneadIt',
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
<p>Every product needs a clear sign with name and price. People won't ask; they'll just walk by. Chalkboard signs or printed cards both work well.</p>

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
<p>Follow up with email subscribers. Let them know they can order online for pickup or delivery. Don't make them wait until next week's market.</p>
HTML,
            ],
            [
                'title' => 'How Much Can You Make Selling Baked Goods from Home?',
                'slug' => 'how-much-money-selling-baked-goods-from-home',
                'category' => 'tips',
                'meta_title' => 'How Much Money Can You Make Selling Baked Goods from Home? | KneadIt',
                'meta_description' => 'Realistic income expectations for cottage food bakers. What top home bakers earn, how to scale, and the math behind a profitable home bakery.',
                'excerpt' => 'The honest answer: it depends. But here\'s what real cottage food bakers are earning and how they got there.',
                'body' => <<<'HTML'
<h2>The Short Answer</h2>
<p>Most cottage food bakers earn between <strong>$500–$2,000/month</strong> part-time, with top performers hitting <strong>$5,000–$10,000/month</strong>. Your ceiling depends on your state's revenue cap, your product mix, and how seriously you treat it as a business.</p>

<h2>Revenue by Stage</h2>
<ul>
<li><strong>Month 1–3 (Getting Started):</strong> $200–$500/month. You're finding your products, building word-of-mouth, and figuring out pricing.</li>
<li><strong>Month 3–6 (Building Momentum):</strong> $500–$1,500/month. Repeat customers, social media traction, maybe your first farmers market.</li>
<li><strong>Month 6–12 (Established):</strong> $1,500–$4,000/month. Consistent orders, efficient processes, possibly a waitlist for busy periods.</li>
<li><strong>Year 2+ (Scaling):</strong> $4,000–$10,000+/month. Multiple channels, holiday rushes, possibly considering a commercial kitchen.</li>
</ul>

<h2>What Affects Your Income</h2>

<h3>Product Choice Matters</h3>
<p>Not all baked goods have the same margins:</p>
<ul>
<li><strong>High margin:</strong> Custom decorated cookies ($4–8 each), specialty cakes ($50–150+), macarons ($3–5 each)</li>
<li><strong>Good margin:</strong> Artisan bread ($6–12/loaf), brownies/bars ($3–5 each), cinnamon rolls ($4–6 each)</li>
<li><strong>Lower margin:</strong> Basic cookies ($1–2 each), muffins ($2–3 each), so volume is needed</li>
</ul>

<h3>Sales Channels</h3>
<p>Diversifying where you sell increases income:</p>
<ul>
<li><strong>Direct orders</strong> (online/phone): highest margin, lowest effort per sale</li>
<li><strong>Farmers markets:</strong> great for new customers, but booth fees + time eat into margins</li>
<li><strong>Wholesale to cafés:</strong> lower margin but consistent volume (check your state laws)</li>
<li><strong>Special events:</strong> weddings, corporate events = big orders</li>
</ul>

<h2>The Math</h2>
<p>Let's say you sell custom sugar cookies:</p>
<ul>
<li>Price: $48/dozen</li>
<li>Ingredient cost: $8/dozen</li>
<li>Packaging: $3/dozen</li>
<li>Labor: 2 hours × your hourly rate</li>
<li><strong>Profit per dozen: -$3</strong> (you're losing money at $48!)</li>
</ul>
<p>This is why pricing matters so much. At $72/dozen (market rate for custom cookies), you'd profit $21/dozen. Ten dozen orders per week = $840/month profit.</p>

<h2>Track Everything</h2>
<p>You can't grow what you don't measure. Track every order, every expense, every hour. KneadIt gives you the financial dashboard to see exactly where your money goes and where it comes from.</p>
HTML,
            ],
            [
                'title' => 'Do I Need a License to Sell Baked Goods from Home?',
                'slug' => 'do-i-need-license-sell-baked-goods-home',
                'category' => 'laws',
                'meta_title' => 'Do I Need a License to Sell Baked Goods from Home? (2026) | KneadIt',
                'meta_description' => 'The answer depends on your state. Learn about cottage food exemptions, when you need permits vs. when you don\'t, and how to sell legally.',
                'excerpt' => 'Spoiler: in most states, you don\'t need a license. But you do need to follow specific rules. Here\'s how it works.',
                'body' => <<<'HTML'
<h2>The Good News</h2>
<p>All 50 US states allow some form of home-based food sales under <strong>cottage food laws</strong>. In most states, you do <em>not</em> need a food handler's license, commercial kitchen, or health department inspection to sell baked goods from your home kitchen.</p>

<h2>What You Typically DON'T Need</h2>
<ul>
<li>Food handler's permit (most states)</li>
<li>Commercial kitchen inspection</li>
<li>Business license (in many states)</li>
<li>Health department approval</li>
</ul>

<h2>What You Typically DO Need</h2>
<ul>
<li><strong>Proper labeling:</strong> Name, address, ingredients, allergens, "Made in a Home Kitchen" disclaimer</li>
<li><strong>To stay within your revenue cap:</strong> Ranges from $25,000 to $250,000+ depending on state</li>
<li><strong>Registration:</strong> Some states require a simple registration (not a license). Usually free or under $50.</li>
<li><strong>Food safety training:</strong> A handful of states require a basic food safety course</li>
</ul>

<h2>States That DON'T Require a License or Registration</h2>
<p>Many states let you start selling immediately with just proper labels: Florida, Texas, Colorado, Utah, Ohio, and others. Check your state's specific rules.</p>

<h2>States With More Requirements</h2>
<p>Some states require permits, inspections, or food safety certifications: California, New York, New Jersey, and a few others have more stringent requirements.</p>

<h2>When You DO Need a License</h2>
<p>You typically need to step up to a commercial license when:</p>
<ul>
<li>You exceed your state's cottage food revenue cap</li>
<li>You want to sell products not on the "allowed" list (like anything requiring refrigeration)</li>
<li>You want to sell wholesale to stores or restaurants</li>
<li>You want to ship products across state lines (federal FDA regulations apply)</li>
</ul>

<h2>Bottom Line</h2>
<p>For most home bakers selling directly to customers in their state, cottage food laws make it easy to start legally. The key is knowing your state's specific rules and staying compliant. KneadIt tracks your revenue against your state's cap automatically, so you always know where you stand.</p>
HTML,
            ],
            [
                'title' => 'Instagram Marketing for Home Bakers: A Complete Guide',
                'slug' => 'instagram-marketing-home-bakers-guide',
                'category' => 'tips',
                'meta_title' => 'Instagram Marketing for Home Bakers: Get More Orders | KneadIt',
                'meta_description' => 'How to use Instagram to grow your cottage food business. Photo tips, hashtags, content ideas, and strategies that actually convert followers to customers.',
                'excerpt' => 'Instagram is the #1 marketing tool for home bakers. Here\'s how to use it to actually get orders, not just likes.',
                'body' => <<<'HTML'
<h2>Why Instagram Works for Bakers</h2>
<p>Baked goods are inherently visual. A perfectly golden sourdough loaf, a tray of decorated sugar cookies, layers of a cake being assembled. This is content that stops the scroll. Instagram is built for exactly this.</p>

<h2>Setting Up Your Profile</h2>
<ul>
<li><strong>Business account:</strong> Switch to a business profile for analytics and contact buttons</li>
<li><strong>Clear bio:</strong> What you sell, where you're located, how to order. Include a link to your menu/order page.</li>
<li><strong>Consistent name:</strong> Use your bakery name, not your personal name</li>
<li><strong>Profile photo:</strong> Your logo or a signature product photo</li>
</ul>

<h2>Content That Converts</h2>

<h3>The 80/20 Rule</h3>
<p>80% value/entertainment, 20% selling. Nobody follows a feed that's all "ORDER NOW!" Post content people actually want to see:</p>

<h3>Content Ideas</h3>
<ul>
<li><strong>Process shots:</strong> Dough being shaped, icing being piped, bread scoring. People love watching the craft.</li>
<li><strong>Before/after:</strong> Raw dough → finished product. Oddly satisfying.</li>
<li><strong>Behind the scenes:</strong> Your kitchen setup, ingredient sourcing, early morning prep. Builds connection.</li>
<li><strong>Customer reactions:</strong> Repost stories of people enjoying your products (with permission).</li>
<li><strong>Flat lays:</strong> Styled product photos from above. Clean, professional, shareable.</li>
<li><strong>Seasonal specials:</strong> Holiday cookies, fall-themed items. Create urgency.</li>
</ul>

<h2>Photo Tips</h2>
<ul>
<li><strong>Natural light only:</strong> Shoot near a window. No flash. No overhead fluorescents.</li>
<li><strong>Clean backgrounds:</strong> Marble counters, wooden boards, clean white surfaces.</li>
<li><strong>Shoot from above or 45°:</strong> These angles work best for food.</li>
<li><strong>Edit consistently:</strong> Use the same filter/preset so your feed looks cohesive.</li>
</ul>

<h2>Hashtags That Work</h2>
<p>Use a mix of sizes:</p>
<ul>
<li><strong>Large (1M+):</strong> #homebaker #bakingfromscratch #homemade</li>
<li><strong>Medium (100K–1M):</strong> #cottagefoodbaker #homebakery #smallbatchbaking</li>
<li><strong>Small/local (under 100K):</strong> #[yourcity]baker #[yourstate]cottagefood #[yourcity]foodie</li>
</ul>
<p>Use 15–25 hashtags per post. Save sets as templates so you can rotate them.</p>

<h2>Converting Followers to Customers</h2>
<p>The goal isn't followers; it's orders. Make it dead simple to order:</p>
<ul>
<li>Link to your order page in bio</li>
<li>Use "Link in bio" in captions when showing products</li>
<li>Add order page link to Stories with the link sticker</li>
<li>Respond to DMs quickly, but redirect to your order system (DM orders get messy)</li>
</ul>
<p>Having a proper online storefront (like the one KneadIt provides) makes you look professional and eliminates the back-and-forth of DM ordering.</p>
HTML,
            ],
            [
                'title' => 'Cottage Food Tax Guide: What Home Bakers Need to Know',
                'slug' => 'cottage-food-tax-guide-home-bakers',
                'category' => 'laws',
                'meta_title' => 'Cottage Food Tax Guide: Taxes for Home Bakers Explained | KneadIt',
                'meta_description' => 'Yes, cottage food income is taxable. Learn about deductions, Schedule C, sales tax, quarterly payments, and how to keep the IRS happy as a home baker.',
                'excerpt' => 'Cottage food income IS taxable, but the deductions can be significant. Here\'s what you need to know at tax time.',
                'body' => <<<'HTML'
<h2>Is Cottage Food Income Taxable?</h2>
<p><strong>Yes.</strong> Income from selling baked goods is self-employment income, even if it's a side hustle. The IRS considers you a sole proprietor unless you've formed an LLC or corporation.</p>

<h2>What You Owe</h2>
<ul>
<li><strong>Federal income tax:</strong> Your cottage food profit is added to your other income and taxed at your marginal rate.</li>
<li><strong>Self-employment tax:</strong> 15.3% on net earnings (Social Security + Medicare). This is on top of income tax.</li>
<li><strong>State income tax:</strong> If your state has one.</li>
<li><strong>Sales tax:</strong> Depends on your state and what you sell. Many states exempt cottage food from sales tax. Check yours.</li>
</ul>

<h2>Deductions That Save You Money</h2>
<p>The good news: you can deduct legitimate business expenses. Common deductions for home bakers:</p>

<h3>Cost of Goods Sold (COGS)</h3>
<ul>
<li>Flour, sugar, butter, eggs, and all other ingredients</li>
<li>Packaging materials (boxes, bags, labels, ribbon)</li>
<li>Baking supplies consumed (parchment paper, piping bags)</li>
</ul>

<h3>Business Expenses</h3>
<ul>
<li><strong>Equipment:</strong> Mixers, ovens, pans, tools (depreciated or Section 179)</li>
<li><strong>Marketing:</strong> Business cards, website costs, social media tools</li>
<li><strong>Farmers market fees:</strong> Booth rental, event fees</li>
<li><strong>Delivery costs:</strong> Gas mileage (67¢/mile in 2026), delivery bags</li>
<li><strong>Software:</strong> Order management, accounting, website hosting</li>
<li><strong>Insurance:</strong> If you have product liability insurance</li>
<li><strong>Education:</strong> Baking classes, food safety courses, business courses</li>
</ul>

<h3>Home Office (Limited)</h3>
<p>Note: Since cottage food is made in your home <em>kitchen</em> (not a dedicated office), the home office deduction is tricky. You generally can't deduct a portion of your kitchen since it's not used "exclusively" for business. Consult a tax professional.</p>

<h2>Quarterly Estimated Taxes</h2>
<p>If you expect to owe more than $1,000 in taxes for the year, you should make quarterly estimated payments to avoid penalties. Deadlines: April 15, June 15, September 15, January 15.</p>

<h2>Record Keeping</h2>
<p>Keep receipts for everything. Track every dollar in and every dollar out. The IRS requires records for at least 3 years. Use a system. A shoebox of receipts won't cut it when you're doing 50+ orders a month.</p>
<p>KneadIt's finance tracking categorizes expenses using IRS Schedule C categories automatically, so tax time is just pulling a report instead of digging through bank statements.</p>
HTML,
            ],
            [
                'title' => 'How to Sell Cookies from Home: A Complete Guide for 2026',
                'slug' => 'how-to-sell-cookies-from-home',
                'category' => 'guides',
                'meta_title' => 'How to Sell Cookies from Home: Start Your Cookie Business | KneadIt',
                'meta_description' => 'Learn how to sell cookies from home legally and profitably. Covers cottage food laws, best cookies to sell, pricing, packaging, and finding customers.',
                'excerpt' => 'Cookies are the perfect entry point for a home baking business. Here\'s everything you need to know to start selling cookies from your kitchen.',
                'body' => <<<'HTML'
<h2>Why Cookies Are the Perfect Home Bakery Product</h2>
<p>If you're looking to start a home baking business, cookies are hands-down the best place to begin. They're relatively quick to make, easy to package, travel well, and everyone loves them. Plus, the profit margins can be surprisingly good once you nail your pricing.</p>
<p>Whether you want a weekend side hustle or a full-time gig, selling cookies from home is one of the lowest-barrier ways to start making money with your baking skills.</p>

<h2>Step 1: Check Your State's Cottage Food Laws</h2>
<p>Before you bake a single batch, look up your state's cottage food law. The good news: cookies are allowed in virtually every state. But you still need to know:</p>
<ul>
<li><strong>Your annual revenue cap:</strong> ranges from $25,000 to $250,000+ depending on your state</li>
<li><strong>Where you can sell:</strong> some states allow online orders and delivery, others restrict you to farmers markets and in-person sales</li>
<li><strong>Labeling requirements:</strong> almost every state requires labels with ingredients, allergens, and a "made in a home kitchen" disclaimer</li>
<li><strong>Whether you need to register:</strong> some states require a simple (usually free) registration</li>
</ul>
<p>Search "[your state] cottage food law" to find the official guidelines. It takes 15 minutes and saves you headaches later.</p>

<h2>Step 2: Choose Your Cookie Lineup</h2>
<p>Don't try to sell every cookie recipe you know. Start with 3–5 varieties that you can make consistently and that have broad appeal. The best sellers for home cookie businesses are:</p>
<ul>
<li><strong>Chocolate chip cookies:</strong> the classic never fails</li>
<li><strong>Custom decorated sugar cookies:</strong> high-margin, perfect for events and holidays</li>
<li><strong>Specialty cookies:</strong> think stuffed cookies, crumbl-style cookies, or unique flavor combos that set you apart</li>
<li><strong>Cookie boxes/assortments:</strong> great for gifting, higher average order value</li>
</ul>

<h3>Think About Scalability</h3>
<p>Decorated sugar cookies look amazing on Instagram but take hours per dozen. If you want volume, include some "batch-friendly" cookies that you can crank out quickly alongside your premium options.</p>

<h2>Step 3: Price for Profit (Not Charity)</h2>
<p>This is where most home bakers stumble. Your cookies are not grocery store cookies, so don't price them like they are. A solid pricing formula:</p>
<p><strong>(Ingredient cost × 3) + labor + packaging = your price</strong></p>
<p>For regular cookies, expect to charge $3–5 per cookie or $24–48 per dozen. Custom decorated sugar cookies command $5–8 each or $60–96 per dozen. These are normal market prices, so don't feel guilty about them.</p>

<h2>Step 4: Get Your Packaging Right</h2>
<p>Packaging matters more than you think. It protects your product, makes it look professional, and is required by law to include certain information. At minimum you need:</p>
<ul>
<li>Food-safe bags, boxes, or containers</li>
<li>A label with your business name, ingredients, allergens, weight, and home kitchen disclaimer</li>
<li>Tissue paper or parchment to separate layers</li>
</ul>
<p>Your packaging is your brand's first impression. Clear cellophane bags with a nice sticker label look clean and professional without breaking the bank.</p>

<h2>Step 5: Find Your Customers</h2>
<p>You don't need a marketing degree. Start with what's free and easy:</p>
<ul>
<li><strong>Instagram and Facebook:</strong> post mouth-watering photos of your cookies. Consistency beats perfection.</li>
<li><strong>Friends and family:</strong> tell everyone you know. Word of mouth is the #1 driver for home bakers.</li>
<li><strong>Local Facebook groups:</strong> many communities have buy/sell groups or foodie groups where you can promote.</li>
<li><strong>Farmers markets:</strong> great for building a customer base and getting real-time feedback.</li>
<li><strong>Holiday and event seasons:</strong> Valentine's Day, Christmas, graduation parties, baby showers. Cookies are perfect for all of them.</li>
</ul>

<h2>Step 6: Set Up Systems Early</h2>
<p>It's tempting to manage everything through DMs and notes on your phone. That works for your first five orders. By order twenty, you'll be losing track of who ordered what, when it's due, and whether they paid.</p>
<p>Set up a simple order tracking system from day one. Track your orders, costs, and revenue so you know what's actually making you money. Tools like <a href="https://getkneadit.app">KneadIt</a> are built specifically for this: managing orders, tracking costs, and giving your customers a professional way to browse and order.</p>

<h2>Start This Weekend</h2>
<p>Seriously, you don't need to overthink this. Check your state law, pick your top 3 cookies, price them properly, post on Instagram, and take your first order. You can refine everything else as you go. The hardest part is starting, and cookies make it easy.</p>
HTML,
            ],
            [
                'title' => 'Cottage Food Packaging Ideas and Requirements You Need to Know',
                'slug' => 'cottage-food-packaging-ideas-requirements',
                'category' => 'tips',
                'meta_title' => 'Cottage Food Packaging Ideas & Requirements (2026) | KneadIt',
                'meta_description' => 'Packaging ideas and legal requirements for cottage food bakers. Learn what your labels must include and how to package baked goods that look professional.',
                'excerpt' => 'Great packaging protects your product, builds your brand, and keeps you legal. Here\'s how to nail all three without overcomplicating it.',
                'body' => <<<'HTML'
<h2>Packaging Is More Than a Box</h2>
<p>When a customer picks up their order, your packaging is the first thing they see and touch. It tells them whether you're a serious business or someone winging it. Good packaging does three things: protects the product, meets legal requirements, and makes people want to buy from you again.</p>
<p>Let's break down the requirements first, then get into the fun stuff: the ideas.</p>

<h2>What the Law Requires on Your Packaging</h2>
<p>Cottage food laws in nearly every state require specific information on your labels. Missing any of these can get you fined or shut down, so take this seriously:</p>

<h3>Required Label Elements (Most States)</h3>
<ul>
<li><strong>Product name:</strong> "Double Chocolate Brownies," not just "brownies"</li>
<li><strong>Ingredients list:</strong> in descending order by weight, just like store-bought products</li>
<li><strong>Allergen statement:</strong> must call out the Big 9: milk, eggs, wheat, soy, peanuts, tree nuts, fish, shellfish, sesame</li>
<li><strong>Net weight or quantity:</strong> "12 oz" or "6 cookies"</li>
<li><strong>Your name and home address:</strong> PO boxes usually don't count</li>
<li><strong>"Made in a home kitchen" disclaimer:</strong> exact wording varies by state, so look up yours</li>
<li><strong>Date:</strong> production date or best-by date, depending on your state</li>
</ul>
<p>Some states have additional requirements like a registration number or specific font size minimums. Always check your state's Department of Agriculture website for the exact language.</p>

<h2>Packaging Ideas by Product Type</h2>

<h3>Cookies</h3>
<ul>
<li><strong>Clear cellophane bags with a heat seal:</strong> classic, affordable, and lets the product sell itself visually</li>
<li><strong>Kraft paper boxes with a window:</strong> more premium feel, great for gift sets and decorated cookies</li>
<li><strong>Individually wrapped:</strong> perfect for farmers markets where people want to grab one or two</li>
</ul>

<h3>Bread and Loaf Cakes</h3>
<ul>
<li><strong>Paper bread bags:</strong> the bakery-style look people love, with a label sticker to seal</li>
<li><strong>Kraft paper wrap + twine:</strong> rustic, Instagram-worthy, and inexpensive</li>
<li><strong>Clear poly bags:</strong> practical for seeing the product, less aesthetic</li>
</ul>

<h3>Cakes</h3>
<ul>
<li><strong>Sturdy cake boxes:</strong> non-negotiable. Get boxes that fit your standard sizes (8", 10", etc.)</li>
<li><strong>Non-slip shelf liner on the bottom:</strong> prevents sliding during transport</li>
<li><strong>Cake boards:</strong> always use a board that's 2" larger than the cake</li>
</ul>

<h3>Bars, Brownies, and Small Items</h3>
<ul>
<li><strong>Glassine bags:</strong> grease-resistant, translucent, professional-looking</li>
<li><strong>Small kraft boxes:</strong> great for brownie assortments or sampler packs</li>
<li><strong>Wax paper wraps:</strong> simple, clean, and eco-friendly</li>
</ul>

<h2>Labeling Tips That Look Professional</h2>
<p>Your label is your brand. Here's how to make it look legit without spending a fortune:</p>
<ul>
<li><strong>Use Canva</strong> to design your label. Free templates get you 80% of the way there</li>
<li><strong>Print on Avery labels</strong> at home to start. Upgrade to Sticker Mule or a local printer when volume justifies it.</li>
<li><strong>Pick one or two fonts and stick with them:</strong> consistency looks professional, randomness looks amateur</li>
<li><strong>Include your Instagram handle or website:</strong> turns every package into a marketing tool</li>
<li><strong>Use waterproof labels</strong> if your products create any condensation</li>
</ul>

<h2>Where to Buy Packaging Supplies</h2>
<p>You don't need to spend a fortune. Here are reliable, affordable sources:</p>
<ul>
<li><strong>Amazon:</strong> widest selection, fast shipping, competitive prices on bulk orders</li>
<li><strong>WebstaurantStore:</strong> commercial-grade supplies at wholesale prices</li>
<li><strong>Nashville Wraps:</strong> beautiful packaging specifically for food businesses</li>
<li><strong>Dollar Tree:</strong> surprisingly good for basic cellophane bags and tissue paper when starting out</li>
</ul>

<h2>Don't Forget the Cost</h2>
<p>Packaging adds up fast. A nice box, label, tissue paper, and ribbon can cost $2–4 per order. Make sure you're including packaging costs in your pricing formula, not eating it as a hidden expense. Track your packaging costs per product so you know exactly what each order costs you. <a href="https://getkneadit.app">KneadIt</a> lets you include packaging in your recipe cost calculations so nothing gets overlooked.</p>
HTML,
            ],
            [
                'title' => 'How to Price Custom Cakes for Profit (Without Scaring Off Customers)',
                'slug' => 'how-to-price-custom-cakes-profit',
                'category' => 'tips',
                'meta_title' => 'How to Price Custom Cakes for Profit: Cake Pricing Guide | KneadIt',
                'meta_description' => 'Learn how to price custom cakes that cover your costs and pay you fairly. Includes pricing formulas, tier-based examples, and common mistakes to avoid.',
                'excerpt' => 'Custom cakes are high-effort, high-reward, but only if you price them right. Here\'s how to calculate prices that are fair to you AND your customers.',
                'body' => <<<'HTML'
<h2>The Custom Cake Pricing Problem</h2>
<p>Custom cakes are one of the most profitable products a home baker can offer, but they're also the easiest to underprice. Unlike cookies or bread where you make a batch and sell multiples, every custom cake is a one-off project with unique design requirements, consultations, and hours of hands-on work.</p>
<p>If you're charging $50 for a cake that took 6 hours to make, you're paying yourself less than minimum wage after costs. Let's fix that.</p>

<h2>The Pricing Formula</h2>
<p>Here's a straightforward formula that works for custom cakes:</p>
<p><strong>Price = Ingredients + Packaging + Labor + Overhead + Profit Margin</strong></p>

<h3>Breaking It Down</h3>
<ul>
<li><strong>Ingredients:</strong> cost of every ingredient in the cake, filling, and frosting/fondant. Weigh and measure precisely.</li>
<li><strong>Packaging:</strong> cake box, cake board, dowels, any delivery supplies</li>
<li><strong>Labor:</strong> every hour you spend: baking, cooling, leveling, filling, crumb coating, decorating, cleaning up, and any consultation time. Use $25–40/hr depending on your experience and market.</li>
<li><strong>Overhead:</strong> electricity, gas, equipment wear, and the cost of running your business. A simple approach: add 15–20% to your subtotal.</li>
<li><strong>Profit margin:</strong> add 10–20% on top. This isn't your labor pay; it's business profit that funds growth, covers slow months, and rewards the risk you take.</li>
</ul>

<h2>Example: A Two-Tier Custom Cake</h2>
<p>Let's price a two-tier buttercream cake with custom decorations (a popular order for birthdays and showers):</p>
<ul>
<li>Ingredients (cake, filling, buttercream): $22</li>
<li>Packaging (box, board, dowels): $8</li>
<li>Labor: 5 hours × your hourly rate (set this based on your experience level and market)</li>
<li>Subtotal: $180</li>
<li>Overhead (18%): $32</li>
<li>Profit (15%): $32</li>
<li><strong>Price: $244</strong></li>
</ul>
<p>Does $244 feel high? It shouldn't. Custom two-tier cakes regularly sell for $200–400+ depending on your market and the complexity of the design. You're offering a handmade, personalized product, not a sheet cake from Costco.</p>

<h2>Pricing by Serving Size</h2>
<p>Many bakers price per serving as a quick reference. Here are common ranges for custom cakes:</p>
<ul>
<li><strong>Simple buttercream:</strong> $4–6 per serving</li>
<li><strong>Detailed buttercream:</strong> $6–9 per serving</li>
<li><strong>Fondant-covered:</strong> $8–12 per serving</li>
<li><strong>Sculpted or elaborate designs:</strong> $10–15+ per serving</li>
</ul>
<p>A typical 8" round cake serves 15–20 people, so even the "simple" tier puts you at $60–120 for a single-tier cake. That's a reasonable starting point.</p>

<h2>Common Pricing Mistakes</h2>

<h3>1. Forgetting Consultation Time</h3>
<p>Emails, texts, phone calls, Pinterest board reviews, design sketches. All of this is work. If you spend 45 minutes going back and forth with a customer before they even order, that time needs to be reflected in your price.</p>

<h3>2. Not Charging for Complexity</h3>
<p>A cake with hand-painted flowers takes three times longer than a smooth buttercream finish. Your pricing should reflect the difference. Create pricing tiers based on decoration complexity.</p>

<h3>3. Matching Grocery Store Prices</h3>
<p>Stop comparing yourself to Walmart. They use industrial equipment, pre-made mixes, and minimum-wage labor. You are a skilled artisan creating something custom and made with care. Different product, different price point.</p>

<h3>4. Offering Too Many Free Extras</h3>
<p>Tastings, delivery, cake toppers, extra servings "just in case." If you're giving these away, you're cutting into your profit. Decide what's included and what costs extra. Be upfront about it.</p>

<h2>How to Communicate Your Prices</h2>
<p>Confidence matters. Don't apologize for your prices or over-explain. State them clearly:</p>
<ul>
<li>Post a starting price on your website or social media: "Custom cakes start at $XX"</li>
<li>Provide a detailed quote for each custom order so the customer sees what they're paying for</li>
<li>Require a 50% deposit to secure the order date</li>
</ul>
<p>Customers who balk at fair pricing aren't your customers. The ones who value quality and craftsmanship will happily pay.</p>

<h2>Track Your Actual Costs</h2>
<p>The only way to know if your pricing works is to track real numbers, not estimates. After each cake, log what you actually spent on ingredients and how many hours it really took. You'll be surprised how often your estimates are too low. <a href="https://getkneadit.app">KneadIt</a> makes this easy with built-in recipe costing and order tracking, so you can see your true profit on every cake you make.</p>
HTML,
            ],
        ];

        // Space posts 3 days apart, newest first
        $totalPosts = count($posts);
        foreach ($posts as $index => $post) {
            BlogPost::updateOrCreate(
                ['slug' => $post['slug']],
                array_merge($post, [
                    'is_published' => true,
                    'published_at' => now()->subDays($index * 3),
                ])
            );
        }
    }
}
