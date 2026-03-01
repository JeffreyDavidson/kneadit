# KneadIt Launch Checklist

## Legal & Business
- [ ] File **Infinity Digital LLC** in Florida at sunbiz.org (~$125)
- [ ] Get EIN from IRS (irs.gov, free, instant)
- [ ] Open business bank account (separate from personal)
- [ ] Register "KneadIt" as DBA under Infinity Digital LLC (~$50)
- [ ] Business email set up (hello@getkneadit.app)

## Payments & Billing
- [ ] Create Stripe account under the LLC
- [ ] Set up Stripe products/prices (Starter $15, Growth $29, Pro $45 + founding rates)
- [ ] Integrate Laravel Cashier for subscriptions
- [ ] Configure 30-day free trial flow
- [ ] Set up Stripe webhooks (payment success/failure, cancellation)
- [ ] Stripe customer billing portal (self-service plan changes)

## Legal Pages
- [ ] Terms of Service (SaaS usage, data handling, liability limits)
- [ ] Privacy Policy (required by Stripe, GDPR/CCPA)
- [ ] Cookie Policy (if using analytics)
- [ ] Acceptable Use Policy (what bakers can/can't do)

## Platform (Tech)
- [ ] Multi-tenancy (Stancl Tenancy — database per tenant, subdomain routing)
- [ ] Onboarding wizard (bakery name, state, branding, first products)
- [ ] Tenant signup → Stripe checkout → provision tenant flow
- [ ] Email system (Resend — transactional + waitlist announcements)
- [ ] Waitlist capture on landing page (wire forms to save emails)

## Domain & Infrastructure
- [x] SSL on getkneadit.app
- [x] Cloudflare DNS
- [x] Production server ready (cold-moon)
- [ ] Wildcard subdomain for tenants (*.getkneadit.app)
- [ ] Wildcard SSL certificate
- [ ] Database backup strategy

## Marketing & Launch
- [ ] Finalize landing page content (in progress)
- [ ] Waitlist email collection (Resend or DB)
- [ ] Launch announcement email to waitlist
- [ ] Social media accounts (Instagram at minimum)
- [ ] Demo video or screenshots of actual admin panel
- [x] SEO basics (meta tags, og:image)

## Nice to Have (Post-Launch)
- [ ] Help docs / knowledge base
- [ ] In-app onboarding tour
- [ ] Referral program
- [ ] Status page (uptime monitoring)
- [ ] Chat support or contact form
