// Run against a disposable local migration copy with two temporary phpBB users.
const assert = require('node:assert/strict');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const base = process.env.RPGDX_TEST_URL || 'http://127.0.0.1:8083';
assert(['127.0.0.1', 'localhost'].includes(new URL(base).hostname), 'Smoke writes require localhost');
assert.equal(process.env.RPGDX_SMOKE_ALLOW_WRITES, '1', 'Set RPGDX_SMOKE_ALLOW_WRITES=1 for a disposable copy');
for (const key of ['RPGDX_TEST_USER', 'RPGDX_TEST_PASSWORD', 'RPGDX_TEST_ADMIN', 'RPGDX_TEST_ADMIN_PASSWORD']) {
  assert(process.env[key], `Missing ${key}`);
}
const marker = `Upgrade smoke ${Date.now()}`;
const unicode = 'café naïve 東京 中文 🎮';
const projectName = `${marker} ${unicode} O'Brien`;
const projectSummary = `Summary: ${unicode}.`;
const projectDescription = `[b]Preserved BBCode[/b] with Unicode: ${unicode}.`;
const editedProjectName = `${marker} edited ${unicode}`;
const screenshotCaption = `Reviewer's screenshot: ${unicode}`;
const maximumScreenshotCaption = screenshotCaption + 'x'.repeat(50 - [...screenshotCaption].length);
const excessiveScreenshotCaption = maximumScreenshotCaption + 'x';
const reviewText = `${marker}: reviewer's saved text ${unicode}.`;
const editedReviewText = `${marker}: edited review ${unicode}.`;
const articleTitle = `${marker} article ${unicode}`;
const articleSummary = `A temporary local article: ${unicode}.`;
const newsTitle = `${marker} news ${unicode}`;
const newsMessage = `[b]Local news test[/b] ${unicode}`;
const editedNewsMessage = `[b]Edited local news[/b] ${unicode}`;
const browserErrors = [];
let projectId;
let reviewId;
let articleId;
let newsId;
let ordinary;
let admin;
let ordinaryContext;
let adminContext;
async function healthy(page) {
  const body = await page.locator('body').innerText();
  assert(!/Fatal error:|Warning:|Deprecated:|Error during MySQL query/.test(body), body.slice(0, 2000));
}
async function visit(page, path) {
  await page.goto(new URL(path, base).href);
  await healthy(page);
}
async function submit(page) {
  await Promise.all([page.waitForLoadState('load'), page.locator('input[name="submit"]').click()]);
  await healthy(page);
}
async function confirmRemoval(page, path) {
  await visit(page, `${path}&confirmed=1`);
  assert.equal(await page.locator('form[method="post"] input[name="form_token"]').count(), 1, 'Confirmed GET must render a tokenized deletion form');
  await page.locator('input[name="form_token"]').evaluate(input => input.remove());
  await page.waitForTimeout(1100);
  await Promise.all([page.waitForLoadState('load'), page.locator('input[name="confirmed"]').click()]);
  await healthy(page);
  assert((await page.locator('body').innerText()).includes('Your form has expired.'), 'Deletion without a form token must be rejected');
  await visit(page, path);
  await page.waitForTimeout(1100);
  await Promise.all([page.waitForLoadState('load'), page.locator('input[name="confirmed"]').click()]);
  await healthy(page);
  assert.equal(new URL(page.url()).pathname, '/userpage.php', 'Tokenized POST deletion must return to the user page');
}
async function login(page, username, password) {
  await visit(page, '/login.php');
  await page.locator('[name="username"]').fill(username);
  await page.locator('[name="password"]').fill(password);
  await page.waitForTimeout(1100);
  await page.locator('input[name="login"]').click();
  await page.waitForURL(url => url.pathname === '/index.php' || url.pathname === '/', { timeout: 15000 });
  await healthy(page);
  assert((await page.locator('body').innerText()).includes(username));
  await visit(page, '/forums/index.php');
  assert.equal(await page.locator('#username_logged_in').count(), 1, 'Forum shares main-site login');
}
async function chooseFirst(page, name) {
  const value = await page.locator(`[name="${name}"] option`).evaluateAll(options => options.find(option => Number(option.value) > 0)?.value);
  assert(value, `No ${name} choices`);
  await page.locator(`[name="${name}"]`).selectOption(value);
}
(async () => {
  const browser = await chromium.launch({ executablePath: process.env.CHROMIUM_PATH, headless: true, args: ['--no-sandbox'] });
  try {
    ordinaryContext = await browser.newContext();
    adminContext = await browser.newContext();
    for (const context of [ordinaryContext, adminContext]) {
      await context.route('**/*', route => {
        const url = new URL(route.request().url());
        return url.origin === new URL(base).origin ? route.continue() : route.abort();
      });
    }
    ordinary = await ordinaryContext.newPage();
    admin = await adminContext.newPage();
    for (const page of [ordinary, admin]) page.on('pageerror', error => browserErrors.push(error.message));
    await login(ordinary, process.env.RPGDX_TEST_USER, process.env.RPGDX_TEST_PASSWORD);
    await login(admin, process.env.RPGDX_TEST_ADMIN, process.env.RPGDX_TEST_ADMIN_PASSWORD);
    await visit(ordinary, '/admin.php');
    assert((await ordinary.locator('body').innerText()).includes('do not have administrator rights'));
    for (const path of ['/admin.php?page=stats', '/admin.php?page=debug', '/admin.php?page=list&class=member']) await visit(admin, path);

    await visit(ordinary, '/userpage.php');
    const oldTheme = await ordinary.locator('[name="user_theme"]').inputValue();
    const oldSort = await ordinary.locator('[name="sort_method"]').inputValue();
    await ordinary.locator('[name="sort_method"]').selectOption('1');
    const classic = ordinary.locator('[name="user_theme"] option[value="2"]');
    if (await classic.count()) await ordinary.locator('[name="user_theme"]').selectOption('2');
    await ordinary.waitForTimeout(1100);
    await ordinary.locator('input[value="Apply changes"]').click();
    await healthy(ordinary);
    assert.equal(await ordinary.locator('[name="sort_method"]').inputValue(), '1');
    if (await classic.count()) assert((await ordinary.locator('link[rel="stylesheet"]').first().getAttribute('href')).includes('templates/classic/'));
    await ordinary.locator('[name="user_theme"]').selectOption(oldTheme);
    await ordinary.locator('[name="sort_method"]').selectOption(oldSort);
    await ordinary.waitForTimeout(1100);
    await ordinary.locator('input[value="Apply changes"]').click();
    await healthy(ordinary);

    await visit(ordinary, '/editproject_general.php?action=add');
    await ordinary.locator('[name="project_name"]').fill(projectName);
    await chooseFirst(ordinary, 'project_type');
    await ordinary.locator('[name="project_summary"]').fill(projectSummary);
    await ordinary.locator('[name="project_description"]').fill(projectDescription);
    await submit(ordinary);
    projectId = new URL(ordinary.url()).searchParams.get('project_id');
    assert(projectId, 'Project created');
    const png = Buffer.from(await ordinary.evaluate(() => {
      const canvas = document.createElement('canvas');
      canvas.width = canvas.height = 32;
      canvas.getContext('2d').fillRect(0, 0, 32, 32);
      return canvas.toDataURL('image/png').split(',')[1];
    }), 'base64');
    const iconForm = ordinary.locator('form').filter({ has: ordinary.locator('[name="uploaded_icon"]') });
    await iconForm.locator('[name="uploaded_icon"]').setInputFiles({ name: 'smoke.png', mimeType: 'image/png', buffer: png });
    await iconForm.locator('[name="upload"]').click();
    await healthy(ordinary);
    const screenshotForm = ordinary.locator('form').filter({ has: ordinary.locator('[name="uploaded_screenshot"]') });
    await screenshotForm.locator('[name="screenshot_caption"]').fill(excessiveScreenshotCaption);
    await screenshotForm.locator('[name="uploaded_screenshot"]').setInputFiles({ name: 'smoke.png', mimeType: 'image/png', buffer: png });
    await screenshotForm.locator('input[type="submit"]').click();
    await healthy(ordinary);
    const screenshotRejection = await ordinary.locator('body').innerText();
    await visit(ordinary, `/editproject.php?project_id=${projectId}`);
    const rejectedScreenshotCount = await ordinary.locator('input[name="screenshots[]"]').count();
    if (rejectedScreenshotCount) {
      for (let index = 0; index < rejectedScreenshotCount; index++) await ordinary.locator('input[name="screenshots[]"]').nth(index).check();
      await ordinary.locator('input[value="Remove selected"]').click();
      await healthy(ordinary);
    }
    await screenshotForm.locator('[name="screenshot_caption"]').fill(maximumScreenshotCaption);
    await screenshotForm.locator('[name="uploaded_screenshot"]').setInputFiles({ name: 'smoke.png', mimeType: 'image/png', buffer: png });
    await screenshotForm.locator('input[type="submit"]').click();
    await healthy(ordinary);
    const savedScreenshotText = await ordinary.locator('body').innerText();
    assert.equal(await ordinary.locator('input[name="screenshots[]"]').count(), 1, 'A 50-character mixed Unicode caption must upload');
    await ordinary.locator('input[name="screenshots[]"]').check();
    await ordinary.locator('input[value="Remove selected"]').click();
    await healthy(ordinary);
    await ordinary.locator('input[name="remove"]').click();
    await healthy(ordinary);
    assert(screenshotRejection.includes('Caption exceeds maximum length (50 characters).'), 'A 51-character caption must be rejected');
    assert.equal(rejectedScreenshotCount, 0, 'Rejected captions must not create screenshot rows');
    assert(savedScreenshotText.includes(maximumScreenshotCaption), 'Screenshot caption preserves all 50 mixed Unicode characters');
    await visit(ordinary, `/showgame.php?project_id=${projectId}`);
    assert((await ordinary.locator('body').innerText()).includes(projectName));
    assert((await ordinary.locator('body').innerText()).includes(`with Unicode: ${unicode}.`));
    assert.equal(await ordinary.locator('span[style="font-weight: bold"]').filter({ hasText: 'Preserved BBCode' }).count(), 1);
    await visit(ordinary, `/editproject_general.php?action=edit&project_id=${projectId}`);
    assert.equal(await ordinary.locator('[name="project_name"]').inputValue(), projectName);
    assert.equal(await ordinary.locator('[name="project_summary"]').inputValue(), projectSummary);
    assert.equal(await ordinary.locator('[name="project_description"]').inputValue(), projectDescription);
    await ordinary.locator('[name="project_name"]').fill(editedProjectName);
    await submit(ordinary);
    assert((await ordinary.locator('body').innerText()).includes('has been updated'));
    await visit(admin, `/editproject_general.php?action=edit&project_id=${projectId}`);
    assert.equal(await admin.locator('[name="project_name"]').inputValue(), editedProjectName);
    assert.equal(await admin.locator('[name="project_summary"]').inputValue(), projectSummary);
    assert.equal(await admin.locator('[name="project_description"]').inputValue(), projectDescription);

    await visit(admin, `/editreview.php?action=add&project_id=${projectId}`);
    await admin.locator('[name="review_score"]').selectOption('8');
    await admin.locator('[name="review_text"]').fill(reviewText);
    await submit(admin);
    const savedReviewText = await admin.locator('body').innerText();
    await visit(admin, '/userpage.php');
    const reviewHref = await admin.locator('tr').filter({ hasText: marker }).locator('a[href*="editreview.php?action=edit"]').first().getAttribute('href');
    reviewId = new URL(reviewHref, base).searchParams.get('review_id');
    assert(savedReviewText.includes(reviewText), 'Published review preserves mixed Unicode');
    await visit(ordinary, `/editreview.php?action=edit&review_id=${reviewId}`);
    assert((await ordinary.locator('body').innerText()).includes('do not have permission'));
    await visit(admin, `/editreview.php?action=edit&review_id=${reviewId}`);
    assert.equal(await admin.locator('[name="review_text"]').inputValue(), reviewText);
    await admin.locator('[name="review_text"]').fill(editedReviewText);
    await submit(admin);
    assert((await admin.locator('body').innerText()).includes('has been updated'));
    await visit(admin, `/editreview.php?action=edit&review_id=${reviewId}`);
    assert.equal(await admin.locator('[name="review_text"]').inputValue(), editedReviewText);
    await visit(admin, `/showgame.php?project_id=${projectId}`);
    assert((await admin.locator('body').innerText()).includes(editedReviewText));

    await visit(ordinary, '/editarticle.php?action=add');
    await ordinary.locator('[name="article_title"]').fill(articleTitle);
    await ordinary.locator('[name="article_url"]').fill('https://example.com/');
    await ordinary.locator('[name="article_summary"]').fill(articleSummary);
    await ordinary.locator('input[name="article_type"]').first().check();
    const articleCategory = await ordinary.locator('input[name="article_type"]:checked').inputValue();
    await submit(ordinary);
    await visit(ordinary, '/userpage.php');
    articleId = new URL(await ordinary.locator('tr').filter({ hasText: marker }).locator('a[href*="editarticle.php?action=edit"]').first().getAttribute('href'), base).searchParams.get('article_id');
    await visit(ordinary, `/editarticle.php?action=edit&article_id=${articleId}`);
    assert.equal(await ordinary.locator('[name="article_title"]').inputValue(), articleTitle);
    assert.equal(await ordinary.locator('[name="article_summary"]').inputValue(), articleSummary);
    await visit(ordinary, `/showarticles.php?cat=${articleCategory}`);
    assert((await ordinary.locator('body').innerText()).includes(articleTitle));
    assert((await ordinary.locator('body').innerText()).includes(articleSummary));

    await visit(ordinary, '/editnews.php?action=add');
    await ordinary.locator('[name="news_title"]').fill(newsTitle);
    await ordinary.locator('[name="news_message"]').fill(newsMessage);
    await submit(ordinary);
    await visit(ordinary, '/index.php');
    const newsHref = await ordinary.locator('.news-post').filter({ hasText: marker }).locator('a[href*="editnews.php?"][href*="action=edit"]').first().getAttribute('href');
    newsId = new URL(newsHref, base).searchParams.get('news_id');
    assert((await ordinary.locator('body').innerText()).includes(newsTitle));
    assert((await ordinary.locator('body').innerText()).includes(`Local news test ${unicode}`));
    await visit(ordinary, `/editnews.php?action=edit&news_id=${newsId}`);
    assert.equal(await ordinary.locator('[name="news_title"]').inputValue(), newsTitle);
    assert.equal(await ordinary.locator('[name="news_message"]').inputValue(), newsMessage);
    await ordinary.locator('[name="news_message"]').fill(editedNewsMessage);
    await submit(ordinary);
    assert((await ordinary.locator('body').innerText()).includes('has been updated'));
    await visit(ordinary, `/editnews.php?action=edit&news_id=${newsId}`);
    assert.equal(await ordinary.locator('[name="news_title"]').inputValue(), newsTitle);
    assert.equal(await ordinary.locator('[name="news_message"]').inputValue(), editedNewsMessage);
    await visit(ordinary, '/index.php');
    assert((await ordinary.locator('body').innerText()).includes(newsTitle));
    assert((await ordinary.locator('body').innerText()).includes(`Edited local news ${unicode}`));
    await visit(ordinary, '/profile.php?user_id=' + new URL(await ordinary.locator('a[href*="profile.php?user_id="]').first().getAttribute('href'), base).searchParams.get('user_id'));
    assert((await ordinary.locator('body').innerText()).includes('Personal Information'));
    assert.equal(browserErrors.length, 0, browserErrors.join('\n'));
    console.log('PASS: shared login, ordinary/admin permissions, preferences, mixed accented/CJK/emoji project, screenshot caption, review, article and news roundtrips, BBCode, uploads, ownership and profile');
  } finally {
    if (admin && reviewId) await confirmRemoval(admin, `/editreview.php?action=remove&review_id=${reviewId}`);
    if (ordinary && articleId) await confirmRemoval(ordinary, `/editarticle.php?action=remove&article_id=${articleId}`);
    if (ordinary && newsId) await confirmRemoval(ordinary, `/editnews.php?action=remove&news_id=${newsId}`);
    if (ordinary && projectId) await confirmRemoval(ordinary, `/editproject_general.php?action=remove&project_id=${projectId}`);
    if (ordinary) {
      await visit(ordinary, '/userpage.php');
      const logout = ordinary.locator('a[href*="login.php?logout=true"]');
      if (await logout.count()) {
        await logout.click();
        await healthy(ordinary);
        await visit(ordinary, '/forums/index.php');
        assert.equal(await ordinary.locator('#username_logged_in').count(), 0, 'Logout ends shared forum session');
      }
    }
    await browser.close();
  }
})().catch(error => { console.error(error); process.exitCode = 1; });
