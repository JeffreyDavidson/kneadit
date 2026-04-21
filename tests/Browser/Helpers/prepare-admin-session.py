"""
Generate a Playwright storageState for the seeded fixture admin so Pest
browser tests can bypass the full login dance on every test.

Also captures fixture record IDs (order id, survey id) so detail-page
tests can reference them without querying the tenant DB.

Run ONCE per dev machine (or after the fixture admin password changes, or
if the Laravel session has invalidated, or if the seeder was re-run with
different IDs):

    python3 tests/Browser/Helpers/prepare-admin-session.py

Outputs (both gitignored):
    tests/Browser/.admin-session.json   — Playwright storage state
    tests/Browser/.admin-fixture-ids.json — {"review_order_id": N, "survey_id": N}

Prerequisite: BrowserTestFixtureSeeder has been run against the target
tenant (see database/seeders/BrowserTestFixtureSeeder.php).
"""
import json
import re
from pathlib import Path
from playwright.sync_api import sync_playwright

STOREFRONT_URL = 'http://sweet-flour-studio.kneadit.test'
ADMIN_EMAIL = 'browser-test-admin@kneadit.test'
ADMIN_PASSWORD = 'browser-test-password'
REVIEW_ORDER_NUMBER = 'BROWSER-TEST-REVIEW'
SURVEY_TITLE = 'Browser Test Survey'

REPO_ROOT = Path(__file__).resolve().parents[3]
SESSION_OUT = REPO_ROOT / 'tests' / 'Browser' / '.admin-session.json'
IDS_OUT = REPO_ROOT / 'tests' / 'Browser' / '.admin-fixture-ids.json'


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

    context.storage_state(path=str(SESSION_OUT))
    print(f'OK saved {SESSION_OUT}')

    review_order_id = find_first_view_id(page, 'orders', REVIEW_ORDER_NUMBER)
    survey_id = find_first_view_id(page, 'surveys', SURVEY_TITLE)

    IDS_OUT.write_text(json.dumps({
        'review_order_id': review_order_id,
        'survey_id': survey_id,
    }, indent=2))
    print(f'OK saved {IDS_OUT} (review_order_id={review_order_id}, survey_id={survey_id})')

    browser.close()
