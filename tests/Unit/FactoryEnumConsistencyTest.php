<?php

use App\Enums\Customers\CateringInquiryStatus;
use App\Enums\Customers\ReferralStatus;
use App\Enums\Financial\ExpenseCategory;
use App\Enums\Financial\IncomeSource;
use App\Enums\Marketing\EmailCampaignStatus;
use App\Enums\Marketing\SocialPlatform;
use App\Enums\Marketing\SocialPostStatus;
use App\Enums\Orders\SenderType;
use App\Enums\Platform\PlatformSenderType;
use App\Enums\Platform\SubscriptionTier;
use App\Enums\Platform\SupportTicketPriority;
use App\Enums\Platform\SupportTicketStatus;
use App\Enums\Staff\UserRole;
use App\Models\Content\SocialPost;
use App\Models\Customers\CateringInquiry;
use App\Models\Customers\Referral;
use App\Models\Engagement\EmailCampaign;
use App\Models\Financial\Expense;
use App\Models\Financial\GiftCard;
use App\Models\Financial\Income;
use App\Models\Inventory\ProductImage;
use App\Models\Orders\OrderMessage;
use App\Models\Platform\PlatformMessage;
use App\Models\Platform\SupportTicket;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;

test('social post factory produces valid enum values', function () {
    // Create many to exercise all randomElement possibilities
    $posts = SocialPost::factory()->count(20)->make();

    $posts->each(function (SocialPost $post) {
        expect($post->status)->toBeInstanceOf(SocialPostStatus::class)
            ->and($post->platform)->toBeInstanceOf(SocialPlatform::class);
    });
});

test('social post factory has scheduled state', function () {
    $post = SocialPost::factory()->scheduled()->make();

    expect($post->status)->toBe(SocialPostStatus::Scheduled)
        ->and($post->scheduled_for)->not->toBeNull();
});

test('social post factory has published state', function () {
    $post = SocialPost::factory()->published()->make();

    expect($post->status)->toBe(SocialPostStatus::Posted);
});

test('email campaign factory uses enum values', function () {
    $campaign = EmailCampaign::factory()->make();

    expect($campaign->status)->toBeInstanceOf(EmailCampaignStatus::class);
});

test('email campaign factory has sending state', function () {
    $campaign = EmailCampaign::factory()->sending()->make();

    expect($campaign->status)->toBe(EmailCampaignStatus::Sending);
});

test('email campaign factory has sent state', function () {
    $campaign = EmailCampaign::factory()->sent()->make();

    expect($campaign->status)->toBe(EmailCampaignStatus::Sent);
});

test('referral factory has completed state', function () {
    $referral = Referral::factory()->completed()->make();

    expect($referral->status)->toBe(ReferralStatus::Completed);
});

test('catering inquiry factory has quoted state', function () {
    $inquiry = CateringInquiry::factory()->quoted()->make();

    expect($inquiry->status)->toBe(CateringInquiryStatus::Quoted);
});

test('catering inquiry factory has confirmed state', function () {
    $inquiry = CateringInquiry::factory()->confirmed()->make();

    expect($inquiry->status)->toBe(CateringInquiryStatus::Confirmed);
});

test('catering inquiry factory has cancelled state', function () {
    $inquiry = CateringInquiry::factory()->cancelled()->make();

    expect($inquiry->status)->toBe(CateringInquiryStatus::Cancelled);
});

test('expense factory uses enum values', function () {
    $expenses = Expense::factory()->count(20)->make();

    $expenses->each(function (Expense $expense) {
        expect($expense->category)->toBeInstanceOf(ExpenseCategory::class);
    });
});

test('income factory uses enum values', function () {
    $incomes = Income::factory()->count(20)->make();

    $incomes->each(function (Income $income) {
        expect($income->source)->toBeInstanceOf(IncomeSource::class);
    });
});

test('support ticket factory has inProgress state', function () {
    $ticket = SupportTicket::factory()->inProgress()->make();

    expect($ticket->status)->toBe(SupportTicketStatus::InProgress);
});

test('support ticket factory has resolved state', function () {
    $ticket = SupportTicket::factory()->resolved()->make();

    expect($ticket->status)->toBe(SupportTicketStatus::Resolved);
});

test('support ticket factory has highPriority state', function () {
    $ticket = SupportTicket::factory()->highPriority()->make();

    expect($ticket->priority)->toBe(SupportTicketPriority::High);
});

test('platform message factory has fromTenant state', function () {
    $message = PlatformMessage::factory()->fromTenant()->make();

    expect($message->sender_type)->toBe(PlatformSenderType::Tenant);
});

test('platform message factory has fromAdmin state', function () {
    $message = PlatformMessage::factory()->fromAdmin()->make();

    expect($message->sender_type)->toBe(PlatformSenderType::Admin);
});

test('gift card factory has expired and depleted states', function () {
    $expired = GiftCard::factory()->expired()->make();
    $depleted = GiftCard::factory()->depleted()->make();

    expect($expired->expires_at->isPast())->toBeTrue()
        ->and((float) $depleted->current_balance)->toBe(0.0);
});

test('user factory has owner state', function () {
    $user = User::factory()->owner()->make();

    expect($user->role)->toBe(UserRole::Owner);
});

test('user factory has manager state', function () {
    $user = User::factory()->manager()->make();

    expect($user->role)->toBe(UserRole::Manager);
});

test('user factory has staff state', function () {
    $user = User::factory()->staff()->make();

    expect($user->role)->toBe(UserRole::Staff);
});

test('user factory has platformAdmin state', function () {
    $user = User::factory()->platformAdmin()->make();

    expect($user->role)->toBe(UserRole::PlatformAdmin);
});

test('tenant factory has growth state', function () {
    $tenant = Tenant::factory()->growth()->make();

    expect($tenant->plan)->toBe(SubscriptionTier::Growth->value);
});

test('tenant factory has pro state', function () {
    $tenant = Tenant::factory()->pro()->make();

    expect($tenant->plan)->toBe(SubscriptionTier::Pro->value);
});

test('order message factory fromBaker uses enum', function () {
    $message = OrderMessage::factory()->fromBaker()->make(['order_id' => 1]);

    expect($message->sender_type)->toBe(SenderType::Baker);
});

test('product image factory creates valid instance', function () {
    $image = ProductImage::factory()->make(['product_id' => 1]);

    expect($image->path)->toBeString()
        ->and($image->sort_order)->toBeInt()
        ->and($image->is_primary)->toBeBool();
});

test('gift card transaction factory uses enum type', function () {
    $transaction = App\Models\Financial\GiftCardTransaction::factory()->make(['gift_card_id' => 1]);

    expect($transaction->type)->toBeInstanceOf(App\Enums\Financial\GiftCardTransactionType::class);
});

test('gift card transaction factory redemption state uses enum', function () {
    $transaction = App\Models\Financial\GiftCardTransaction::factory()->redemption()->make(['gift_card_id' => 1]);

    expect($transaction->type)->toBe(App\Enums\Financial\GiftCardTransactionType::Redemption);
});

test('loyalty reward factory uses enum reward_type', function () {
    $rewards = App\Models\Engagement\LoyaltyReward::factory()->count(10)->make();

    $rewards->each(fn ($reward) => expect($reward->reward_type)->toBeInstanceOf(App\Enums\Engagement\RewardType::class));
});
