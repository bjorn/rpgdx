// Uses exact-ID disposable fixtures and restores them even after a failed assertion.
const assert = require('node:assert/strict');
const { execFileSync } = require('node:child_process');
const { randomBytes } = require('node:crypto');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const base = process.env.RPGDX_TEST_URL || 'http://127.0.0.1:8083';
assert(['127.0.0.1', 'localhost'].includes(new URL(base).hostname), 'Use a local disposable site');
assert.equal(process.env.RPGDX_SMOKE_ALLOW_WRITES, '1', 'Opt into local fixture writes');
for (const key of ['RPGDX_TEST_USER', 'RPGDX_TEST_PASSWORD']) assert(process.env[key], `Missing ${key}`);
const fixtureManifest = process.env.RPGDX_CONTEST_FIXTURE_MANIFEST || `/tmp/rpgdx-contest-csrf-${randomBytes(12).toString('hex')}.json`;
function fixture(action) {
  return execFileSync('docker', ['compose', '-p', 'rpgdx-phpbb3', 'exec', '-T', '--user', 'www-data', '-e', 'RPGDX_SMOKE_ALLOW_WRITES=1', '-e', 'RPGDX_FIXTURE_MANIFEST', '-e', `RPGDX_CONTEST_FIXTURE_MANIFEST=${fixtureManifest}`, 'web', 'php', 'tests/contest-csrf-fixtures.php', action], { encoding: 'utf8' });
}
function state() { return JSON.parse(fixture('state')); }
async function healthyText(response) {
  const body = await response.text();
  assert(!/Fatal error:|Warning:|Deprecated:|Error during MySQL query/.test(body), body.slice(0, 2000));
  return body;
}
async function hidden(form) {
  return form.locator('input[type="hidden"]').evaluateAll(inputs => Object.fromEntries(inputs.map(input => [input.name, input.value])));
}
(async () => {
  let browser;
  let creationAttempted = false;
  try {
    creationAttempted = true;
    const fixtures = JSON.parse(fixture('create'));
    browser = await chromium.launch({ headless: true, executablePath: process.env.CHROMIUM_PATH, args: ['--no-sandbox'] });
    const context = await browser.newContext({ javaScriptEnabled: false });
    await context.route('**/*', route => ['127.0.0.1', 'localhost'].includes(new URL(route.request().url()).hostname) ? route.continue() : route.abort());
    const page = await context.newPage();
    await page.goto(new URL('/login.php', base).href);
    await page.locator('[name="username"]').fill(process.env.RPGDX_TEST_USER);
    await page.locator('[name="password"]').fill(process.env.RPGDX_TEST_PASSWORD);
    await page.waitForTimeout(1100);
    await Promise.all([page.waitForURL(url => url.pathname === '/index.php' || url.pathname === '/'), page.locator('input[name="login"]').click()]);
    assert((await page.locator('body').innerText()).includes(process.env.RPGDX_TEST_USER), 'Temporary member is logged in');
    async function formFor(kind, id) {
      await page.goto(new URL(`/showcontest.php?contest_id=${id}`, base).href);
      const form = page.locator(`form:has(input[name="${kind === 'voting' ? 'vote' : kind}"])`);
      assert.equal(await form.count(), 1, `${kind} form is visible without JavaScript`);
      assert.equal((await form.getAttribute('method')).toLowerCase(), 'post');
      const action = new URL(await form.getAttribute('action'), base);
      assert.equal(action.searchParams.has('action'), false, 'Mutation action is a POST field');
      const fields = await hidden(form);
      assert(fields.creation_time && fields.form_token, `${kind} has phpBB form credentials`);
      if (kind !== 'voting') assert.equal(fields.action, kind);
      return { form, action, fields };
    }
    async function rejectForged(kind, shown, extra, wrongToken) {
      const before = state();
      const valid = { ...shown.fields, ...extra };
      const url = new URL(shown.action);
      url.search = new URLSearchParams(valid).toString();
      assert.match(await healthyText(await context.request.get(url.href)), /require[s]? a submitted form/);
      const missing = { ...valid };
      delete missing.form_token;
      assert.match(await healthyText(await context.request.post(shown.action.href, { form: missing })), /Your form has expired/);
      assert.match(await healthyText(await context.request.post(shown.action.href, { form: { ...valid, ...wrongToken } })), /Your form has expired/);
      assert.deepEqual(state(), before, `${kind} forged submissions leave all fixture entries and votes unchanged`);
    }
    for (const theme of ['classic', 'modern']) {
      assert(fixtures.themes[theme], `Missing ${theme} theme`);
      await page.goto(new URL('/userpage.php', base).href);
      const preferences = page.locator('form:has(select[name="user_theme"])');
      const preferenceToken = await hidden(preferences);
      await preferences.locator('[name="user_theme"]').selectOption(String(fixtures.themes[theme]));
      await page.waitForTimeout(1100);
      await Promise.all([page.waitForLoadState('load'), preferences.locator('input[type="submit"]').click()]);
      const subscribe = await formFor('subscribe', fixtures.contests.open);
      await page.waitForTimeout(1100);
      await rejectForged('subscribe', subscribe, { project_id: String(fixtures.project) }, preferenceToken);
      const safe = { ...subscribe.fields, project_id: '0', contest_id: '0' };
      const conflicting = new URL(subscribe.action);
      conflicting.search = new URLSearchParams({ action: 'unsubscribe', entry_id: '1', contest_id: String(fixtures.contests.open), project_id: String(fixtures.project) }).toString();
      assert.match(await healthyText(await context.request.post(conflicting.href, { form: safe })), /valid project_id/);
      assert.equal(state().open.entries.length, 0, 'Query identifiers cannot override POST');
      await subscribe.form.locator('[name="project_id"]').selectOption(String(fixtures.project));
      await Promise.all([page.waitForLoadState('load'), subscribe.form.locator('input[name="subscribe"]').click()]);
      assert.equal(state().open.entries.length, 1, `${theme} valid subscription succeeds`);
      const unsubscribe = await formFor('unsubscribe', fixtures.contests.open);
      await page.waitForTimeout(1100);
      await rejectForged('unsubscribe', unsubscribe, {}, { creation_time: subscribe.fields.creation_time, form_token: subscribe.fields.form_token });
      await Promise.all([page.waitForLoadState('load'), unsubscribe.form.locator('input[name="unsubscribe"]').click()]);
      assert.equal(state().open.entries.length, 0, `${theme} valid unsubscription succeeds`);
      const voting = await formFor('voting', fixtures.contests[theme]);
      const choices = { [`categories[${fixtures.categories[theme]}]`]: String(fixtures.entries[theme]) };
      await page.waitForTimeout(1100);
      await rejectForged('voting', voting, choices, { creation_time: subscribe.fields.creation_time, form_token: subscribe.fields.form_token });
      await voting.form.locator('select').selectOption(String(fixtures.entries[theme]));
      await Promise.all([page.waitForLoadState('load'), voting.form.locator('input[name="vote"]').click()]);
      const votes = state()[theme].votes;
      assert.equal(votes.length, 1, `${theme} valid vote succeeds`);
      assert.equal(Number(votes[0].vote_entry), fixtures.entries[theme]);
    }
    console.log('PASS contest CSRF: both themes without JavaScript, GET/missing/wrong-purpose rejection without changes, POST precedence, valid subscribe/unsubscribe/voting');
  } finally {
    try {
      if (browser) await browser.close();
    } finally {
      if (creationAttempted) fixture('remove');
    }
  }
})().catch(error => { console.error(error); process.exitCode = 1; });
