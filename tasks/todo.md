# Multi-Language Support — String Extraction Plan

## Goal
Extract all hardcoded English strings from Blade templates into Laravel lang files using `__()` calls. English only for now — adding more languages later will just require creating new translation files.

## Lang File Structure
```
lang/en/
├── common.php                  # Shared: buttons, labels, loading states, empty states
├── forms.php                   # Shared form labels & placeholders
├── auth.php                    # Login, register, password reset
├── errors.php                  # 404, 500, 503 pages
├── invitations.php             # Invitation page
├── storefront/
│   ├── home.php
│   ├── menu.php
│   ├── order.php
│   ├── order-confirmation.php
│   ├── tracking.php
│   ├── about.php
│   ├── contact.php
│   ├── reviews.php
│   ├── submit-review.php
│   ├── loyalty.php
│   ├── gift-cards.php
│   ├── catering.php
│   ├── gallery.php
│   ├── blog.php
│   ├── survey.php
│   └── driver.php
├── components/
│   ├── hero.php
│   └── featured-products.php
└── emails/
    ├── orders.php
    ├── customers.php
    ├── marketing.php
    └── platform.php
```

## Phases

### Phase 1-2: Infrastructure + Auth/Error/Invitation Pages ✅
- [x] Fix `APP_NAME=Laravel` → `APP_NAME=KneadIt` in `.env`
- [x] Create `lang/en/` directory
- [x] Create `lang/en/forms.php` — shared form labels & placeholders
- [x] Create `lang/en/auth.php` — login, register, forgot/reset password, verify email + Laravel framework keys
- [x] Create `lang/en/errors.php` — 404, 500, 503 pages
- [x] Create `lang/en/invitations.php` — invitation show + expired
- [x] Extract `auth/login.blade.php` (11 strings)
- [x] Extract `auth/register.blade.php` (20 strings, `{!! !!}` for terms agreement)
- [x] Extract `auth/forgot-password.blade.php` (7 strings)
- [x] Extract `auth/reset-password.blade.php` (11 strings)
- [x] Extract `auth/verify-email.blade.php` (4 strings, `{!! !!}` for email-bold description, `e()` for email param)
- [x] Extract `errors/404.blade.php` (4 strings, parameterized `:store`)
- [x] Extract `errors/500.blade.php` (4 strings)
- [x] Extract `errors/503.blade.php` (3 strings)
- [x] Extract `invitations/show.blade.php` (14 strings, `{!! !!}` for HTML strings, `e()` for user params)
- [x] Extract `invitations/expired.blade.php` (3 strings)
- [x] Update `<html lang="en">` → `<html lang="{{ app()->getLocale() }}">`  on all 10 files
- [x] Replace hardcoded "KneadIt" with `config('app.name')` in brand references
- [x] Update `TenantStorefrontMetaTest` to check for translation key reference instead of raw string
- [x] Run `vendor/bin/pint --dirty --format agent` — passed
- [x] Run `php artisan test --compact` — all previously-passing tests still pass

### Phase 3: Core Storefront Pages
- [ ] Extract `storefront/home.blade.php` → `lang/en/storefront/home.php`
- [ ] Extract `storefront/menu.blade.php` → `lang/en/storefront/menu.php`
- [ ] Extract `storefront/order.blade.php` → `lang/en/storefront/order.php`
- [ ] Extract `storefront/order-confirmation.blade.php` → `lang/en/storefront/order-confirmation.php`
- [ ] Extract `storefront/order-tracking.blade.php` → `lang/en/storefront/tracking.php`

