# Best Practices Fixes

## Security
- [ ] Add throttle to auth routes (register, password reset)
- [ ] Add rate limiting to storefront POST routes

## Data Integrity
- [ ] Fix order number race condition with Cache::lock
- [ ] Add FK constraint on coupon_id in orders table

## Validation
- [ ] Fix unvalidated email in FavoriteController index
- [ ] Switch to $request->validated() in ForgotPassword/ResetPassword controllers

## Config
- [ ] Extract hardcoded URLs (ReferralProgram, BlogFeedController)
- [ ] Extract hardcoded PayPal values to config/settings

## DI Consistency
- [ ] Replace new Service() with injection in 5 controllers

## Enum Usage
- [ ] Replace hardcoded 'customer'/'baker' strings with SenderType enum
- [ ] Add label() to enums missing it, replace ucfirst($enum->value)
- [ ] Extract delivery fee tiers to config or enum

## Performance
- [ ] Fix N+1 in UpcomingOrdersWidget
- [ ] Fix N+1 in ReorderController
- [ ] Move queries out of Blade templates into controllers/components
- [ ] Add caching for expensive queries (Hero, dashboard stats)

## Architecture
- [ ] Extract AvailabilityController logic to CapacityCalculator
- [ ] Extract StripeConnectController::getAccountStatus to service
- [ ] Clean OnboardingController::store (extract email logic)
- [ ] Remove dead code (StripeConnectWebhook empty if, CateringController unused vars)
