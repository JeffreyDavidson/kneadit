# Refactoring Plan — Scalability & Code Quality

## Phase 1: Enum Casts & Quick Wins
- [ ] Cast StaffInvitation.role → UserRole enum
- [ ] Cast Tenant.plan → SubscriptionTier enum
- [ ] Fix Order $attributes to use enum values
- [ ] Create StockAdjustmentType enum
- [ ] Create GiftCardTransactionType enum
- [ ] Create AnnouncementType enum
- [ ] Create BlogPostCategory enum
- [ ] Create SupportReplyAuthorType enum

## Phase 2: Query Objects & ProductQueryBuilder
- [ ] Create ProductQueryBuilder with active(), featured(), availableForOrder()
- [ ] Create app/Queries/RevenueQuery (4 files duplicated)
- [ ] Create app/Queries/DailyRevenueQuery (3 files)
- [ ] Create app/Queries/AtRiskCustomersQuery (4 files)
- [ ] Create app/Queries/ProductSalesQuery (3 files)
- [ ] Consolidate FinancialCalculator + FinancialReport (use existing builders)

## Phase 3: DTOs
- [ ] CreateOrderData DTO (12+ keys)
- [ ] CouponValidationResult DTO
- [ ] GiftCardRedemptionResult DTO
- [ ] CreateGiftCardData DTO
- [ ] FinancialSummary DTO
- [ ] PricingResult DTO

## Phase 4: Action Extraction
- [ ] QuickOrder::createOrder() → delegate to CreateOrder action
- [ ] GiftCardService write methods → Action classes
- [ ] Onboarding page save steps → per-step Actions
- [ ] PricingEngine calculation → PricingCalculator service
- [ ] Webhook handler logic → Action classes

## Phase 5: Service Splitting
- [ ] TenantHealthService → TenantUsageService + ChurnAlertService + TenantHealthService
- [ ] ProfitAnalysisService → memoize getProductAnalysis()

## Phase 6: Value Objects & Custom Casts (lower priority)
- [ ] Money value object (14 models, 20+ columns)
- [ ] DateRange expansion (isActive() for seasonal/announcement/coupon)
- [ ] PhoneNumber cast (4 models)
- [ ] Address value object (Customer only)
