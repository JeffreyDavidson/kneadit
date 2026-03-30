# Refactoring Plan — Scalability & Code Quality

## Phase 1: Enum Casts & Quick Wins
- [x] Cast StaffInvitation.role → UserRole enum
- [~] Cast Tenant.plan → SubscriptionTier enum (skipped — Stancl Tenancy incompatible)
- [x] Fix Order $attributes to use enum values (already uses enum cases)
- [x] Create StockAdjustmentType enum
- [x] Create GiftCardTransactionType enum
- [x] Create AnnouncementType enum
- [x] Create BlogPostCategory enum
- [x] Create SupportReplyAuthorType enum

## Phase 2: Query Objects & ProductQueryBuilder
- [x] Create ProductQueryBuilder with active(), featured(), inSeason()
- [x] Create app/Queries/RevenueQuery (4 files duplicated)
- [x] Create app/Queries/DailyRevenueQuery (already in RevenueQuery::dailyBreakdown(), consolidated RevenueChartWidget)
- [x] Create app/Queries/AtRiskCustomersQuery (4 files)
- [x] Create app/Queries/ProductSalesQuery (3 files)
- [x] Consolidate FinancialCalculator + FinancialReport (FinancialReport now delegates to FinancialCalculator)

## Phase 3: DTOs
- [x] CreateOrderData DTO (12+ keys)
- [x] CouponValidationResult DTO
- [x] GiftCardRedemptionResult DTO
- [x] CreateGiftCardData DTO
- [x] FinancialSummary DTO
- [~] PricingResult DTO (skipped — Livewire serialization prevents DTO as public property, array is fine)

## Phase 4: Action Extraction
- [x] QuickOrder::createOrder() → delegate to CreateQuickOrder action
- [x] GiftCardService write methods → Action classes
- [~] Onboarding page save steps → per-step Actions (skipped — steps are simple settings() calls, extraction would over-engineer)
- [x] PricingEngine calculation → PricingCalculator service
- [x] Webhook handler logic → Action classes

## Phase 5: Service Splitting
- [x] TenantHealthService → TenantUsageService + ChurnAlertService + TenantHealthService
- [x] ProfitAnalysisService → memoize getProductAnalysis() (already uses once())

## Phase 6: Value Objects & Custom Casts
- [x] Money value object (14 models, 20+ columns)
- [x] DateRange expansion (isActive(), contains())
- [x] PhoneNumber cast (4 models)
- [x] Address value object (Customer only)

## Review — FilaCheck Progress
- Started: 27 passed, 8 failed, 155 warnings
- Current: 30 passed, 5 failed, 70 warnings
- Resolved: deprecated-test-methods, enum-missing-filament-interfaces, custom-theme-needed
- Remaining: action-missing-authorization (page-level auth), string-icon-instead-of-enum (intentional), widget table filters/searchable (N/A)
