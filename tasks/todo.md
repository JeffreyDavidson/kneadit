# Test Coverage Gap Analysis

**Current coverage: 71.6%** (1,722 tests, 4,383 assertions)
**Type coverage: 100%**

## Priority Tiers

Business logic and data integrity matter most. Admin UI matters least.

---

## Tier 1: Core Business Logic — Actions & Services (0% coverage)

These are write operations and domain services with zero tests. Highest risk.

### Actions (0% coverage)
- [ ] `Actions/Customers/CreateBirthdayCoupon` — 0%
- [ ] `Actions/Orders/AwardLoyaltyPoints` — 0%
- [ ] `Actions/Stripe/InitiateStripeConnect` — 0% (only instantiation test)

### Services (0% coverage)
- [ ] `Services/Engagement/Engagements/RepeatOrderReminderEngagement` — 0%
- [ ] `Services/Engagement/Engagements/ReviewRequestEngagement` — 0%
- [ ] `Services/Tenants/ChurnAlertService` — 0%

### Services (< 50% coverage)
- [ ] `Services/Engagement/EngagementDispatcher` — 21.2%
- [ ] `Services/Engagement/Engagements/BirthdayDiscountEngagement` — 4.5%
- [ ] `Services/Engagement/Engagements/BirthdayEmailEngagement` — 4.8%
- [ ] `Services/Analytics/PageViewTracker` — 13.6%
- [ ] `Services/Customers/BirthdayCalculator` — 11.1%
- [ ] `Services/Tenants/TenantUsageService` — 11.9%
- [ ] `Services/Tenants/TenantHealthService` — 12.6%
- [ ] `Services/Tenants/TenancyManager` — 45.5%
- [ ] `Services/Settings/TenantSettingsRegistry` — 38.5%
- [ ] `Services/Platform/ForgeService` — 4.8%
- [ ] `Services/Stripe/StripeCheckoutService` — 6.3%
- [ ] `Services/Financial/TaxCsvExporter` — 46.2%
- [ ] `Services/Production/PrepScheduleService` — 47.3%
- [ ] `Services/Analytics/ProductTrendsService` — 48.6%

---

## Tier 2: Data Layer — Models, Builders, Queries (low coverage)

### Builders (< 50% coverage)
- [ ] `Builders/Inventory/ProductQueryBuilder` — 25.0%
- [ ] `Builders/Content/BlogPostQueryBuilder` — 43.8%
- [ ] `Builders/Orders/OrderQueryBuilder` — 43.8%
- [ ] `Builders/Financial/ExpenseQueryBuilder` — 44.4%
- [ ] `Builders/Financial/IncomeQueryBuilder` — 50.0%
- [ ] `Builders/Customers/ReviewQueryBuilder` — 57.1%

### Queries (< 100%)
- [ ] `Queries/Financial/ProductSalesQuery` — 50.0% (topByQuantity untested)
- [ ] `Queries/Financial/RevenueQuery` — 75.0% (orderCount untested)

### Models (0% coverage)
- [ ] `Models/Engagement/EmailCampaignLog` — 0%
- [ ] `Models/Operations/CheckinLog` — 0%
- [ ] `Models/Platform/PlatformSetting` — 0%

### Models (< 70% coverage)
- [ ] `Models/Content/BlogPost` — 50.0%
- [ ] `Models/Staff/User` — 46.4%
- [ ] `Models/Customers/CustomerFavorite` — 50.0%
- [ ] `Models/Customers/TenantNote` — 50.0%
- [ ] `Models/Operations/BusinessSchedule` — 66.7%
- [ ] `Models/Operations/ScheduledCheckin` — 66.7%
- [ ] `Models/Customers/Referral` — 66.7%
- [ ] `Models/Customers/CustomerNote` — 66.7%
- [ ] `Models/Engagement/PageView` — 60.0%
- [ ] `Models/Customers/WaitlistEntry` — 69.2%
- [ ] `Models/Orders/OrderMessage` — 75.0%

---

