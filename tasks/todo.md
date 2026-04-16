# Customizable Email Templates (Pro Feature)

## Goal
Allow Pro-tier tenants to customize the subject line and body content of customer-facing emails, while providing sensible defaults that work out of the box for all tenants.

---

## Architecture

### New Enum: `EmailTemplateType` (backed string enum)
Defines every customizable email type. Cases:
- `OrderPlaced`
- `OrderConfirmed`
- `OrderBaking`
- `OrderReady`
- `OrderDelivered`
- `OrderCancelled`
- `ReviewRequest`
- `HappyBirthday`
- `RepeatOrderReminder`
- `ProductAvailable`

Each case provides:
- `label()` — human-readable name (e.g., "Order Placed")
- `description()` — what the email does (e.g., "Sent when a customer submits an order")
- `availablePlaceholders()` — array of placeholder strings for that type
- `defaultSubject()` — matches current hardcoded subjects

### New Model: `EmailTemplate` (tenant DB)
- `id`, `email_type` (cast to `EmailTemplateType`), `subject`, `body` (longText), `timestamps`
- No factory/seeder — templates are created on-demand when a tenant customizes

### New Service: `EmailTemplateRenderer`
1. Look up `EmailTemplate` for the given `EmailTemplateType`
2. If found → use custom subject/body
3. If not → return `null` so the Mailable falls back to the existing Blade view
4. Replace `{placeholder}` tokens with actual values in subject and body
5. Return a simple DTO with `subject` and `body`

### Changes to Mailables
Each of the 10 customer-facing Mailables:
- `envelope()` → checks renderer for custom subject, falls back to current hardcoded subject
- `content()` → when custom body exists, uses a generic `emails.custom-template` view that wraps the body in the existing layout; otherwise uses the current Blade view unchanged

**Zero disruption for non-Pro tenants** — they keep the existing Blade templates exactly as-is.

### New Blade View: `emails/custom-template.blade.php`
Extends `emails.layout`, renders `{!! $customBody !!}` in the content section. Only used when a tenant has a custom template.

### New Filament Page: `ManageEmailTemplates`
- Settings navigation group
- Gated: `Feature::active('pro-features')` + `RequiresManagerRole`
- Table of all `EmailTemplateType` cases showing name, description, status (Default / Customized)
- Edit slide-over per type:
  - Available placeholders listed (read-only reference)
  - Subject input (pre-filled with default)
  - Body textarea (pre-filled with default)
  - Reset to default action (deletes the `EmailTemplate` row)

---

## Implementation Steps

- [x] 1. Create `EmailTemplateType` enum with cases, labels, descriptions, placeholders, default subjects
- [x] 2. Create migration + `EmailTemplate` model + factory
- [x] 3. Create `EmailTemplateRenderer` service
- [x] 4. Create `emails/custom-template.blade.php` view
- [x] 5. Update 6 Mailables (covering 10 email types) to use renderer
- [x] 6. Create `ManageEmailTemplates` Filament page with slide-over edit + reset
- [x] 7. Write tests (71 new tests: enum, renderer, mailable integration, Filament page)
- [x] 8. Run full test suite (3,292 pass) + Pint (clean)

---

## Placeholders by Email Type

| Email Type | Placeholders |
|---|---|
| Order Placed | `{customer_name}`, `{order_number}`, `{order_total}`, `{store_name}` |
| Order Confirmed | `{customer_name}`, `{order_number}`, `{order_total}`, `{delivery_date}`, `{store_name}` |
| Order Baking | `{customer_name}`, `{order_number}`, `{store_name}` |
| Order Ready | `{customer_name}`, `{order_number}`, `{store_name}` |
| Order Delivered | `{customer_name}`, `{order_number}`, `{store_name}` |
| Order Cancelled | `{customer_name}`, `{order_number}`, `{order_total}`, `{store_name}` |
| Review Request | `{customer_name}`, `{order_number}`, `{review_url}`, `{store_name}` |
| Happy Birthday | `{customer_name}`, `{coupon_code}`, `{coupon_amount}`, `{store_name}` |
| Repeat Order Reminder | `{customer_name}`, `{days_since_last_order}`, `{store_name}` |
| Product Available | `{customer_name}`, `{product_name}`, `{store_name}` |

---

## What This Does NOT Change
- Email layout (header/footer/branding) — unchanged
- BakerBranded from/reply-to — unchanged
- BaseMailable view data injection — unchanged
- Platform emails — not customizable
- Email campaign system — separate feature
- Non-Pro tenants — no change whatsoever

---

## Review

### Files Created (8)
- `app/Enums/Marketing/EmailTemplateType.php` — backed string enum with 10 cases, each providing label, description, placeholders, and default subject
- `app/Models/Marketing/EmailTemplate.php` — tenant-scoped model with `email_type` (enum cast), `subject`, `body`
- `database/factories/Marketing/EmailTemplateFactory.php` — factory for arch test compliance
- `database/migrations/tenant/2026_04_16_032058_create_email_templates_table.php` — tenant migration
- `app/Services/Email/EmailTemplateRenderer.php` — resolves custom template or returns null for fallback
- `resources/views/emails/custom-template.blade.php` — simple layout wrapper for custom body content
- `app/Filament/Pages/Settings/ManageEmailTemplates.php` — Pro-gated Filament page with edit slide-over and reset
- `resources/views/filament/pages/settings/manage-email-templates.blade.php` — card-based UI showing all 10 email types

### Files Modified (6)
- `app/Mail/Orders/OrderPlacedMail.php` — checks renderer for custom subject/body
- `app/Mail/Orders/OrderStatusMail.php` — maps 5 statuses to template types, checks renderer
- `app/Mail/Customers/ReviewRequestMail.php` — checks renderer
- `app/Mail/Customers/HappyBirthdayMail.php` — checks renderer
- `app/Mail/Customers/RepeatOrderReminderMail.php` — checks renderer
- `app/Mail/Customers/ProductAvailableMail.php` — checks renderer

### Tests Created (4 files, 71 tests)
- `tests/Unit/Enums/Marketing/EmailTemplateTypeTest.php` — 50 tests (enum labels, descriptions, placeholders, subjects)
- `tests/Unit/Services/Email/EmailTemplateRendererTest.php` — 6 tests (resolve, fallback, placeholder replacement)
- `tests/Integration/Mail/CustomEmailTemplateTest.php` — 10 tests (each Mailable with/without custom templates)
- `tests/Integration/Filament/Pages/Settings/ManageEmailTemplatesPageTest.php` — 5 tests (page data, status tracking, reset)

### Test Results
- Full suite: 3,292 tests, 7,141 assertions — all passing
- Pint: clean
- No regressions in existing mail tests

### Related Issue
- #31 — per-email toggle (future follow-up, not part of this implementation)
