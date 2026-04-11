# Test Coverage Gap Analysis

**Current coverage: 71.6%** (1,722 tests, 4,383 assertions)
**Type coverage: 100%**

## Priority Tiers

Business logic and data integrity matter most. Admin UI matters least.

---

## Tier 1: Core Business Logic — Actions & Services (0% coverage)

These are write operations and domain services with zero tests. Highest risk.

### Actions (0% coverage)
- [ ] `Actions/Customers/CreateBirthdayCoupon` — 0%
- [ ] `Actions/Orders/AwardLoyaltyPoints` — 0%
- [ ] `Actions/Stripe/InitiateStripeConnect` — 0% (only instantiation test)

### Services (0% coverage)
- [ ] `Services/Engagement/Engagements/RepeatOrderReminderEngagement` — 0%
- [ ] `Services/Engagement/Engagements/ReviewRequestEngagement` — 0%
- [ ] `Services/Tenants/ChurnAlertService` — 0%

### Services (< 50% coverage)
- [ ] `Services/Engagement/EngagementDispatcher` — 21.2%
- [ ] `Services/Engagement/Engagements/BirthdayDiscountEngagement` — 4.5%
- [ ] `Services/Engagement/Engagements/BirthdayEmailEngagement` — 4.8%
- [ ] `Services/Analytics/PageViewTracker` — 13.6%
- [ ] `Services/Customers/BirthdayCalculator` — 11.1%
- [ ] `Services/Tenants/TenantUsageService` — 11.9%
- [ ] `Services/Tenants/TenantHealthService` — 12.6%
- [ ] `Services/Tenants/TenancyManager` — 45.5%
- [ ] `Services/Settings/TenantSettingsRegistry` — 38.5%
- [ ] `Services/Platform/ForgeService` — 4.8%
- [ ] `Services/Stripe/StripeCheckoutService` — 6.3%
- [ ] `Services/Financial/TaxCsvExporter` — 46.2%
- [ ] `Services/Production/PrepScheduleService` — 47.3%
- [ ] `Services/Analytics/ProductTrendsService` — 48.6%

---

## Tier 6: Filament Resource Coverage

### Tenant Resources - New Test Files
- [x] Create CateringInquiryResourceTest (24 tests)

### Tenant Resources - Add Tests to Existing Files
- [x] EmailCampaignResourceTest - add create, edit, table columns, global search (+12 tests)
- [x] GiftCardResourceTest - add ViewGiftCard page test, global search (+4 tests)
- [x] OrderResourceTest - add global search coverage (+4 tests)
- [x] BlogPostResourceTest - add table columns, search, filter, sort, global search (+8 tests)
- [x] SocialPostResourceTest - add navigation badge, filter by platform, global search (+7 tests)
- [x] WaitlistEntryResourceTest - add navigation badge, sort, global search (+6 tests)
- [x] SurveyResourceTest - add ViewSurvey page, sort, global search (+5 tests)
- [x] CustomerResourceTest - add global search coverage (+3 tests)
- [x] ProductResourceTest - add global search coverage (+4 tests)
- [x] IngredientResourceTest - add global search coverage (+3 tests)
- [x] LoyaltyRewardResourceTest - add global search coverage (+3 tests)
- [x] CouponResourceTest - add global search coverage (+3 tests)
- [x] ContactMessageResourceTest - add global search coverage (+3 tests)
- [x] ReviewResourceTest - add global search coverage (+4 tests)
- [x] RecipeResourceTest - add sort, global search coverage (+4 tests)

### Central Resources
- [x] CentralViewPagesTest - add ViewMessage, ticket reply, ticket status, tenant stats (+6 tests)
- [x] Create CentralTenantResourceTest (12 tests)
- [x] Create CentralSupportTicketResourceTest (4 tests)
- [x] Create CentralMessageResourceTest (9 tests)

### Finish
- [x] Run Pint
- [x] Run tests (472 passed, 1490 assertions)
- [x] Add review section

