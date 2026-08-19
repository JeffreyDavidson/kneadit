import { writeFile } from "node:fs/promises";
import { fileURLToPath } from "node:url";
import path from "node:path";
import { chromium } from "playwright";

const storefrontUrl =
    process.env.BROWSER_TEST_STOREFRONT_URL ??
    "http://browser-test.kneadit.test";
const centralUrl =
    process.env.BROWSER_TEST_CENTRAL_URL ?? "http://kneadit.test";

const tenantAdminEmail = "browser-test-admin@kneadit.test";
const tenantAdminPassword = "browser-test-password";
const centralAdminEmail = "browser-test-central@kneadit.test";
const centralAdminPassword = "browser-test-password";
const reviewOrderNumber = "BROWSER-TEST-REVIEW";
const surveyTitle = "Browser Test Survey";

const repositoryRoot = path.resolve(
    path.dirname(fileURLToPath(import.meta.url)),
    "../../..",
);
const sessionOutput = path.join(
    repositoryRoot,
    "tests/Browser/.admin-session.json",
);
const fixtureIdsOutput = path.join(
    repositoryRoot,
    "tests/Browser/.admin-fixture-ids.json",
);
const centralSessionOutput = path.join(
    repositoryRoot,
    "tests/Browser/.central-admin-session.json",
);

async function findFirstViewId(page, listPath, searchTerm) {
    await page.goto(
        `${storefrontUrl}/admin/${listPath}?tableSearch=${encodeURIComponent(searchTerm)}`,
        {
            waitUntil: "networkidle",
        },
    );

    const hrefs = await page
        .locator(`a[href*="/admin/${listPath}/"]`)
        .evaluateAll((elements) => elements.map((element) => element.href));

    for (const href of hrefs) {
        const match = href.match(new RegExp(`/admin/${listPath}/(\\d+)`));

        if (match !== null) {
            return Number.parseInt(match[1], 10);
        }
    }

    throw new Error(`Could not find ${searchTerm} in /admin/${listPath}.`);
}

async function loginAndSave(
    browser,
    { baseUrl, email, password, output, kind },
) {
    const context = await browser.newContext({
        baseURL: baseUrl,
        ignoreHTTPSErrors: true,
    });
    const page = await context.newPage();

    await page.goto("/admin/login", { waitUntil: "networkidle" });
    await page.locator('input[type="email"]').fill(email);
    await page.locator('input[type="password"]').fill(password);
    await page.locator('button[type="submit"]').click();
    await page.waitForURL((url) => !url.pathname.includes("/login"));

    if (page.url().includes("/login")) {
        throw new Error(
            `${kind} login failed. Did you run the fixture seeder?`,
        );
    }

    await context.storageState({ path: output });
    process.stdout.write(`OK saved ${output}\n`);

    return { context, page };
}

const browser = await chromium.launch({ headless: true });

try {
    const tenant = await loginAndSave(browser, {
        baseUrl: storefrontUrl,
        email: tenantAdminEmail,
        password: tenantAdminPassword,
        output: sessionOutput,
        kind: "Tenant",
    });

    const reviewOrderId = await findFirstViewId(
        tenant.page,
        "orders",
        reviewOrderNumber,
    );
    const surveyId = await findFirstViewId(tenant.page, "surveys", surveyTitle);

    await writeFile(
        fixtureIdsOutput,
        `${JSON.stringify({ review_order_id: reviewOrderId, survey_id: surveyId }, null, 2)}\n`,
    );
    process.stdout.write(`OK saved ${fixtureIdsOutput}\n`);

    await tenant.context.close();

    const central = await loginAndSave(browser, {
        baseUrl: centralUrl,
        email: centralAdminEmail,
        password: centralAdminPassword,
        output: centralSessionOutput,
        kind: "Central",
    });

    await central.context.close();
} finally {
    await browser.close();
}
