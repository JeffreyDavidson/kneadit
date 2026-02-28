# CLAUDE.md — KneadIt

## Project Overview
Landing page for KneadIt — a SaaS platform for cottage food bakers. Currently a static HTML site collecting waitlist signups. The actual app is in the `bakeryonbiscotto` repo (being converted to multi-tenant SaaS).

## Tech Stack (Landing Page)
- Single `index.html` — no build tools, no frameworks
- Inline CSS and JavaScript
- Fonts: Playfair Display + DM Sans (Google Fonts CDN)
- Deployed to Forge (cold-moon server), Cloudflare DNS

## Git Workflow
- Single branch: `main` (auto-deploys via Forge)
- Commit format: `type: description`
- Git email: `jdavidsonwebdev@gmail.com`
- Remote: `git@github-kneadit:JeffreyDavidson/kneadit.git`

## Domain
- getkneadit.app (Cloudflare DNS → 137.184.194.56)

## Pricing (3 tiers, all with 30-day free trial)
- Starter: $9/mo founding ($15 regular) — orders, storefront, customer directory
- Growth: $19/mo founding ($29 regular) — + invoicing, financial dashboard, recipes
- Pro: $29/mo founding ($45 regular) — + analytics, automation, custom branding

## Color Palette (CSS variables)
- `--warm-black` #1c1410, `--espresso` #2a1f18, `--walnut` #4a3728
- `--cinnamon` #8b6844, `--honey` #d4920c, `--golden` #e8b04a
- `--butter` #f5d88e, `--flour` #faf4e8, `--cream` #fef9ef

## Key Interactive Features
- Hero: auto-typing bakery names that update a storefront preview
- Baker's Day timeline: 6 moments showing KneadIt in action
- Count-up number animations
- Flour particle effects
- FAQ accordion

## Business
- Parent company: Infinity Digital LLC (filing pending)
- KneadIt will be a DBA under this LLC
- Billing: Stripe + Laravel Cashier (planned)
