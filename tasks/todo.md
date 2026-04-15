# Round 8: Route/Controller Cleanup & Remaining Test Improvements

## Tasks

- [x] 8a: Create `VerifyEmailController` (invokable) and `SendVerificationNotificationController` (invokable) to replace inline closures in `routes/web.php`
- [x] 8b: Convert login redirect closure to `Route::redirect()` (confirmed middleware chaining works)
- [x] 8c: Replace hardcoded URLs in test files with named route helpers
  - [x] `InvitationTest.php` — use `route('invitation.show', ...)`
  - [x] `InvoiceTest.php` — use `route('admin.orders.invoice', ...)`
  - [x] `SurveyTest.php` — use `route('storefront.survey', ...)` and `route('survey.submit', ...)`
  - [x] `StripeSuccessControllerTest.php` — use `route('order.stripe.success', ...)`
- [x] 8d: Add missing integer casts to models
  - [x] `OrderItem` — `quantity`
  - [x] `Review` — `rating`
  - [x] `Category` — `sort_order`
  - [x] `Recipe` — `prep_time_minutes`
  - [x] `Holiday` — `lead_days`, `max_orders`
  - [x] `Referral` — `reward_months`
  - [x] `LoyaltyReward` — `points_required`
  - [x] `FeatureUsageLog` — `usage_count`
- [x] Run `php -l` on all modified files
- [x] Run `vendor/bin/pint --dirty --format agent`

## Review

All 15 modified files pass `php -l` and Pint formatting. All 12 affected tests pass.

**8a** — Created two invokable controllers (`VerifyEmailController`, `SendVerificationNotificationController`) to replace inline closures in `routes/web.php`. Removed unused `EmailVerificationRequest` and `Request` imports from the route file.

**8b** — Replaced the login redirect closure with `Route::redirect('login', '/')`. Confirmed `Route::redirect()` returns a `Route` instance that supports `->name()` and `->middleware()` chaining.

**8c** — Replaced 12 hardcoded URL strings across 4 test files with `route()` helpers using the `false` third parameter for tenant routes (established codebase pattern). The StripeSuccessControllerTest query string case uses array parameter syntax.

**8d** — Added explicit `'integer'` casts to 10 columns across 8 models. These ensure consistent type coercion regardless of database driver.
