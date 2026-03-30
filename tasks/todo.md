# Controller Query Extraction + Architecture Improvements

## Work Item 1: Extract Queries from Controllers
- [ ] Add `ReviewQueryBuilder::statistics()` method
- [ ] Add `OrderQueryBuilder::forDeliveryOnDate()` method
- [ ] Add `OrderQueryBuilder::forCustomerEmail()` method
- [ ] Create `app/Queries/StorefrontStatsQuery.php`
- [ ] Create `app/Queries/DriverDeliveryQuery.php`
- [ ] Refactor ReviewsController
- [ ] Refactor AboutController + Hero component
- [ ] Refactor DriverController
- [ ] Refactor TrackingController
- [ ] Refactor LoyaltyController (deduplicate)
- [ ] Refactor GalleryController
- [ ] Refactor BlogFeedController
- [ ] Refactor StripeWebhookController (deduplicate)
- [ ] Refactor FavoriteController (Storefront)
- [ ] Refactor InvitationController (deduplicate)
- [ ] Create `tests/Arch/ControllerQueryTest.php`

## Work Item 2: Order Creation Pipeline
- [ ] Create `app/Pipes/Orders/OrderPipelineData.php`
- [ ] Create pipe stages (8 classes)
- [ ] Refactor `CreateOrder` action to use pipeline
- [ ] Write unit tests for each pipe
- [ ] Write integration test for full pipeline

## Work Item 3: Webhook Processing Pipeline
- [ ] Create `app/Pipes/Webhooks/WebhookData.php`
- [ ] Create pipe stages (4 classes)
- [ ] Refactor StripeWebhookController
- [ ] Refactor StripeConnectWebhookController

## Work Item 4: Form Request → DTO
- [ ] Add `toData()` to StoreOrderRequest
- [ ] Add `toData()` to StoreApiOrderRequest
- [ ] Add `toData()` to PurchaseGiftCardRequest
- [ ] Update 3 controllers

## Work Item 5: TenantSettings DTO
- [ ] Create `app/Services/Settings/TenantSettings.php`
- [ ] Register in service container
- [ ] Refactor 12+ storefront controllers
- [ ] Refactor view components

## Work Item 6: API Response Standardization
- [ ] Create `app/Http/Responses/ApiResponse.php`
- [ ] Update ~15 controllers

## Work Item 7: Event-Driven Emails
- [ ] Create PaymentFailed event + listener
- [ ] Create HealthCheckFailed event + listener
- [ ] Move MessageController Mail to existing event
- [ ] Create arch test: no Mail:: in controllers