## Tier 3: HTTP Layer — Controllers & Middleware

### Controllers (0% coverage)
- [ ] `Http/Controllers/Billing/BillingPortalController` — 0%
- [ ] `Http/Controllers/Central/ConsumeImpersonationController` — 0%
- [ ] `Http/Controllers/Central/ImpersonateController` — 0%
- [ ] `Http/Controllers/Central/SitemapController` — 0%
- [ ] `Http/Controllers/Order/StripeSuccessController` — 0%
- [ ] `Http/Controllers/Storefront/SubmitOrderController` — 0%
- [ ] `Http/Controllers/Stripe/StripeConnectController` — 0%

### Controllers (< 50% coverage)
- [ ] `Http/Controllers/Billing/SwapPlanController` — 18.2%
- [ ] `Http/Controllers/Billing/CheckoutSuccessController` — 22.2%
- [ ] `Http/Controllers/Billing/CheckoutController` — 33.3%
- [ ] `Http/Controllers/Api/ReviewController` — 37.5%
- [ ] `Http/Controllers/Stripe/StripeWebhookController` — 42.6%
- [ ] `Http/Controllers/Central/ExportController` — 45.9%
- [ ] `Http/Controllers/Stripe/StripeConnectWebhookController` — 46.2%

### Middleware (< 70% coverage)
- [ ] `Http/Middleware/EnsureOnboardingComplete` — 50.0%

---

## Tier 4: Support Classes — DTOs, Enums, ValueObjects, Listeners

### Settings DTOs (0% coverage — 8 files)
- [ ] `DataTransferObjects/Settings/BrandingSettings` — 0%
- [ ] `DataTransferObjects/Settings/CateringSettings` — 0%
- [ ] `DataTransferObjects/Settings/EngagementSettings` — 0%
- [ ] `DataTransferObjects/Settings/HomepageSettings` — 0%
- [ ] `DataTransferObjects/Settings/OnboardingSettings` — 0%
- [ ] `DataTransferObjects/Settings/PaymentSettings` — 0%
- [ ] `DataTransferObjects/Settings/PolicySettings` — 0%
- [ ] `DataTransferObjects/Settings/WebhookSettings` — 0%

### Other DTOs (< 100%)
- [ ] `DataTransferObjects/Settings/StoreInfo` — 25.0%
- [ ] `DataTransferObjects/Settings/OrderSettings` — 50.0%
- [ ] `DataTransferObjects/Financial/PricingRecommendation` — 51.9%

### Enums (0% coverage — 9 enums)
- [ ] `Enums/Content/CaptionStyle` — 0%
- [ ] `Enums/Content/PageType` — 0%
- [ ] `Enums/Customers/ReferralStatus` — 0%
- [ ] `Enums/Financial/CouponTransactionType` — 0%
- [ ] `Enums/Financial/GiftCardTransactionType` — 0%
- [ ] `Enums/Inventory/StockAdjustmentType` — 0%
- [ ] `Enums/Inventory/StockStatus` — 0%
- [ ] `Enums/Orders/SenderType` — 0%
- [ ] `Enums/Storefront/StorefrontTheme` — 0%

### Enums (< 100%)
- [ ] `Enums/Financial/MarginHealth` — 50.0%
- [ ] `Enums/Financial/PricingPosition` — 50.0%
- [ ] `Enums/Content/BlogPostCategory` — 60.0%
- [ ] `Enums/Staff/UserRole` — 84.6%

