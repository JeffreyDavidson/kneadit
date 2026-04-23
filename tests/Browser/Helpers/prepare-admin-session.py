"""
Generate Playwright storageState files for the seeded fixture admins so
Pest browser tests can bypass the full login dance on every test.

Produces two separate sessions:
  1. Tenant admin against a tenant storefront (/admin on tenant subdomain)
  2. Central admin against the landlord panel (/admin on the central domain)

Also captures tenant fixture record IDs (order id, survey id) so
detail-page tests can reference them without querying the tenant DB.

Run by tests/Pest.php's authenticatedVisit() / fixtureId() helpers
automatically when the session/fixture-id files are missing or expired.
Can also be invoked manually after password changes or seeder re-runs:

    python3 tests/Browser/Helpers/prepare-admin-session.py

Outputs (all gitignored):
    tests/Browser/.admin-session.json         — tenant admin state
    tests/Browser/.admin-fixture-ids.json     — {"review_order_id": N, "survey_id": N}
    tests/Browser/.central-admin-session.json — central admin state

Prerequisites:
    php artisan tenants:provision-test-tenant
    php artisan db:seed --class="Database\\Seeders\\BrowserTestCentralFixtureSeeder"
"""
import json
import re
from pathlib import Path
from playwright.sync_api import sync_playwright

STOREFRONT_URL = 'http://browser-test.kneadit.test'
TENANT_ADMIN_EMAIL = 'browser-test-admin@kneadit.test'
TENANT_ADMIN_PASSWORD = 'browser-test-password'
REVIEW_ORDER_NUMBER = 'BROWSER-TEST-REVIEW'
SURVEY_TITLE = 'Browser Test Survey'

CENTRAL_URL = 'http://kneadit.test'
CENTRAL_ADMIN_EMAIL = 'browser-test-central@kneadit.test'
CENTRAL_ADMIN_PASSWORD = 'browser-test-password'

REPO_ROOT = Path(__file__).resolve().parents[3]
SESSION_OUT = REPO_ROOT / 'tests' / 'Browser' / '.admin-session.json'
IDS_OUT = REPO_ROOT / 'tests' / 'Browser' / '.admin-fixture-ids.json'
CENTRAL_SESSION_OUT = REPO_ROOT / 'tests' / 'Browser' / '.central-admin-session.json'


def find_first_view_id(page, list_path: str, search_term: str) -> int:
    """Navigate to a Filament resource index, search for a term, return the
    first matching row's record id parsed from its view-page link href."""
    page.goto(f'{STOREFRONT_URL}/admin/{list_path}?tableSearch={search_term}', wait_until='networkidle')
    page.wait_for_timeout(1500)
    hrefs = page.locator(f'a[href*="/admin/{list_path}/"]').evaluate_all('els => els.map(e => e.href)')
    for href in hrefs:
        match = re.search(rf'/admin/{re.escape(list_path)}/(\d+)', href)
        if match:
            return int(match.group(1))
    raise SystemExit(f'could not find {search_term} in /admin/{list_path}')


def login_and_save(browser, *, base_url: str, email: str, password: str, out_path: Path, kind: str):
    context = browser.new_context(base_url=base_url, ignore_https_errors=True)
    page = context.new_page()
    page.goto(f'{base_url}/admin/login', wait_until='networkidle')
    page.locator('input[type="email"]').fill(email)
    page.locator('input[type="password"]').fill(password)
    page.locator('button[type="submit"]').click()
    page.wait_for_timeout(5000)

    if 'login' in page.url:
        raise SystemExit(f'{kind} login failed — still on {page.url}. Did you run the fixture seeder?')

    context.storage_state(path=str(out_path))
    print(f'OK saved {out_path}')

    return context, page


with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)

    # Tenant admin session + fixture IDs
    _, tenant_page = login_and_save(
        browser,
        base_url=STOREFRONT_URL,
        email=TENANT_ADMIN_EMAIL,
        password=TENANT_ADMIN_PASSWORD,
        out_path=SESSION_OUT,
        kind='tenant',
    )

    review_order_id = find_first_view_id(tenant_page, 'orders', REVIEW_ORDER_NUMBER)
    survey_id = find_first_view_id(tenant_page, 'surveys', SURVEY_TITLE)

    IDS_OUT.write_text(json.dumps({
        'review_order_id': review_order_id,
        'survey_id': survey_id,
    }, indent=2))
    print(f'OK saved {IDS_OUT} (review_order_id={review_order_id}, survey_id={survey_id})')

    # Central admin session
    login_and_save(
        browser,
        base_url=CENTRAL_URL,
        email=CENTRAL_ADMIN_EMAIL,
        password=CENTRAL_ADMIN_PASSWORD,
        out_path=CENTRAL_SESSION_OUT,
        kind='central',
    )

    browser.close()
