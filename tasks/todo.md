# Unified Discount Ledger — Design 3

## Problem
Coupon and gift card discount handling is asymmetric:
- Gift card amount applied is invisible on the Order record
- Coupons have no audit trail (just a `used_count` counter)
- Gift card modifies `order.total` after it was already persisted
- Pipeline ordering creates a two-write problem

## Approach
Mirror the existing GiftCardTransaction pattern for coupons. Fix pipeline so both
discounts are calculated before persist and recorded after persist.

## Changes

### 1. Migrations
- [ ] Add `gift_card_id` (FK, nullable) and `gift_card_amount` (decimal, default 0) to orders
- [ ] Create `coupon_transactions` table mirroring `gift_card_transactions`

### 2. New Files
- [ ] `CouponTransactionType` enum (Usage, Reversal)
- [ ] `CouponTransaction` model + factory
- [ ] `RecordGiftCardRedemption` pipe (post-persist, calls existing RedeemGiftCard action)

### 3. Modified Files
- [ ] `Order` model — add gift_card_id, gift_card_amount to fillable/casts, add giftCard() and couponTransactions() relationships
- [ ] `Coupon` model — add transactions() hasMany
- [ ] `OrderPipelineData` — add giftCardAmount, giftCardId properties
- [ ] `ApplyGiftCard` pipe — rewrite to pre-persist calculation only (no DB writes)
- [ ] `ApplyCouponUsage` → rename to `RecordCouponUsage`, add CouponTransaction creation
- [ ] `PersistOrder` pipe — add gift_card_id and gift_card_amount to create()
- [ ] `CreateOrder` action — reorder pipeline

### 4. Pipeline: Before → After
```
BEFORE: CalculateOrderTotals → ValidateCapacity → ApplyCoupon → ResolveCustomer → PersistOrder → ApplyCouponUsage → ApplyGiftCard → PersistOrderItems

AFTER:  CalculateOrderTotals → ValidateCapacity → ApplyCoupon → ApplyGiftCard → ResolveCustomer → PersistOrder → RecordCouponUsage → RecordGiftCardRedemption → PersistOrderItems
```

### 5. Tests
- [ ] CouponTransaction model tests
- [ ] RecordCouponUsage pipe creates transaction record
- [ ] RecordGiftCardRedemption pipe calls RedeemGiftCard action
- [ ] ApplyGiftCard pipe calculates without DB writes
- [ ] Full pipeline integration test with both coupon + gift card

## Out of Scope
- Reversal logic on cancellation (future work, effect dispatcher hook exists)
- View/UI updates (invoice, order confirmation, Filament) — separate PR
- Controller consolidation
- Backfill migration for historical orders
