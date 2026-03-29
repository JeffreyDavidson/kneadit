# Pest PHP v3 to v4 Upgrade Research

## Summary of Findings

### Current State (Our Project)
- **Pest**: 3.8.6
- **PHPUnit**: 11.5.50
- **Collision**: 8.9.1
- **PHP**: 8.4.19
- **pest-plugin-laravel**: 3.2.0
- **pest-plugin-arch**: 3.1.1
- **pest-plugin-type-coverage**: 3.6.1
- **pest-plugin-mutate**: 3.0.5 (transitive)
- **rector-pest (mrpunyapal/rector-pest)**: installed, has `PestSetList::PEST_40` and `PestLevelSetList::UP_TO_PEST_40` ready

---

## 1. Official Upgrade Guide

Source: https://pestphp.com/docs/upgrade-guide

**Estimated upgrade time: 2 minutes**

### Dependency Changes Required

| Package | Current | Target |
|---------|---------|--------|
| `pestphp/pest` | `^3.8` | `^4.0` |
| `pestphp/pest-plugin-laravel` | `^3.2` | `^4.0` |
| `pestphp/pest-plugin-arch` | `^3.1` | `^4.0` |
| `pestphp/pest-plugin-type-coverage` | `^3.6` | `^4.0` |
| `phpunit/phpunit` | `^11.5.3` | `^12.0` |
| `nunomaduro/collision` | `^8.6` | `^8.6` (no change needed, v8.x supports PHPUnit 12) |

---

## 2. Breaking Changes (v3 -> v4)

### A. PHP 8.3+ Required
- **Impact on us: NONE** — we run PHP 8.4.19

### B. PHPUnit 12 (up from 11)
- **Impact on us: MEDIUM** — PHPUnit 12 is a major version bump. Key PHPUnit 12 breaking changes:
  1. **DocBlock annotations removed** — `@test`, `@depends`, `@dataProvider`, `@group`, `@covers` annotations no longer work. Must use PHP 8 attributes instead.
     - **Impact on us: NONE** — we use Pest syntax, not PHPUnit class-based tests. Verified no PHPUnit annotations in our test files.
  2. **`createStub()` can no longer configure expectations** — must use `createMock()` instead.
     - **Impact on us: NONE** — we don't use `createStub()`.
  3. **`getMockForAbstractClass()` and `getMockForTrait()` removed**.
     - **Impact on us: NONE** — we don't use these.
  4. **Data providers must be static with return types, test classes should be final**.
     - **Impact on us: NONE** — Pest handles this internally.

### C. Snapshot Testing Name Generation Changed
- If using snapshot testing, run `./vendor/bin/pest --update-snapshots` after upgrading.
- **Impact on us: NONE** — we don't use snapshot testing.

### D. Archived Plugins (removed/no longer maintained)
- `pestphp/pest-plugin-watch` — archived
- `pestphp/pest-plugin-faker` — archived
- **Impact on us: NONE** — we don't use either of these.

---

## 3. New Features in Pest v4

1. **Browser Testing (Playwright-powered)** — First-class browser testing with Laravel integration, parallel runs, device emulation, light/dark mode toggles. Requires `npm install playwright@latest && npx playwright install`.
2. **Smoke Testing** — One-liner smoke tests that crawl routes checking for JS errors: `visit(['/', '/about'])->assertNoSmoke()`.
3. **Visual Regression Testing** — `assertScreenshotMatches()` for pixel-level UI comparison.
4. **Test Sharding** — Distribute tests across CI machines with `--shard=1/4` combined with `--parallel`.
5. **Faster Type Coverage** — ~2x faster on first run, near-instant on subsequent runs, with sharding support.
6. **Environment-conditional tests** — `skipLocally()` / `skipOnCi()`.
7. **New Architecture Expectations** — `toBeSlug`, `not->toHaveSuspiciousCharacters()`.
8. **Profanity Checker Plugin** — opt-in via `--profanity`.

---

## 4. Composer Commands to Upgrade

```bash
# Update all pest packages + PHPUnit in one command
composer require pestphp/pest:^4.0 \
  pestphp/pest-plugin-laravel:^4.0 \
  pestphp/pest-plugin-arch:^4.0 \
  pestphp/pest-plugin-type-coverage:^4.0 \
  phpunit/phpunit:^12.0 \
  --dev --with-all-dependencies

# Optional: if using browser testing later
npm install playwright@latest
npx playwright install
```

---

## 5. Known Issues

- **Browser plugin navigation crash** — Any navigation event (redirect, `navigate()`, `goto()`) can cause silent PHP crash with no output. Only relevant if using browser testing.
- **`toHaveSuspiciousCharacters`** — Removed from PHP preset as it requires an extension that may not be available.
- **`App\Http` arch test false positive** — Was incorrectly flagged in Laravel providers (fixed in recent patches).
- Multiple open bugs on GitHub as of March 2026, but most relate to the new browser testing plugin.

---

## 6. Compatibility Matrix

| Requirement | Needed | We Have | Compatible? |
|-------------|--------|---------|-------------|
| PHP | >= 8.3 | 8.4.19 | YES |
| Laravel | 11 or 12 | 12 | YES |
| PHPUnit | 12.x | 11.5.50 (will upgrade) | YES (after upgrade) |
| Collision | 8.x | 8.9.1 | YES (no change needed) |

---

## 7. pest-plugin-laravel

Yes, it needs to be updated from `^3.2` to `^4.0`. All Pest-maintained plugins must match the major version.

---

## 8. rector-pest (mrpunyapal/rector-pest)

**Yes, it already supports Pest 4.** Our installed version has:
- `PestSetList::PEST_40` — Pest 4-specific migration rules
- `PestLevelSetList::UP_TO_PEST_40` — Cumulative level set
- `PestSetList::PEST_CODE_QUALITY` and `PestSetList::PEST_CHAIN` — already in our rector.php

The PEST_40 set is currently minimal (the comment says "Rules will be added as migration patterns emerge") because Pest v4 has very few code-level breaking changes — it's primarily a dependency bump.

After upgrading, we could optionally add `PestLevelSetList::UP_TO_PEST_40` to our rector.php config.

---

## Risk Assessment

**LOW RISK upgrade.** Reasons:
1. We don't use any deprecated features (no snapshots, no archived plugins, no PHPUnit annotations)
2. We don't use `createStub()`, `getMockForAbstractClass()`, or `getMockForTrait()`
3. Our PHP version exceeds the minimum
4. Collision already supports PHPUnit 12
5. The main breaking change (PHPUnit 12) doesn't affect Pest-syntax tests
6. rector-pest is ready for Pest 4

**The only real risk is indirect PHPUnit 12 behavioral changes** that could subtly affect test execution. The safest approach is to run the full test suite after upgrading and fix any failures.
