const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');

let target = new URL(process.env.RPGDX_TEST_URL || 'http://localhost:8083');
assert.equal(target.protocol, 'http:', 'Forum checks require local HTTP');
assert(['127.0.0.1', 'localhost'].includes(target.hostname), 'Forum checks require localhost');
assert(!target.username && !target.password, 'Credentials belong in environment variables');
const port = target.port || '80';
const output = process.env.RPGDX_ARTIFACT_DIR || path.join(os.tmpdir(), 'rpgdx-forum-qa');
const browserErrors = [];

function local(url) {
    return url.protocol === 'http:' && ['127.0.0.1', 'localhost'].includes(url.hostname) && (url.port || '80') === port;
}

async function launch() {
    fs.mkdirSync(output, { recursive: true });
    const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
    return chromium.launch({ executablePath: process.env.CHROMIUM_PATH, headless: true });
}

async function context(browser, viewport = { width: 1280, height: 1000 }) {
    const result = await browser.newContext({ viewport, serviceWorkers: 'block' });
    // Both loopback names are allowed because phpBB can canonicalize redirects.
    await result.route('**/*', route => local(new URL(route.request().url())) ? route.continue() : route.abort());
    result.on('page', page => page.on('pageerror', error => browserErrors.push(error.message)));
    return result;
}

async function healthy(page) {
    const body = await page.locator('body').innerText();
    assert(!/Fatal error:|Warning:|Deprecated:|Error during MySQL query/.test(body), body.slice(0, 1500));
}

async function visit(page, relative) {
    const url = new URL(relative, target);
    assert(local(url), 'Navigation must stay on the local test board');
    await page.goto(url.href, { waitUntil: 'networkidle' });
    await healthy(page);
}

async function submit(page, selector) {
    // Stock phpBB rejects forms submitted in their creation second.
    await page.waitForTimeout(1100);
    await page.locator(selector).click();
    await page.waitForLoadState('networkidle');
    await healthy(page);
}

async function login(page, username, password) {
    for (let attempt = 0; attempt < 2; attempt++) {
        const originalOrigin = target.origin;
        await visit(page, '/forums/ucp.php?mode=login');
        await page.locator('#username').fill(username);
        await page.locator('#password').fill(password);
        await submit(page, 'input[name="login"]');
        await page.waitForURL(url => url.pathname.endsWith('/index.php'), { timeout: 15000 });
        await page.waitForLoadState('networkidle');
        const canonical = new URL(page.url());
        assert(local(canonical), 'Login redirect must remain local');
        target = new URL(canonical.origin);
        if (canonical.origin === originalOrigin && await page.locator('#username_logged_in').count()) return;
    }
    assert.fail('Forum login did not persist after the local canonical redirect');
}

async function capture(page, name) {
    const file = path.join(output, `${name}.png`);
    await page.screenshot({ path: file, fullPage: true });
    const layout = await page.evaluate(() => ({
        width: innerWidth,
        documentWidth: document.documentElement.scrollWidth,
        brokenLocalImages: [...document.images].filter(image => image.complete && !image.naturalWidth && new URL(image.src).origin === location.origin).map(image => image.getAttribute('src')),
    }));
    assert(layout.documentWidth <= layout.width, `${name} overflows horizontally`);
    return { name, file, title: await page.title(), ...layout };
}

function report(name, data) {
    fs.writeFileSync(path.join(output, `${name}.json`), JSON.stringify(data, null, 2) + '\n');
}

function credentials() {
    for (const key of ['RPGDX_TEST_USER', 'RPGDX_TEST_PASSWORD', 'RPGDX_TEST_ADMIN', 'RPGDX_TEST_ADMIN_PASSWORD']) {
        assert(process.env[key], `Missing ${key}`);
    }
}

module.exports = { assert, browserErrors, capture, context, credentials, healthy, launch, login, output, report, submit, visit };
