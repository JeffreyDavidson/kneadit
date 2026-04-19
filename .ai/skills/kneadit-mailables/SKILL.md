---
name: kneadit-mailables
description: "KneadIt-specific mailable patterns including the unified OrderStatusMail, birthday coupon generation, and email-related domain rules. Activate when editing or creating mailables, working under app/Mail/, working with order status emails, birthday emails, or when the user mentions OrderStatusMail, BirthdayService, birthday coupons, BDAY codes, status emails, or order email templates. Covers why one mailable handles all order statuses (resolved via match), how to add a new status email, deterministic birthday coupon code generation, and the separation between birthday program and birthday coupon toggles."
---

# KneadIt Mailables

## Order Status Emails

- Order status emails use a single `OrderStatusMail($order, OrderStatus $status)` — subject and view are resolved from the status via a `match` expression. Do NOT create individual mailable classes per status.
- To add a new order status email: add a case to `OrderStatusMail::resolveSubject()` and create the Blade view at `emails.orders.order-{status}`.

## Birthday Coupons

- `BirthdayService::findOrCreateBirthdayCoupon()` generates deterministic codes (`BDAY-{customer_id}-{year}`) using `firstOrCreate` — safe to retry without creating duplicates.
- The birthday program toggle (`birthdayProgramEnabled`) and coupon toggle (`birthdayCouponEnabled`) are separate — the program can send a birthday email without a coupon.