### Phase 4: Secondary Storefront Pages
- [ ] Extract `storefront/about.blade.php` → `lang/en/storefront/about.php`
- [ ] Extract `storefront/contact.blade.php` → `lang/en/storefront/contact.php`
- [ ] Extract `storefront/reviews.blade.php` → `lang/en/storefront/reviews.php`
- [ ] Extract `storefront/submit-review.blade.php` → `lang/en/storefront/submit-review.php`
- [ ] Extract `storefront/loyalty.blade.php` → `lang/en/storefront/loyalty.php`
- [ ] Extract `storefront/gift-cards.blade.php` → `lang/en/storefront/gift-cards.php`
- [ ] Extract `storefront/catering.blade.php` → `lang/en/storefront/catering.php`
- [ ] Extract `storefront/gallery.blade.php` → `lang/en/storefront/gallery.php`
- [ ] Extract `storefront/blog/index.blade.php` → `lang/en/storefront/blog.php`
- [ ] Extract `storefront/blog/show.blade.php` → append to blog.php
- [ ] Extract `storefront/survey.blade.php` → `lang/en/storefront/survey.php`
- [ ] Extract `storefront/driver.blade.php` → `lang/en/storefront/driver.php`

### Phase 5: Components
- [ ] Extract `components/home/hero.blade.php` → `lang/en/components/hero.php`
- [ ] Extract `components/home/featured-products.blade.php` → `lang/en/components/featured-products.php`
- [ ] Extract other components with hardcoded strings

### Phase 6: Email Templates
- [ ] Extract order email templates → `lang/en/emails/orders.php`
- [ ] Extract customer email templates → `lang/en/emails/customers.php`
- [ ] Extract marketing email templates → `lang/en/emails/marketing.php`
- [ ] Extract platform email templates → `lang/en/emails/platform.php`

### Phase 7: Cleanup & Verify
- [ ] Run `php artisan test --compact` to ensure nothing broke
- [ ] Run `vendor/bin/pint --dirty --format agent` for formatting
- [ ] Verify storefront pages render correctly

## Conventions
- Use `__('file.key')` syntax (e.g., `__('storefront/home.hero_eyebrow')`)
- Parameterized strings use `:placeholder` syntax: `__('common.welcome_back', ['name' => $name])`
- Shared strings (buttons, labels) go in `common.php` or `forms.php` — page-specific strings go in their page file
- Keys use snake_case
- Group related keys with dot notation arrays in the lang files
- HTML in translations: use `{!! __('key') !!}` and escape user params with `e()` before passing
- Brand name uses `config('app.name')` instead of hardcoded "KneadIt"
- `<html lang>` uses `app()->getLocale()` for future-proofing

## Review — Phase 1-2

### Summary
Created localization infrastructure and extracted all hardcoded strings from auth, error, and invitation pages (10 Blade templates total).

### Files Created (4)
- `lang/en/forms.php` — 4 labels + 7 placeholders shared across forms
- `lang/en/auth.php` — 3 Laravel framework keys + 5 page groups (login, register, forgot_password, reset_password, verify_email)
- `lang/en/errors.php` — 3 error page groups (404, 500, 503)
- `lang/en/invitations.php` — 2 groups (show, expired)

### Files Modified (11)
- `.env` — `APP_NAME=Laravel` → `APP_NAME=KneadIt`
- 5 auth templates — all strings extracted to `__('auth.*')` + `__('forms.*')`
- 3 error templates — all strings extracted to `__('errors.*')`
- 2 invitation templates — all strings extracted to `__('invitations.*')` + `__('forms.*')`
- `TenantStorefrontMetaTest.php` — updated assertion to check for translation key reference

### Key Decisions
- Used `{!! !!}` only where HTML lives in the translation (terms agreement, email bold, role bold, welcome back bold)
- User-provided params escaped with `e()` before passing to `__()` (email, name, role)
- 404 page: changed fallback `'KneadIt'` to `config('app.name')` for consistency
- Emojis (🍞, ⏰) kept in Blade templates, not in lang files

### Pre-existing Test Issues (not caused by this change)
- 30 errors: `TenantSettings::__construct()` missing `$birthdayCouponEnabled` argument
- 4 warnings: Missing `BirthdayDiscountGenerated.php` and `SendBirthdayDiscountEmailListener.php`
