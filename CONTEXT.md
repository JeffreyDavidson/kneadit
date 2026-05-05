# KneadIt Context

KneadIt is a multi-tenant SaaS for cottage food bakers. Use these terms when planning, coding, and writing issues so agent work stays concise and consistent.

## Language

**Tenant**
A baker's isolated KneadIt workspace. Each tenant owns its storefront, settings, customers, products, orders, and connected services.
_Avoid_: account, shop, store, unless referring to UI copy or third-party wording.

**Baker**
The person or business using KneadIt to sell cottage food.
_Avoid_: vendor, merchant, seller, unless quoting payment-provider docs.

**Storefront**
The public customer-facing site for a tenant.
_Avoid_: frontend when discussing the product domain.

**Admin panel**
The Filament area where a baker manages products, orders, content, settings, and integrations.
_Avoid_: dashboard when the specific Filament admin surface matters.

**Customer**
A person buying from a baker's storefront.
_Avoid_: user unless referring to authentication records.

**Order**
A customer's purchase/request captured through a storefront. Orders may involve invoices, pickup/delivery details, and payment state.
_Avoid_: ticket, request.

**Product**
A sellable bakery item with pricing, description, photos, and availability.
_Avoid_: item when naming domain code unless it is a generic UI label.

**Settings**
Tenant-owned configuration that controls storefront identity, theme, payments, notifications, and operational preferences.
_Avoid_: config when the value is managed by the baker in the product.

**Connected Stripe account**
A baker's Stripe Connect account used to receive payments.
_Avoid_: platform account, except for Infinity Digital's Stripe platform.

**Custom domain**
A tenant-owned domain routed to their storefront.
_Avoid_: vanity URL when implementation or SSL behavior matters.

## Relationships

- A Tenant has one Baker/business identity.
- A Tenant owns one Storefront and one Admin panel.
- A Storefront creates Orders for Customers.
- Products belong to a Tenant and appear on that Tenant's Storefront.
- Settings shape both Admin panel behavior and Storefront presentation.
- Connected Stripe accounts belong to Bakers, while the platform belongs to Infinity Digital.

## Flagged Ambiguities

- "User" can mean authenticated admin user or storefront customer. Prefer **Baker** or **Customer** unless discussing auth tables.
- "Dashboard" can mean the whole Filament admin panel or the landing page inside it. Prefer **Admin panel** or **dashboard page**.
- "Store" can mean a Tenant, Storefront, or product catalog. Prefer the specific term.
