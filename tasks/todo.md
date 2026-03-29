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
- [x] Extract delivery fee tiers to config('kneadit.delivery_fees')

## DI Consistency
- [x] Replace new Service() with DI in all controllers + actions (zero service locator)
- [x] Replace resolve()/app() with constructor DI in CreateOrder and DeductIngredients

## Enum Usage
- [x] Replace hardcoded 'customer'/'baker' strings with SenderType enum
- [x] Add label() to 9 enums, replace ucfirst($enum->value) across codebase
- [x] SubscriptionTier label() added

## Performance
- [x] Fix N+1 in UpcomingOrdersWidget (withCount)
- [x] Fix N+1 in ReorderController (eager load)
- [x] Move queries out of Blade templates (deleted 6 dead partials, moved about queries to controller)
- [x] Add caching for storefront stat queries (Hero, AboutController — 1h TTL)

## Architecture
- [x] Extract AvailabilityController logic → AvailabilityService
- [x] Remove dead StripeConnectController::getAccountStatus (never called)
- [x] Remove dead LoginController + LoginRequest (never routed)
- [x] Remove dead code (StripeConnectWebhook empty if, CateringController unused vars, 6 dead Blade partials)
- [x] Remove unnecessary try/catch in LoyaltyController

## Code Quality
- [x] Add ShouldBeUnique to 3 queued listeners
- [x] Add missing indexes on central tables (admin_audit_logs, platform_activities, email_campaigns)
- [x] Add Http::preventStrayRequests() to all HTTP-faking tests
- [x] Add environments(['production']) to health:check schedule

## Conventions
- [x] Replace number_format() with Number::currency()/format() (34 conversions)
- [x] Replace raw PHP string functions with Str:: helpers (45 conversions)
- [x] Extract inline <style> blocks to external CSS files (32 files, ~1,683 lines)
- [x] Add @stack('styles') and @stack('scripts') to storefront layout
- [x] Fix flaky phone format in 4 factories (numerify instead of phoneNumber)

## Arch Tests (84 total)
- [x] Actions must be invokable
- [x] Form requests must extend FormRequest
- [x] Services must be classes
- [x] Observers must end with Observer
- [x] Enums must be string-backed
- [x] Mail/Listener/Command naming conventions enforced

## Review
- Test suite: 1,463 tests, 3,826 assertions, 84 arch tests, 0 failures
- All best practices audit items addressed
- 8 bugs fixed, 1 flaky test fixed
- ~1,683 lines CSS extracted, 45 Str:: conversions, 34 Number:: conversions
- Dead code removed: LoginController, LoginRequest, 6 Blade partials, static methods
