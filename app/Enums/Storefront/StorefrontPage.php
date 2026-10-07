<?php

namespace App\Enums\Storefront;

use Filament\Support\Contracts\HasLabel;

/**
 * The storefront pages that get their own browser title, keyed by route name.
 * A page not listed here keeps the bakery name as its title.
 */
enum StorefrontPage: string implements HasLabel
{
    case Menu = 'storefront.menu';
    case Order = 'order.create';
    case OrderConfirmation = 'order.confirmation';
    case OrderVerify = 'order.verify.show';
    case OrderTracking = 'order.track';
    case GiftCards = 'storefront.giftCards';
    case Gallery = 'storefront.gallery';
    case Reviews = 'storefront.reviews';
    case ReviewForm = 'storefront.submitReview';
    case ReviewThanks = 'storefront.reviewSubmitted';
    case About = 'storefront.about';
    case Contact = 'contact.show';
    case Catering = 'storefront.catering';
    case CateringDepositPayment = 'catering.payDeposit';
    case CateringDepositSuccess = 'catering.stripe.success';
    case CateringDepositCancelled = 'catering.stripe.cancel';
    case Blog = 'storefront.blog';
    case Survey = 'storefront.survey';
    case Rewards = 'storefront.rewards';
    case Unsubscribe = 'emailUnsubscribe.show';
    case AccountLogin = 'account.login.show';
    case AccountRegister = 'account.register.show';
    case AccountForgotPassword = 'account.password.request';
    case AccountResetPassword = 'account.password.reset';
    case AccountVerifyEmail = 'account.email.verify.notice';
    case AccountDashboard = 'account.dashboard';
    case AccountOrders = 'account.orders';
    case AccountProfile = 'account.profile.show';

    public static function forRoute(?string $routeName): ?self
    {
        return self::tryFrom((string) $routeName);
    }

    public function title(string $storeName): string
    {
        return "{$this->getLabel()} · {$storeName}";
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Menu => 'Menu',
            self::Order => 'Order',
            self::OrderConfirmation => 'Order Confirmation',
            self::OrderVerify => 'Verify Your Order',
            self::OrderTracking => 'Track Your Order',
            self::GiftCards => 'Gift Cards',
            self::Gallery => 'Gallery',
            self::Reviews => 'Reviews',
            self::ReviewForm, self::ReviewThanks => 'Leave a Review',
            self::About => 'About',
            self::Contact => 'Contact',
            self::Catering => 'Catering',
            self::CateringDepositPayment, self::CateringDepositSuccess, self::CateringDepositCancelled => 'Catering Deposit',
            self::Blog => 'Blog',
            self::Survey => 'Survey',
            self::Rewards => 'Rewards',
            self::Unsubscribe => 'Email Preferences',
            self::AccountLogin => 'Sign In',
            self::AccountRegister => 'Create an Account',
            self::AccountForgotPassword => 'Forgot Password',
            self::AccountResetPassword => 'Reset Password',
            self::AccountVerifyEmail => 'Verify Your Email',
            self::AccountDashboard => 'My Account',
            self::AccountOrders => 'My Orders',
            self::AccountProfile => 'My Profile',
        };
    }

    /**
     * A search-result summary for the pages people find through search; null
     * keeps the bakery tagline the layout falls back to.
     */
    public function description(string $storeName): ?string
    {
        return match ($this) {
            self::Menu => "Browse the {$storeName} menu and order fresh baked goods for pickup or delivery.",
            self::Order => "Place an order with {$storeName} for pickup or delivery.",
            self::GiftCards => "Buy a gift card for {$storeName}, or check the balance of one you already have.",
            self::Gallery => "Photos of {$storeName} bakes shared by our customers.",
            self::Reviews => "What customers say about {$storeName}.",
            self::About => "The story behind {$storeName}.",
            self::Contact => "Get in touch with {$storeName}: hours, location and a contact form.",
            self::Catering => "Request catering from {$storeName} for your event.",
            self::Blog => "News, recipes and stories from {$storeName}.",
            default => null,
        };
    }
}
