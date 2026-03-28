# Best Practices Fixes — ALL COMPLETE

## Security
- [x] Add throttle to auth routes (register, password reset)
- [x] Add rate limiting to storefront POST routes (13 routes)

## Data Integrity
- [x] Fix order number race condition with Cache::lock + MAX()
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
- [x] Add caching for storefront stat queries (Hero, AboutController — 1h TTL)

## Architecture
- [x] Extract AvailabilityController logic → AvailabilityService
- [x] Remove dead StripeConnectController::getAccountStatus (never called)
- [x] OnboardingController::store — reviewed, acceptable as-is
- [x] Remove dead code (StripeConnectWebhook empty if, CateringController unused vars, 6 dead Blade partials)