### Listeners (< 50% coverage)
- [ ] `Listeners/Customers/SendBirthdayDiscountEmailListener` — 20.0%
- [ ] `Listeners/Customers/SendReviewRequestEmailListener` — 33.3%
- [ ] `Listeners/Platform/SendPaymentFailedEmailListener` — 20.0%
- [ ] `Listeners/Platform/SendTrialExpiredEmailListener` — 20.0%
- [ ] `Listeners/Platform/SendTrialReminderEmailListener` — 20.0%
- [ ] `Listeners/Marketing/SendCateringQuoteEmailListener` — 20.0%
- [ ] `Listeners/Customers/SendHappyBirthdayEmailListener` — 42.9%
- [ ] `Listeners/Customers/SendRepeatOrderReminderEmailListener` — 42.9%
- [ ] `Listeners/Marketing/SendCampaignEmailListener` — 42.9%
- [ ] `Listeners/Platform/SendScheduledCheckinEmailListener` — 42.9%
- [ ] `Listeners/Platform/SendStaffInvitationEmailListener` — 42.9%
- [ ] `Listeners/Orders/SendOrderPlacedEmailListener` — 44.4%

### ValueObjects (< 100%)
- [ ] `ValueObjects/Address` — 66.7%
- [ ] `ValueObjects/Money` — 92.3%

### Contracts
- [ ] `Contracts/Engagement/EngagementRecipient` — 0%

---

## Tier 5: Console Commands

### Commands (0% coverage)
- [ ] `Console/Commands/Stripe/CreateStripeProductsCommand` — 0%
- [ ] `Console/Commands/Tenants/CreateDemoBakeriesCommand` — 0%
- [ ] `Console/Commands/Tenants/CreateDemoTenantCommand` — 0%
- [ ] `Console/Commands/Tenants/CreateOneTenantCommand` — 0%

### Commands (< 50% coverage)
- [ ] `Console/Commands/Stripe/CheckPayPalPaymentsCommand` — 4.7%
- [ ] `Console/Commands/Platform/SendScheduledCheckinsCommand` — 13.8%
- [ ] `Console/Commands/Platform/SendWeeklyDigestCommand` — 14.3%
- [ ] `Console/Commands/Platform/CheckChurnAlertsCommand` — 50.8%

---

## Tier 6: Filament Admin UI (lowest priority)

These are admin panel pages/forms. They have value but lower risk than business logic.
Listed only items at 0% or below 30%.

### Filament Pages (0% or < 30%)
- [ ] `Filament/Pages/Platform/OnboardingSteps/CompleteStep` — 0%
- [ ] `Filament/Pages/Platform/OnboardingSteps/OnboardingStepRegistry` — 0%
- [ ] `Filament/Pages/Platform/OnboardingSteps/PreviewStep` — 0%
- [ ] `Filament/Central/Pages/DataExport` — 8.5%
- [ ] `Filament/Central/Pages/TenantComparison` — 9.5%
- [ ] `Filament/Central/Pages/BakeryInsights` — 11.4%
- [ ] `Filament/Pages/Tools/InstagramCaptionGenerator` — 15.8%
- [ ] `Filament/Pages/Tools/PriceSuggestionTool` — 17.1%
- [ ] `Filament/Pages/Platform/Messages` — 17.9%
- [ ] `Filament/Pages/Analytics/SurveyResults` — 20.8%
- [ ] `Filament/Pages/Settings/CustomDomain` — 22.5%
- [ ] `Filament/Pages/Operations/StaffManagement` — 23.4%
- [ ] `Filament/Central/Pages/OnboardingTracker` — 23.8%
- [ ] `Filament/Pages/Analytics/ReportsCenter` — 25.0%
- [ ] `Filament/Pages/Tools/ShoppingListGenerator` — 27.4%
- [ ] `Filament/Pages/Settings/ThemeSelector` — 28.6%
- [ ] `Filament/Pages/Platform/Onboarding` — 29.2%

### Filament Resource Forms/Views (0%)
- [ ] All `Schemas/*Form` files at 0% (forms are exercised by create/edit feature tests)
- [ ] All `Pages/View*` files at 0% (view pages)
- [ ] All `RelationManagers/*` at 0%

---

## Approach

For each tier, work through items in this order:
1. Read the source to understand what needs testing
2. Write tests at the correct level (Unit/Integration/Feature per our definitions)
3. Run coverage on that file to verify improvement
4. Move to the next item

**Target:** Get Tiers 1-4 to 80%+ coverage. Tiers 5-6 can stay lower for now.