## Review
Added ~122 new Filament Resource feature tests across 22 files (3 new, 19 modified).

Coverage areas exercised:
- Global search methods (getGloballySearchableAttributes, getGlobalSearchResultTitle, getGlobalSearchResultDetails, getGlobalSearchEloquentQuery)
- Navigation badges (getNavigationBadge, getNavigationBadgeColor) for SocialPosts, WaitlistEntries, SupportTickets, Messages
- View pages: ViewGiftCard, ViewSurvey, ViewMessage (render + mark as read + reply + thread)
- View ticket page: reply, status update, resolve with timestamp
- Tenant stats on ViewTenant page
- Form schemas exercised through create/edit flows (CateringInquiry, EmailCampaign full CRUD)
- Table features: column rendering, search, sort, filter for previously untested resources
- Fixed Pest.php support_tickets table schema to include resolved_at and admin_notes columns

Note: SupportTicketResource table tests are limited because the SupportTicketsTable has a type mismatch bug (color closures type-hint `string $state` but receive SupportTicketStatus enum). This is a pre-existing issue.

---

## Tier 7: Filament Pages & Widgets Coverage

### Central Pages
- [x] FeatureUsage (2%) -> 17 tests
- [x] DataExport (9%) -> 9 tests
- [x] TenantComparison (10%) -> 17 tests
- [x] OnboardingTracker (24%) -> 13 tests

### Tenant Pages - Analytics
- [x] SurveyResults (21%) -> 6 tests
- [x] ReportsCenter (25%) -> 11 tests
- [x] ProductTrends (54%) -> 9 tests
- [x] StorefrontAnalytics (74%) -> 10 tests

### Tenant Pages - Operations
- [x] StaffManagement (23%) -> 9 tests
- [x] HolidayPlanningCalendar (35%) -> 12 tests
- [x] WeeklyPrepPlanner (50%) -> 8 tests
- [x] DeliveryRoutePlanner (53%) -> 6 tests
- [x] OrderCalendar (59%) -> 14 tests
- [x] SeasonalItems (60%) -> 12 tests

### Tenant Pages - Tools
- [x] InstagramCaptionGenerator (16%) -> 11 tests
- [x] PriceSuggestionTool (17%) -> 14 tests
- [x] ShoppingListGenerator (27%) -> 8 tests
- [x] ThemeSelector (29%) -> 3 tests
- [x] SmartShoppingList (39%) -> 7 tests
- [x] RecipeCostCalculator (41%) -> 9 tests
- [x] DescriptionGenerator (44%) -> 9 tests
- [x] ProductImportExport (49%) -> 7 tests
- [x] PricingEngine (51%) -> 10 tests

### Tenant Pages - Settings/Platform
- [x] CustomDomain (23%) -> 6 tests
- [x] HomepageBuilder (42%) -> 13 tests
- [x] Messages (18%) -> 10 tests
- [x] OnboardingSteps (0-20%) -> 6 tests (CompleteStep + PreviewStep)
- [x] ActivityLogPage (43%) -> 17 tests

### Widgets
- [x] UpcomingHolidayWidget (15%) -> 4 tests
- [x] InboxWidget (33%) -> 1 test
- [x] BirthdayWidget (40%) -> 6 tests
- [x] UpcomingOrdersWidget (41%) -> 6 tests
- [x] OnboardingProgress (27%) -> 8 tests

### Review
Added 296 new tests across 29 new test files (380 total Integration/Filament tests including pre-existing).
All 380 Integration/Filament tests pass. All 540 Feature/Filament tests pass (no regressions).

Test approach: Direct instantiation of page/widget classes and method calls, matching the existing Integration test pattern. Protected methods accessed via ReflectionMethod where needed. RefreshDatabase used only for tests needing main-migration tables (holidays). Central test setup used for pages/widgets that depend on central-connection models (PlatformMessage).
