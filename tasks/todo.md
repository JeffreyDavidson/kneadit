# Best Practices Fixes

## Security
- [x] Add throttle to auth routes (register, password reset)
- [x] Add rate limiting to storefront POST routes (13 routes)

## Data Integrity
- [ ] Fix order number race condition with Cache::lock (deferred — needs careful testing)
- [x] Add FK constraint on coupon_id in orders table

## Validation
- [x] Fix unvalidated email in FavoriteController index
- [x] Switch to $request->validated() in ForgotPassword/ResetPassword controllers

## Config
- [x] Extract hardcoded URLs (ReferralProgram, BlogFeedController)
- [x] Extract hardcoded PayPal values to tenant settings

## DI Consistency
- [x] Replace new Service() with injection in 4 controllers

## Enum Usage
- [x] Replace hardcoded 'customer'/'baker' strings with SenderType enum
- [x] Add label() to 8 enums, replace ucfirst($enum->value) across codebase
- [x] Extract delivery fee tiers to config('kneadit.delivery_fees')

## Performance
- [x] Fix N+1 in UpcomingOrdersWidget (withCount)
- [x] Fix N+1 in ReorderController (eager load)
- [x] Move queries out of Blade templates (deleted 6 dead partials, moved about queries to controller)
- [ ] Add caching for expensive queries (deferred — needs cache invalidation strategy)

## Architecture
- [x] Extract AvailabilityController logic → AvailabilityService
- [x] Remove dead StripeConnectController::getAccountStatus (never called)
- [x] Clean OnboardingController::store — reviewed, acceptable as-is (sequential, each step depends on previous)
- [x] Remove dead code (StripeConnectWebhook empty if, CateringController unused vars)

## Deferred Items
- Order number race condition — needs Cache::lock with careful testing around concurrent order creation
- Caching layer — needs cache invalidation strategy per tenant; best added when performance monitoring shows it's needed
