<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class HelpCenter extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationLabel = 'Help';
    protected static string|\UnitEnum|null $navigationGroup = null;
    protected static ?int $navigationSort = 100;
    protected string $view = 'filament.pages.help-center';

    public function getTopics(): array
    {
        return [
            [
                'title' => 'Getting Started',
                'icon' => 'heroicon-o-rocket-launch',
                'articles' => [
                    [
                        'title' => 'Setting Up Your Store',
                        'content' => 'Welcome to KneadIt! To get started, head to <strong>Settings</strong> in the sidebar. Fill in your bakery name, address, contact info, and business hours. Upload your logo — it\'ll appear on your storefront, invoices, and labels. Set your timezone and currency preferences. Once saved, your storefront is live and ready to accept orders.',
                    ],
                    [
                        'title' => 'Adding Your First Products',
                        'content' => 'Navigate to <strong>Products</strong> in the sidebar. Click "New Product" and fill in the name, description, price, and category. Upload a mouth-watering photo — this is what customers see first! Set a cost price to track your margins. You can mark products as "Featured" to highlight them on your storefront. Use categories to organize your menu (Breads, Pastries, Cakes, etc.).',
                    ],
                    [
                        'title' => 'Configuring Delivery',
                        'content' => 'Go to <strong>Settings → Delivery</strong> to set up your delivery options. Define your delivery radius, minimum order amount, and delivery fee. You can offer free delivery above a certain order total. Set available delivery time slots and blackout dates for holidays. Customers will see these options at checkout.',
                    ],
                ],
            ],
            [
                'title' => 'Managing Orders',
                'icon' => 'heroicon-o-clipboard-document-list',
                'articles' => [
                    [
                        'title' => 'Order Workflow',
                        'content' => 'Orders flow through these statuses: <strong>Pending → Confirmed → In Progress → Ready → Delivered/Picked Up</strong>. When a new order comes in, review it and confirm. Move it to "In Progress" when you start baking. Mark as "Ready" when it\'s done. Finally, mark as "Delivered" or "Picked Up" to complete the order. You can also cancel orders if needed.',
                    ],
                    [
                        'title' => 'Changing Order Status',
                        'content' => 'From the Orders list, click on any order to view details. Use the status dropdown or action buttons to move the order to the next stage. You can add internal notes at any stage. Customers receive email notifications when their order status changes (if email is configured).',
                    ],
                    [
                        'title' => 'Using Quick Order',
                        'content' => 'The <strong>Quick Order</strong> page lets you create orders on the fly — perfect for walk-ins, phone orders, or farmers market sales. Select a customer (or create one), add products, apply any discounts, and submit. It\'s designed to be fast: keyboard shortcuts, recent customers, and popular products are all at your fingertips.',
                    ],
                ],
            ],
            [
                'title' => 'Storefront',
                'icon' => 'heroicon-o-building-storefront',
                'articles' => [
                    [
                        'title' => 'Customizing Your Theme',
                        'content' => 'Visit <strong>Theme Selector</strong> to choose your storefront\'s look and feel. Pick from pre-built themes or customize colors to match your brand. Your theme affects your public ordering page, which customers see when they visit your KneadIt URL.',
                    ],
                    [
                        'title' => 'Announcement Banners',
                        'content' => 'Use <strong>Announcement Banner</strong> to display important messages on your storefront. Great for holiday hours, special promotions, or temporary closures. Set a background color, add your message, and toggle it on/off. You can schedule banners to appear and disappear automatically.',
                    ],
                    [
                        'title' => 'SEO & Custom Domain',
                        'content' => 'Improve your search visibility with SEO settings. Add a meta description, keywords, and social sharing image. For a professional touch, connect your own domain (e.g., order.mybakery.com) via the <strong>Custom Domain</strong> page.',
                    ],
                ],
            ],
            [
                'title' => 'Finances',
                'icon' => 'heroicon-o-banknotes',
                'articles' => [
                    [
                        'title' => 'Tracking Expenses',
                        'content' => 'Record every business expense under <strong>Expenses</strong>. Categorize them (Ingredients, Packaging, Equipment, etc.) and set the business-use percentage for accurate tax deductions. Upload receipt photos to keep everything organized. KneadIt automatically calculates your deductible amounts.',
                    ],
                    [
                        'title' => 'Recording Income',
                        'content' => 'Beyond order revenue, track additional income sources under <strong>Income</strong> — like catering deposits, teaching classes, or wholesale accounts. This gives you a complete financial picture in your Finance Summary.',
                    ],
                    [
                        'title' => 'Tax Exports',
                        'content' => 'Come tax time, head to <strong>Tax Export</strong> to generate a summary of your revenue, expenses, and deductions for any year. Export as CSV for your accountant. The report includes categorized expenses with deductible amounts already calculated.',
                    ],
                ],
            ],
            [
                'title' => 'Marketing',
                'icon' => 'heroicon-o-megaphone',
                'articles' => [
                    [
                        'title' => 'Email Campaigns',
                        'content' => 'Create and send email campaigns to your customers from <strong>Email Campaigns</strong>. Use it for weekly specials, holiday pre-orders, or loyalty rewards. Write your message, select recipients (all customers, or filter by criteria), preview, and send. Track open rates to see what works.',
                    ],
                    [
                        'title' => 'Social Media Posts',
                        'content' => 'Plan your social content with the <strong>Social Calendar</strong>. Draft posts, schedule them, and use the Instagram Caption Generator for engaging captions. Keep your bakery top-of-mind with consistent posting.',
                    ],
                    [
                        'title' => 'Loyalty Programs',
                        'content' => 'Reward your regulars with the <strong>Loyalty Dashboard</strong>. Set up point-based rewards: customers earn points per dollar spent and redeem them for discounts or free items. Track who your most loyal customers are and send them special offers.',
                    ],
                ],
            ],
            [
                'title' => 'Tools',
                'icon' => 'heroicon-o-wrench-screwdriver',
                'articles' => [
                    [
                        'title' => 'Baking Sheet',
                        'content' => 'The <strong>Baking Sheet</strong> is your daily production plan. It aggregates all orders for a given date and shows exactly what you need to bake, broken down by product. Print it out and hang it in your kitchen. No more missed orders!',
                    ],
                    [
                        'title' => 'Weekly Prep Planner',
                        'content' => 'Plan your week with the <strong>Prep Planner</strong>. See upcoming orders, batch similar items together, and schedule your prep work. It helps you work efficiently and avoid last-minute rushes.',
                    ],
                    [
                        'title' => 'Label Generator',
                        'content' => 'Create professional product labels with the <strong>Label Generator</strong>. Include product name, ingredients, allergens, weight, and your bakery info. Print on standard label sheets. Required for farmers markets and retail sales in most states.',
                    ],
                ],
            ],
            [
                'title' => 'Billing',
                'icon' => 'heroicon-o-credit-card',
                'articles' => [
                    [
                        'title' => 'Plans & Pricing',
                        'content' => 'KneadIt offers tiered plans: <strong>Starter</strong> (free, basic features), <strong>Growth</strong> (full toolkit for growing bakeries), and <strong>Pro</strong> (everything, unlimited). Compare features on the <strong>Upgrade Plan</strong> page. Your current plan is shown in the sidebar.',
                    ],
                    [
                        'title' => 'Upgrading Your Plan',
                        'content' => 'Ready for more? Visit <strong>Upgrade Plan</strong> to see what you\'re missing and upgrade instantly. Billing is handled through Stripe — secure and simple. You can upgrade or downgrade at any time; changes take effect immediately.',
                    ],
                    [
                        'title' => 'Referral Program',
                        'content' => 'Love KneadIt? Share your referral link from the <strong>Referral Program</strong> page. When another baker signs up and subscribes using your link, you both get a discount. Track your referrals and rewards right from the dashboard.',
                    ],
                ],
            ],
            [
                'title' => 'FAQ',
                'icon' => 'heroicon-o-chat-bubble-left-right',
                'articles' => [
                    [
                        'title' => 'Can I use KneadIt for a food truck or catering business?',
                        'content' => 'Absolutely! While KneadIt is designed with bakeries in mind, its order management, customer tracking, and financial tools work great for any small food business. Customize your product categories and you\'re all set.',
                    ],
                    [
                        'title' => 'How do I handle custom cake orders?',
                        'content' => 'Use the order notes field for custom requests. You can also create a "Custom Cake" product with a base price and adjust the total manually. For complex orders, the Catering Inquiries feature lets customers submit detailed requests that you can quote.',
                    ],
                    [
                        'title' => 'Is my data safe?',
                        'content' => 'Yes! KneadIt uses industry-standard encryption, secure hosting, and regular backups. Your data is yours — we never share it with third parties. You can export all your data at any time.',
                    ],
                    [
                        'title' => 'Can multiple people use my account?',
                        'content' => 'Yes! With the <strong>Staff Management</strong> feature (Growth plan and above), you can add team members with different roles: Admin (full access), Manager (most features), or Staff (order management only).',
                    ],
                    [
                        'title' => 'How do I get help?',
                        'content' => 'You\'re here! Browse our help articles, or reach out via the Contact Support link below. We typically respond within 24 hours on business days.',
                    ],
                ],
            ],
        ];
    }
}
