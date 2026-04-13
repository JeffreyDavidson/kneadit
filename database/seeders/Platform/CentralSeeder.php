<?php

namespace Database\Seeders\Platform;

use App\Enums\Platform\SubscriptionTier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CentralSeeder extends Seeder
{
    public function run(): void
    {
        $db = DB::connection('central');

        $db->beginTransaction();

        try {
            // Clear seeded data from previous runs
            $this->truncateSeededTables($db);

            $this->seedTenants($db);
            $this->seedAnnouncements($db);
            $this->seedSupportTickets($db);
            $this->seedPlatformMessages($db);
            $this->seedEmailCampaigns($db);
            $this->seedPlatformActivities($db);
            $this->seedFeatureUsageLogs($db);
            $this->seedAdminAuditLogs($db);
            $this->seedTenantNotes($db);
            $this->seedPlatformSettings($db);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        $this->command?->info('Central admin tables seeded with demo data.');
    }

    private function truncateSeededTables($db): void
    {
        $tables = [
            'platform_announcements', 'support_replies', 'support_tickets',
            'platform_messages', 'email_campaign_logs', 'email_campaigns',
            'platform_activities', 'feature_usage_logs', 'admin_audit_logs',
            'tenant_notes', 'platform_settings', 'impersonation_tokens',
        ];

        foreach ($tables as $table) {
            try {
                $db->table($table)->truncate();
            } catch (\Throwable) {
                // Table may not exist yet
            }
        }
    }

    // ──────────────────────────────────────────────
    // 1. Tenants
    // ──────────────────────────────────────────────
    private function seedTenants($db): void
    {
        $now = now();

        // Clear existing demo tenants + domains
        $demoIds = ['sweet-surrender', 'rolling-pin', 'flour-and-fancy', 'cake-boss', 'sugar-rush'];
        $db->table('domains')->whereIn('tenant_id', $demoIds)->delete();
        $db->table('tenants')->whereIn('id', $demoIds)->delete();

        $db->table('tenants')->insert([
            [
                'id' => 'sweet-surrender',
                'name' => 'Maria Lopez',
                'email' => 'maria@sweetsurrender.com',
                'plan' => SubscriptionTier::Starter->value,
                'trial_ends_at' => $now->copy()->addDays(10)->toDateTimeString(),
                'store_name' => 'Sweet Surrender Bakery',
                'store_logo' => null,
                'brand_color_primary' => '#e84393',
                'brand_color_secondary' => '#2d3436',
                'storefront_enabled' => true,
                'external_website' => null,
                'is_active' => true,
                'created_at' => $now->copy()->subDays(5)->toDateTimeString(),
                'updated_at' => $now->copy()->subDays(5)->toDateTimeString(),
                'data' => null,
            ],
            [
                'id' => 'rolling-pin',
                'name' => 'James Chen',
                'email' => 'james@therollingpin.com',
                'plan' => SubscriptionTier::Growth->value,
                'trial_ends_at' => $now->copy()->subDays(15)->toDateTimeString(),
                'store_name' => 'The Rolling Pin',
                'store_logo' => null,
                'brand_color_primary' => '#00b894',
                'brand_color_secondary' => '#1e272e',
                'storefront_enabled' => true,
                'external_website' => null,
                'is_active' => true,
                'created_at' => $now->copy()->subDays(25)->toDateTimeString(),
                'updated_at' => $now->copy()->subDays(3)->toDateTimeString(),
                'data' => null,
            ],
            [
                'id' => 'flour-and-fancy',
                'name' => 'Sophie Williams',
                'email' => 'sophie@flourandfancy.com',
                'plan' => SubscriptionTier::Starter->value,
                'trial_ends_at' => $now->copy()->subDays(3)->toDateTimeString(),
                'store_name' => 'Flour & Fancy',
                'store_logo' => null,
                'brand_color_primary' => '#6c5ce7',
                'brand_color_secondary' => '#222222',
                'storefront_enabled' => false,
                'external_website' => null,
                'is_active' => true,
                'created_at' => $now->copy()->subDays(20)->toDateTimeString(),
                'updated_at' => $now->copy()->subDays(1)->toDateTimeString(),
                'data' => null,
            ],
            [
                'id' => 'cake-boss',
                'name' => 'Anthony Rossi',
                'email' => 'anthony@cakebosskitchen.com',
                'plan' => SubscriptionTier::Growth->value,
                'trial_ends_at' => null,
                'store_name' => 'Cake Boss Kitchen',
                'store_logo' => null,
                'brand_color_primary' => '#fdcb6e',
                'brand_color_secondary' => '#2d3436',
                'storefront_enabled' => true,
                'external_website' => 'https://cakebosskitchen.com',
                'is_active' => true,
                'created_at' => $now->copy()->subDays(45)->toDateTimeString(),
                'updated_at' => $now->copy()->subDays(2)->toDateTimeString(),
                'data' => null,
            ],
            [
                'id' => 'sugar-rush',
                'name' => 'Priya Patel',
                'email' => 'priya@sugarrushsweets.com',
                'plan' => SubscriptionTier::Pro->value,
                'trial_ends_at' => null,
                'store_name' => 'Sugar Rush Sweets',
                'store_logo' => null,
                'brand_color_primary' => '#ff7675',
                'brand_color_secondary' => '#130f40',
                'storefront_enabled' => true,
                'external_website' => null,
                'is_active' => false,
                'created_at' => $now->copy()->subDays(60)->toDateTimeString(),
                'updated_at' => $now->copy()->subDays(7)->toDateTimeString(),
                'data' => null,
            ],
        ]);
    }

    // ──────────────────────────────────────────────
    // 2. Platform Announcements
    // ──────────────────────────────────────────────
    private function seedAnnouncements($db): void
    {
        $now = now();

        $db->table('platform_announcements')->insert([
            [
                'title' => 'New Feature: Recipe Cost Calculator',
                'body' => 'We\'re excited to announce the Recipe Cost Calculator! Track ingredient costs, calculate per-unit pricing, and optimize your margins — all from the Recipes tab.',
                'type' => 'info',
                'target_plans' => json_encode(['starter', 'growth', 'pro']),
                'is_active' => true,
                'starts_at' => $now->copy()->subDays(2)->toDateTimeString(),
                'ends_at' => $now->copy()->addDays(30)->toDateTimeString(),
                'is_dismissable' => true,
                'created_at' => $now->copy()->subDays(2)->toDateTimeString(),
                'updated_at' => $now->copy()->subDays(2)->toDateTimeString(),
            ],
            [
                'title' => 'Scheduled Maintenance March 15',
                'body' => 'We will be performing scheduled maintenance on March 15 from 2:00 AM to 4:00 AM UTC. The platform may be briefly unavailable during this time.',
                'type' => 'warning',
                'target_plans' => json_encode(['starter', 'growth', 'pro']),
                'is_active' => true,
                'starts_at' => $now->copy()->subDay()->toDateTimeString(),
                'ends_at' => $now->copy()->addDays(5)->toDateTimeString(),
                'is_dismissable' => true,
                'created_at' => $now->copy()->subDay()->toDateTimeString(),
                'updated_at' => $now->copy()->subDay()->toDateTimeString(),
            ],
            [
                'title' => 'Welcome to KneadIt!',
                'body' => 'Welcome to the KneadIt platform! We\'re thrilled to have you on board. Explore the dashboard to get started with your bakery management.',
                'type' => 'info',
                'target_plans' => json_encode(['starter', 'growth', 'pro']),
                'is_active' => false,
                'starts_at' => $now->copy()->subDays(90)->toDateTimeString(),
                'ends_at' => $now->copy()->subDays(60)->toDateTimeString(),
                'is_dismissable' => true,
                'created_at' => $now->copy()->subDays(90)->toDateTimeString(),
                'updated_at' => $now->copy()->subDays(60)->toDateTimeString(),
            ],
        ]);
    }

    // ──────────────────────────────────────────────
    // 3. Support Tickets & Replies
    // ──────────────────────────────────────────────
    private function seedSupportTickets($db): void
    {
        $now = now();

        $tickets = [
            [
                'tenant_id' => 'sweet-surrender',
                'subject' => "Can't upload logo",
                'body' => "I've tried uploading my bakery logo several times but it keeps failing. The file is a PNG, about 2MB. I've tried both Chrome and Firefox.",
                'status' => 'open',
                'priority' => 'high',
                'admin_notes' => null,
                'resolved_at' => null,
                'created_at' => $now->copy()->subDays(1)->toDateTimeString(),
                'updated_at' => $now->copy()->subHours(3)->toDateTimeString(),
            ],
            [
                'tenant_id' => 'rolling-pin',
                'subject' => 'How do I add delivery zones?',
                'body' => "Hi! I just upgraded to Growth and I'd like to set up delivery zones for my area. Where do I find this feature?",
                'status' => 'open',
                'priority' => 'normal',
                'admin_notes' => null,
                'resolved_at' => null,
                'created_at' => $now->copy()->subDays(2)->toDateTimeString(),
                'updated_at' => $now->copy()->subDays(1)->toDateTimeString(),
            ],
            [
                'tenant_id' => 'flour-and-fancy',
                'subject' => 'Payment not showing',
                'body' => "A customer paid via the storefront but the order doesn't show a payment. Order #1042. Can you help?",
                'status' => 'in_progress',
                'priority' => 'high',
                'admin_notes' => 'Checking Stripe logs for this transaction.',
                'resolved_at' => null,
                'created_at' => $now->copy()->subDays(3)->toDateTimeString(),
                'updated_at' => $now->copy()->subHours(6)->toDateTimeString(),
            ],
            [
                'tenant_id' => 'cake-boss',
                'subject' => 'Feature request: bulk product import',
                'body' => 'I have over 200 products and adding them one by one is painful. Any chance you could add a CSV import feature?',
                'status' => 'resolved',
                'priority' => 'normal',
                'admin_notes' => 'Added to roadmap, marked as resolved.',
                'resolved_at' => $now->copy()->subDays(2)->toDateTimeString(),
                'created_at' => $now->copy()->subDays(7)->toDateTimeString(),
                'updated_at' => $now->copy()->subDays(2)->toDateTimeString(),
            ],
            [
                'tenant_id' => 'sugar-rush',
                'subject' => 'Need help with coupons',
                'body' => "I created a 20% off coupon but customers are saying it doesn't work at checkout. The code is SWEET20.",
                'status' => 'closed',
                'priority' => 'normal',
                'admin_notes' => 'Coupon was set to inactive. Helped tenant reactivate it.',
                'resolved_at' => $now->copy()->subDays(10)->toDateTimeString(),
                'created_at' => $now->copy()->subDays(14)->toDateTimeString(),
                'updated_at' => $now->copy()->subDays(10)->toDateTimeString(),
            ],
        ];

        foreach ($tickets as $ticket) {
            $db->table('support_tickets')->insert($ticket);
        }

        // Get the ticket IDs (they're auto-incremented)
        $ticketIds = $db->table('support_tickets')->pluck('id', 'subject');

        $replies = [
            // Ticket 1: Can't upload logo
            ['ticket_id' => $ticketIds["Can't upload logo"], 'author_type' => 'admin', 'author_name' => 'Jeffrey Davidson', 'body' => 'Hi Maria! Sorry about that. Could you tell me the exact file dimensions? We currently support up to 5MB but the image must be under 2048x2048 pixels.', 'created_at' => $now->copy()->subHours(20)->toDateTimeString(), 'updated_at' => $now->copy()->subHours(20)->toDateTimeString()],
            ['ticket_id' => $ticketIds["Can't upload logo"], 'author_type' => 'tenant', 'author_name' => 'Maria Lopez', 'body' => "Oh! It's 4000x4000. Let me resize it and try again. Thank you!", 'created_at' => $now->copy()->subHours(18)->toDateTimeString(), 'updated_at' => $now->copy()->subHours(18)->toDateTimeString()],
            ['ticket_id' => $ticketIds["Can't upload logo"], 'author_type' => 'admin', 'author_name' => 'Jeffrey Davidson', 'body' => "That's likely the issue! Let me know if resizing fixes it. We're also working on automatic image optimization for a future release.", 'created_at' => $now->copy()->subHours(3)->toDateTimeString(), 'updated_at' => $now->copy()->subHours(3)->toDateTimeString()],

            // Ticket 2: Delivery zones
            ['ticket_id' => $ticketIds['How do I add delivery zones?'], 'author_type' => 'admin', 'author_name' => 'Jeffrey Davidson', 'body' => 'Hey James! Great question. Go to Settings → Delivery Zones. You can draw zones on the map or enter postcodes. Let me know if you need a walkthrough!', 'created_at' => $now->copy()->subDays(1)->subHours(5)->toDateTimeString(), 'updated_at' => $now->copy()->subDays(1)->subHours(5)->toDateTimeString()],
            ['ticket_id' => $ticketIds['How do I add delivery zones?'], 'author_type' => 'tenant', 'author_name' => 'James Chen', 'body' => 'Found it, thanks! One more thing — can I set different delivery fees per zone?', 'created_at' => $now->copy()->subDays(1)->toDateTimeString(), 'updated_at' => $now->copy()->subDays(1)->toDateTimeString()],

            // Ticket 3: Payment not showing
            ['ticket_id' => $ticketIds['Payment not showing'], 'author_type' => 'admin', 'author_name' => 'Jeffrey Davidson', 'body' => "Hi Sophie, I'm looking into this now. Can you confirm the approximate time of the order? I'll cross-reference with our payment processor.", 'created_at' => $now->copy()->subDays(2)->toDateTimeString(), 'updated_at' => $now->copy()->subDays(2)->toDateTimeString()],
            ['ticket_id' => $ticketIds['Payment not showing'], 'author_type' => 'tenant', 'author_name' => 'Sophie Williams', 'body' => 'It was around 3:15 PM yesterday. The customer said they got a confirmation email from Stripe.', 'created_at' => $now->copy()->subDays(1)->subHours(12)->toDateTimeString(), 'updated_at' => $now->copy()->subDays(1)->subHours(12)->toDateTimeString()],
            ['ticket_id' => $ticketIds['Payment not showing'], 'author_type' => 'admin', 'author_name' => 'Jeffrey Davidson', 'body' => 'Found the issue — there was a webhook delay. The payment has been reconciled and should now appear on the order. Let me know if it looks correct!', 'created_at' => $now->copy()->subHours(6)->toDateTimeString(), 'updated_at' => $now->copy()->subHours(6)->toDateTimeString()],

            // Ticket 4: Bulk import
            ['ticket_id' => $ticketIds['Feature request: bulk product import'], 'author_type' => 'admin', 'author_name' => 'Jeffrey Davidson', 'body' => "Great suggestion, Anthony! This is something several bakers have asked for. I've added it to our roadmap for Q2.", 'created_at' => $now->copy()->subDays(5)->toDateTimeString(), 'updated_at' => $now->copy()->subDays(5)->toDateTimeString()],
            ['ticket_id' => $ticketIds['Feature request: bulk product import'], 'author_type' => 'tenant', 'author_name' => 'Anthony Rossi', 'body' => 'Awesome, looking forward to it! In the meantime, is there a way to speed up adding products?', 'created_at' => $now->copy()->subDays(4)->toDateTimeString(), 'updated_at' => $now->copy()->subDays(4)->toDateTimeString()],
            ['ticket_id' => $ticketIds['Feature request: bulk product import'], 'author_type' => 'admin', 'author_name' => 'Jeffrey Davidson', 'body' => 'Tip: you can duplicate existing products and just change the name/price. That should save you some time!', 'created_at' => $now->copy()->subDays(2)->toDateTimeString(), 'updated_at' => $now->copy()->subDays(2)->toDateTimeString()],

            // Ticket 5: Coupons
            ['ticket_id' => $ticketIds['Need help with coupons'], 'author_type' => 'admin', 'author_name' => 'Jeffrey Davidson', 'body' => 'Hi Priya! I checked your coupon settings — SWEET20 is currently set to inactive. Would you like me to walk you through reactivating it?', 'created_at' => $now->copy()->subDays(13)->toDateTimeString(), 'updated_at' => $now->copy()->subDays(13)->toDateTimeString()],
            ['ticket_id' => $ticketIds['Need help with coupons'], 'author_type' => 'tenant', 'author_name' => 'Priya Patel', 'body' => 'Oh I see! I must have toggled it off by accident. I reactivated it and it works now. Thank you!', 'created_at' => $now->copy()->subDays(12)->toDateTimeString(), 'updated_at' => $now->copy()->subDays(12)->toDateTimeString()],
            ['ticket_id' => $ticketIds['Need help with coupons'], 'author_type' => 'admin', 'author_name' => 'Jeffrey Davidson', 'body' => "Glad it's working! I'll close this ticket. Don't hesitate to reach out if anything else comes up.", 'created_at' => $now->copy()->subDays(10)->toDateTimeString(), 'updated_at' => $now->copy()->subDays(10)->toDateTimeString()],
        ];

        foreach ($replies as $reply) {
            $db->table('support_replies')->insert($reply);
        }
    }

    // ──────────────────────────────────────────────
    // 4. Platform Messages
    // ──────────────────────────────────────────────
    private function seedPlatformMessages($db): void
    {
        $now = now();

        // Thread 1: Admin welcome to sweet-surrender
        $db->table('platform_messages')->insert([
            'tenant_id' => 'sweet-surrender',
            'sender_type' => 'admin',
            'subject' => 'Welcome to KneadIt! 🎉',
            'body' => "Hi Maria! Welcome aboard. I'm Jeffrey, the founder of KneadIt. If you have any questions getting started, don't hesitate to reach out. Happy baking!",
            'is_read' => true,
            'read_at' => $now->copy()->subDays(4)->toDateTimeString(),
            'parent_id' => null,
            'created_at' => $now->copy()->subDays(5)->toDateTimeString(),
            'updated_at' => $now->copy()->subDays(4)->toDateTimeString(),
        ]);
        $parentId = $db->getPdo()->lastInsertId();
        $db->table('platform_messages')->insert([
            'tenant_id' => 'sweet-surrender',
            'sender_type' => 'tenant',
            'subject' => 'Re: Welcome to KneadIt! 🎉',
            'body' => "Thank you Jeffrey! The platform looks amazing. I'm excited to get my online store up and running!",
            'is_read' => true,
            'read_at' => $now->copy()->subDays(4)->toDateTimeString(),
            'parent_id' => $parentId,
            'created_at' => $now->copy()->subDays(4)->toDateTimeString(),
            'updated_at' => $now->copy()->subDays(4)->toDateTimeString(),
        ]);

        // Thread 2: Admin feature tip to rolling-pin
        $db->table('platform_messages')->insert([
            'tenant_id' => 'rolling-pin',
            'sender_type' => 'admin',
            'subject' => 'Pro tip: Baking Sheets',
            'body' => 'Hey James! Did you know you can generate baking sheets for your daily orders? Go to Orders → Baking Sheet to see everything you need to prep for the day, organized by product.',
            'is_read' => false,
            'read_at' => null,
            'parent_id' => null,
            'created_at' => $now->copy()->subDays(1)->toDateTimeString(),
            'updated_at' => $now->copy()->subDays(1)->toDateTimeString(),
        ]);

        // Thread 3: Tenant question from flour-and-fancy
        $db->table('platform_messages')->insert([
            'tenant_id' => 'flour-and-fancy',
            'sender_type' => 'tenant',
            'subject' => 'Question about storefront SEO',
            'body' => 'Hi! Is there a way to add meta descriptions to my storefront pages? I want to improve my search engine visibility.',
            'is_read' => true,
            'read_at' => $now->copy()->subDays(2)->toDateTimeString(),
            'parent_id' => null,
            'created_at' => $now->copy()->subDays(3)->toDateTimeString(),
            'updated_at' => $now->copy()->subDays(2)->toDateTimeString(),
        ]);
        $parentId = $db->getPdo()->lastInsertId();
        $db->table('platform_messages')->insert([
            'tenant_id' => 'flour-and-fancy',
            'sender_type' => 'admin',
            'subject' => 'Re: Question about storefront SEO',
            'body' => 'Hi Sophie! Great question. Go to Storefront → Settings → SEO. You can set a meta title, description, and OG image for your store. Each product page also has its own SEO fields.',
            'is_read' => true,
            'read_at' => $now->copy()->subDays(1)->toDateTimeString(),
            'parent_id' => $parentId,
            'created_at' => $now->copy()->subDays(2)->toDateTimeString(),
            'updated_at' => $now->copy()->subDays(1)->toDateTimeString(),
        ]);

        // Thread 4: Tenant feedback from cake-boss
        $db->table('platform_messages')->insert([
            'tenant_id' => 'cake-boss',
            'sender_type' => 'tenant',
            'subject' => 'Loving the new order calendar!',
            'body' => 'Just wanted to say the order calendar view is a game-changer. I can finally see my whole week at a glance. Keep up the great work!',
            'is_read' => true,
            'read_at' => $now->copy()->subDays(5)->toDateTimeString(),
            'parent_id' => null,
            'created_at' => $now->copy()->subDays(6)->toDateTimeString(),
            'updated_at' => $now->copy()->subDays(5)->toDateTimeString(),
        ]);
        $parentId = $db->getPdo()->lastInsertId();
        $db->table('platform_messages')->insert([
            'tenant_id' => 'cake-boss',
            'sender_type' => 'admin',
            'subject' => 'Re: Loving the new order calendar!',
            'body' => "Thanks so much Anthony! That means a lot. We've got some exciting calendar improvements coming soon — stay tuned! 🗓️",
            'is_read' => false,
            'read_at' => null,
            'parent_id' => $parentId,
            'created_at' => $now->copy()->subDays(5)->toDateTimeString(),
            'updated_at' => $now->copy()->subDays(5)->toDateTimeString(),
        ]);
    }

    // ──────────────────────────────────────────────
    // 5. Email Campaigns
    // ──────────────────────────────────────────────
    private function seedEmailCampaigns($db): void
    {
        $now = now();

        $db->table('email_campaigns')->insert([
            [
                'name' => 'March Newsletter',
                'subject' => 'KneadIt March Update: New Features & Tips',
                'body' => "Happy March, bakers! 🧁\n\nHere's what's new:\n- Recipe Cost Calculator\n- Improved Order Calendar\n- Baking Sheet PDF Export\n\nLog in to try them out!",
                'target_segment' => 'all',
                'status' => 'sent',
                'scheduled_at' => $now->copy()->subDays(3)->toDateTimeString(),
                'sent_at' => $now->copy()->subDays(3)->toDateTimeString(),
                'recipient_count' => 5,
                'created_at' => $now->copy()->subDays(5)->toDateTimeString(),
                'updated_at' => $now->copy()->subDays(3)->toDateTimeString(),
            ],
            [
                'name' => 'Trial Expiration Reminder',
                'subject' => 'Your KneadIt trial is ending soon!',
                'body' => "Hi there!\n\nYour free trial is coming to an end. Upgrade now to keep all your data and unlock premium features.\n\nUse code BAKE20 for 20% off your first month!",
                'target_segment' => 'trial',
                'status' => 'scheduled',
                'scheduled_at' => $now->copy()->addDays(3)->toDateTimeString(),
                'sent_at' => null,
                'recipient_count' => 0,
                'created_at' => $now->copy()->subDay()->toDateTimeString(),
                'updated_at' => $now->copy()->subDay()->toDateTimeString(),
            ],
            [
                'name' => 'Starter Plan Upgrade Promo',
                'subject' => 'Ready to grow? Upgrade to Growth plan',
                'body' => "You've been doing great on the Starter plan! 🎉\n\nThe Growth plan unlocks delivery zones, advanced analytics, and priority support.\n\nUpgrade today and take your bakery to the next level.",
                'target_segment' => 'starter',
                'status' => 'draft',
                'scheduled_at' => null,
                'sent_at' => null,
                'recipient_count' => 0,
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
        ]);
    }

    // ──────────────────────────────────────────────
    // 7. Platform Activities
    // ──────────────────────────────────────────────
    private function seedPlatformActivities($db): void
    {
        $now = now();

        $activities = [
            ['event' => 'tenant_created',      'tenant_id' => 'sweet-surrender',  'description' => 'New tenant "Sweet Surrender Bakery" signed up on the starter plan.',           'metadata' => json_encode(['plan' => 'starter']),                                     'created_at' => $now->copy()->subDays(5)->toDateTimeString()],
            ['event' => 'tenant_created',      'tenant_id' => 'rolling-pin',      'description' => 'New tenant "The Rolling Pin" signed up on the growth plan.',                   'metadata' => json_encode(['plan' => 'growth']),                                      'created_at' => $now->copy()->subDays(25)->toDateTimeString()],
            ['event' => 'plan_changed',        'tenant_id' => 'rolling-pin',      'description' => 'The Rolling Pin upgraded from starter to growth.',                              'metadata' => json_encode(['from' => 'starter', 'to' => 'growth']),                   'created_at' => $now->copy()->subDays(10)->toDateTimeString()],
            ['event' => 'storefront_toggled',  'tenant_id' => 'flour-and-fancy',  'description' => 'Flour & Fancy disabled their storefront.',                                     'metadata' => json_encode(['enabled' => false]),                                      'created_at' => $now->copy()->subDays(1)->toDateTimeString()],
            ['event' => 'trial_expired',       'tenant_id' => 'flour-and-fancy',  'description' => 'Trial expired for Flour & Fancy.',                                             'metadata' => json_encode(['trial_ends_at' => $now->copy()->subDays(3)->toIso8601String()]), 'created_at' => $now->copy()->subDays(3)->toDateTimeString()],
            ['event' => 'tenant_created',      'tenant_id' => 'cake-boss',        'description' => 'New tenant "Cake Boss Kitchen" signed up on the growth plan.',                 'metadata' => json_encode(['plan' => 'growth']),                                      'created_at' => $now->copy()->subDays(45)->toDateTimeString()],
            ['event' => 'plan_changed',        'tenant_id' => 'sugar-rush',       'description' => 'Sugar Rush Sweets upgraded from growth to pro.',                               'metadata' => json_encode(['from' => 'growth', 'to' => 'pro']),                       'created_at' => $now->copy()->subDays(20)->toDateTimeString()],
            ['event' => 'tenant_deactivated',  'tenant_id' => 'sugar-rush',       'description' => 'Sugar Rush Sweets was deactivated.',                                           'metadata' => json_encode(['reason' => 'payment_failed']),                            'created_at' => $now->copy()->subDays(7)->toDateTimeString()],
            ['event' => 'storefront_toggled',  'tenant_id' => 'cake-boss',        'description' => 'Cake Boss Kitchen enabled their storefront.',                                  'metadata' => json_encode(['enabled' => true]),                                       'created_at' => $now->copy()->subDays(15)->toDateTimeString()],
            ['event' => 'tenant_created',      'tenant_id' => 'flour-and-fancy',  'description' => 'New tenant "Flour & Fancy" signed up on the starter plan.',                    'metadata' => json_encode(['plan' => 'starter']),                                     'created_at' => $now->copy()->subDays(20)->toDateTimeString()],
        ];

        foreach ($activities as $activity) {
            $db->table('platform_activities')->insert($activity);
        }
    }

    // ──────────────────────────────────────────────
    // 8. Feature Usage Logs
    // ──────────────────────────────────────────────
    private function seedFeatureUsageLogs($db): void
    {
        $features = ['quick_order', 'recipe_calculator', 'shopping_list', 'baking_sheet', 'order_calendar', 'review_analytics', 'storefront'];
        $tenants = ['sweet-surrender', 'rolling-pin', 'cake-boss', 'sugar-rush'];

        // Weight map: how heavily each tenant uses each feature (max daily count)
        $weights = [
            'sweet-surrender' => ['quick_order' => 8, 'recipe_calculator' => 2, 'shopping_list' => 5, 'baking_sheet' => 3, 'order_calendar' => 6, 'review_analytics' => 1, 'storefront' => 10],
            'rolling-pin' => ['quick_order' => 12, 'recipe_calculator' => 5, 'shopping_list' => 3, 'baking_sheet' => 7, 'order_calendar' => 8, 'review_analytics' => 4, 'storefront' => 15],
            'cake-boss' => ['quick_order' => 15, 'recipe_calculator' => 8, 'shopping_list' => 6, 'baking_sheet' => 10, 'order_calendar' => 12, 'review_analytics' => 3, 'storefront' => 20],
            'sugar-rush' => ['quick_order' => 3, 'recipe_calculator' => 1, 'shopping_list' => 1, 'baking_sheet' => 2, 'order_calendar' => 2, 'review_analytics' => 0, 'storefront' => 0],
        ];

        $rows = [];
        $now = now();

        foreach ($tenants as $tenant) {
            foreach ($features as $feature) {
                $maxCount = $weights[$tenant][$feature];
                if ($maxCount === 0) {
                    continue;
                }

                for ($d = 0; $d < 30; $d++) {
                    $date = $now->copy()->subDays($d)->toDateString();
                    // Deterministic but varied count
                    $count = max(1, intval($maxCount * (0.4 + 0.6 * abs(sin($d * 0.7 + ord($tenant[0]))))));

                    $rows[] = [
                        'tenant_id' => $tenant,
                        'feature' => $feature,
                        'usage_count' => $count,
                        'last_used_at' => $now->copy()->subDays($d)->subHours(random_int(1, 12))->toDateTimeString(),
                        'date' => $date,
                        'created_at' => $now->copy()->subDays($d)->toDateTimeString(),
                    ];
                }
            }
        }

        // Insert in chunks
        foreach (array_chunk($rows, 100) as $chunk) {
            $db->table('feature_usage_logs')->insert($chunk);
        }
    }

    // ──────────────────────────────────────────────
    // 9. Admin Audit Logs
    // ──────────────────────────────────────────────
    private function seedAdminAuditLogs($db): void
    {
        $now = now();

        $logs = [
            ['admin_id' => 1, 'action' => 'created_tenant',     'target_type' => 'tenant', 'target_id' => 'sweet-surrender', 'description' => 'Created tenant Sweet Surrender Bakery',              'metadata' => json_encode(['plan' => 'starter', 'email' => 'maria@sweetsurrender.com']),    'ip_address' => '192.168.1.10', 'created_at' => $now->copy()->subDays(5)->toDateTimeString()],
            ['admin_id' => 1, 'action' => 'created_tenant',     'target_type' => 'tenant', 'target_id' => 'rolling-pin',     'description' => 'Created tenant The Rolling Pin',                     'metadata' => json_encode(['plan' => 'growth', 'email' => 'james@therollingpin.com']),      'ip_address' => '192.168.1.10', 'created_at' => $now->copy()->subDays(13)->toDateTimeString()],
            ['admin_id' => 1, 'action' => 'impersonated',       'target_type' => 'tenant', 'target_id' => 'flour-and-fancy', 'description' => 'Impersonated tenant Flour & Fancy',                  'metadata' => json_encode(['reason' => 'Debugging storefront issue']),                      'ip_address' => '192.168.1.10', 'created_at' => $now->copy()->subDays(2)->toDateTimeString()],
            ['admin_id' => 1, 'action' => 'sent_announcement',  'target_type' => 'announcement', 'target_id' => '1',         'description' => 'Sent announcement: New Feature: Recipe Cost Calculator', 'metadata' => json_encode(['target_plans' => ['starter', 'growth', 'pro']]),             'ip_address' => '192.168.1.10', 'created_at' => $now->copy()->subDays(2)->toDateTimeString()],
            ['admin_id' => 1, 'action' => 'sent_announcement',  'target_type' => 'announcement', 'target_id' => '2',         'description' => 'Sent announcement: Scheduled Maintenance March 15',      'metadata' => json_encode(['target_plans' => ['starter', 'growth', 'pro']]),             'ip_address' => '10.0.0.1',     'created_at' => $now->copy()->subDay()->toDateTimeString()],
            ['admin_id' => 1, 'action' => 'changed_plan',       'target_type' => 'tenant', 'target_id' => 'rolling-pin',     'description' => 'Changed plan for The Rolling Pin: starter → growth',     'metadata' => json_encode(['from' => 'starter', 'to' => 'growth']),                    'ip_address' => '192.168.1.10', 'created_at' => $now->copy()->subDays(10)->toDateTimeString()],
            ['admin_id' => 1, 'action' => 'changed_plan',       'target_type' => 'tenant', 'target_id' => 'sugar-rush',      'description' => 'Changed plan for Sugar Rush Sweets: growth → pro',       'metadata' => json_encode(['from' => 'growth', 'to' => 'pro']),                        'ip_address' => '192.168.1.10', 'created_at' => $now->copy()->subDays(12)->toDateTimeString()],
            ['admin_id' => 1, 'action' => 'impersonated',       'target_type' => 'tenant', 'target_id' => 'sweet-surrender', 'description' => 'Impersonated tenant Sweet Surrender Bakery',          'metadata' => json_encode(['reason' => 'Helping with logo upload']),                       'ip_address' => '10.0.0.1',     'created_at' => $now->copy()->subDay()->toDateTimeString()],
            ['admin_id' => 1, 'action' => 'exported_data',      'target_type' => 'report', 'target_id' => null,              'description' => 'Exported platform analytics report',                     'metadata' => json_encode(['format' => 'csv', 'period' => 'last_30_days']),            'ip_address' => '192.168.1.10', 'created_at' => $now->copy()->subDays(4)->toDateTimeString()],
            ['admin_id' => 1, 'action' => 'exported_data',      'target_type' => 'report', 'target_id' => null,              'description' => 'Exported tenant list',                                   'metadata' => json_encode(['format' => 'csv', 'count' => 5]),                          'ip_address' => '192.168.1.10', 'created_at' => $now->copy()->subDays(7)->toDateTimeString()],
            ['admin_id' => 1, 'action' => 'created_tenant',     'target_type' => 'tenant', 'target_id' => 'cake-boss',       'description' => 'Created tenant Cake Boss Kitchen',                      'metadata' => json_encode(['plan' => 'growth', 'email' => 'anthony@cakebosskitchen.com']), 'ip_address' => '192.168.1.10', 'created_at' => $now->copy()->subDays(13)->toDateTimeString()],
            ['admin_id' => 1, 'action' => 'impersonated',       'target_type' => 'tenant', 'target_id' => 'cake-boss',       'description' => 'Impersonated tenant Cake Boss Kitchen',                  'metadata' => json_encode(['reason' => 'Demo walkthrough']),                               'ip_address' => '10.0.0.1',     'created_at' => $now->copy()->subDays(8)->toDateTimeString()],
            ['admin_id' => 1, 'action' => 'sent_announcement',  'target_type' => 'campaign', 'target_id' => '1',             'description' => 'Sent email campaign: March Newsletter',                  'metadata' => json_encode(['recipient_count' => 5]),                                   'ip_address' => '192.168.1.10', 'created_at' => $now->copy()->subDays(3)->toDateTimeString()],
            ['admin_id' => 1, 'action' => 'created_tenant',     'target_type' => 'tenant', 'target_id' => 'flour-and-fancy', 'description' => 'Created tenant Flour & Fancy',                          'metadata' => json_encode(['plan' => 'starter', 'email' => 'sophie@flourandfancy.com']),   'ip_address' => '192.168.1.10', 'created_at' => $now->copy()->subDays(11)->toDateTimeString()],
            ['admin_id' => 1, 'action' => 'exported_data',      'target_type' => 'report', 'target_id' => null,              'description' => 'Exported feature usage report',                          'metadata' => json_encode(['format' => 'csv', 'period' => 'last_7_days']),             'ip_address' => '192.168.1.10', 'created_at' => $now->copy()->subDays(1)->toDateTimeString()],
        ];

        foreach ($logs as $log) {
            $db->table('admin_audit_logs')->insert($log);
        }
    }

    // ──────────────────────────────────────────────
    // 10. Tenant Notes
    // ──────────────────────────────────────────────
    private function seedTenantNotes($db): void
    {
        $now = now();

        $db->table('tenant_notes')->insert([
            ['tenant_id' => 'sweet-surrender',  'body' => 'Called to help with product setup. Walked through category creation and image uploads. Very enthusiastic new user.',                  'author' => 'admin', 'created_at' => $now->copy()->subDays(4)->toDateTimeString(), 'updated_at' => $now->copy()->subDays(4)->toDateTimeString()],
            ['tenant_id' => 'rolling-pin',      'body' => 'Interested in upgrading to Growth. Discussed delivery zones and analytics features. Will follow up next week.',                      'author' => 'admin', 'created_at' => $now->copy()->subDays(12)->toDateTimeString(), 'updated_at' => $now->copy()->subDays(12)->toDateTimeString()],
            ['tenant_id' => 'flour-and-fancy',  'body' => 'Trial expired 3 days ago. Hasn\'t upgraded yet. Sent a personal follow-up email. Storefront currently disabled.',                    'author' => 'admin', 'created_at' => $now->copy()->subDays(1)->toDateTimeString(), 'updated_at' => $now->copy()->subDays(1)->toDateTimeString()],
            ['tenant_id' => 'cake-boss',        'body' => 'Power user — heavily uses order calendar and baking sheets. Requested bulk product import feature. Good candidate for case study.', 'author' => 'admin', 'created_at' => $now->copy()->subDays(6)->toDateTimeString(), 'updated_at' => $now->copy()->subDays(6)->toDateTimeString()],
            ['tenant_id' => 'sugar-rush',       'body' => 'Account deactivated due to failed payment. Attempted to contact via email twice with no response. Will try again in a week.',        'author' => 'admin', 'created_at' => $now->copy()->subDays(5)->toDateTimeString(), 'updated_at' => $now->copy()->subDays(5)->toDateTimeString()],
        ]);
    }

    // ──────────────────────────────────────────────
    // 11. Platform Settings
    // ──────────────────────────────────────────────
    private function seedPlatformSettings($db): void
    {
        $now = now();

        $db->table('platform_settings')->insert([
            ['key' => 'maintenance_mode',    'value' => '0',                                                                          'created_at' => $now->toDateTimeString(), 'updated_at' => $now->toDateTimeString()],
            ['key' => 'maintenance_message', 'value' => "We're performing scheduled maintenance. We'll be back shortly!", 'created_at' => $now->toDateTimeString(), 'updated_at' => $now->toDateTimeString()],
        ]);
    }
}
