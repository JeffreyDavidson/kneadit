"""
Generate a Playwright storageState for the seeded fixture admin so Pest
browser tests can bypass the full login dance on every test.

Run ONCE per dev machine (or after the fixture admin password changes, or
if the Laravel session has invalidated):

    python3 tests/Browser/Helpers/prepare-admin-session.py

The resulting tests/Browser/.admin-session.json is gitignored. Pest tests
load it via the authenticatedVisit() helper in tests/Pest.php.

Prerequisite: BrowserTestFixtureSeeder has been run against the target
tenant (see database/seeders/BrowserTestFixtureSeeder.php).
"""
from pathlib import Path
from playwright.sync_api import sync_playwright

STOREFRONT_URL = 'http://sweet-flour-studio.kneadit.test'
ADMIN_EMAIL = 'browser-test-admin@kneadit.test'
ADMIN_PASSWORD = 'browser-test-password'

REPO_ROOT = Path(__file__).resolve().parents[3]
OUTPUT = REPO_ROOT / 'tests' / 'Browser' / '.admin-session.json'

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    context = browser.new_context(base_url=STOREFRONT_URL, ignore_https_errors=True)
    page = context.new_page()
    page.goto(f'{STOREFRONT_URL}/admin/login', wait_until='networkidle')
    page.locator('input[type="email"]').fill(ADMIN_EMAIL)
    page.locator('input[type="password"]').fill(ADMIN_PASSWORD)
    page.locator('button[type="submit"]').click()
    page.wait_for_timeout(5000)

    if 'login' in page.url:
        raise SystemExit(f'login failed — still on {page.url}. Did you run BrowserTestFixtureSeeder?')

    context.storage_state(path=str(OUTPUT))
    print(f'OK saved {OUTPUT}')
    browser.close()
