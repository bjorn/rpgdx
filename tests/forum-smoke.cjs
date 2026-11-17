const qa = require('./forum-browser.cjs');

qa.assert.equal(process.env.RPGDX_SMOKE_ALLOW_WRITES, '1', 'Set RPGDX_SMOKE_ALLOW_WRITES=1 for a disposable copy');
qa.credentials();
const marker = `rpgdxsmoke${Date.now()}`;
const subject = `Forum upgrade smoke ${marker}`;
const pmSubject = `Private message smoke ${marker}`;
const results = { marker, checks: [] };

function check(name, passed) {
    results.checks.push({ name, passed });
    qa.report('forum-smoke', results);
    qa.assert(passed, name);
}

(async () => {
    const browser = await qa.launch();
    try {
        const member = await (await qa.context(browser)).newPage();
        const admin = await (await qa.context(browser)).newPage();
        await qa.login(member, process.env.RPGDX_TEST_USER, process.env.RPGDX_TEST_PASSWORD);
        await qa.login(admin, process.env.RPGDX_TEST_ADMIN, process.env.RPGDX_TEST_ADMIN_PASSWORD);
        await qa.visit(member, '/forums/index.php');
        const forumLink = await member.locator('a.forumtitle').first().getAttribute('href');
        const forum = process.env.RPGDX_TEST_FORUM_ID || new URL(forumLink, member.url()).searchParams.get('f');
        qa.assert(/^\d+$/.test(forum), 'A writable test forum is required');
        await qa.visit(member, `/forums/posting.php?mode=post&f=${forum}`);
        await member.locator('[name="subject"]').fill(subject);
        await member.locator('[name="message"]').fill(`[b]${marker}[/b] posting verification.\n[quote]Preserved BBCode[/quote]`);
        await qa.submit(member, '[name="preview"]');
        check('BBCode preview', await member.locator('.postbody .content strong,.postbody .content b').filter({ hasText: marker }).count() > 0);
        await qa.submit(member, '[name="post"]');
        await member.waitForURL(url => url.pathname.endsWith('/viewtopic.php'), { timeout: 20000 });
        await member.waitForLoadState('networkidle');
        const topicUrl = new URL(member.url());
        results.topicUrl = topicUrl.href;
        const topic = topicUrl.searchParams.get('t');
        check('Posted topic renders BBCode', await member.locator('.post .content strong,.post .content b').filter({ hasText: marker }).count() > 0);

        await qa.visit(member, '/forums/ucp.php?i=pm&mode=compose');
        await member.locator('[name="username_list"]').fill(process.env.RPGDX_TEST_ADMIN);
        await qa.submit(member, '[name="add_to"]');
        await member.locator('[name="subject"]').fill(pmSubject);
        await member.locator('[name="message"]').fill(`[b]${marker}[/b] private message verification.`);
        await qa.submit(member, '[name="post"]');
        check('Private message submitted', (await member.locator('body').innerText()).includes('sent successfully'));
        await qa.visit(admin, '/forums/ucp.php?i=pm&folder=inbox');
        await admin.getByRole('link', { name: pmSubject, exact: true }).click();
        await admin.waitForLoadState('networkidle');
        check('Private message received', (await admin.locator('.content').allTextContents()).some(text => text.includes(marker) && text.includes('private message verification.')));
        await qa.capture(admin, 'forum-smoke-pm');

        await qa.visit(member, `/forums/posting.php?mode=reply&f=${forum}&t=${topic}`);
        await member.locator('[name="message"]').fill(`${marker} reply verification.`);
        await qa.submit(member, '[name="post"]');
        if ((await member.locator('body').innerText()).includes('cannot make another post so soon')) {
            // Respect the board's normal flood limit instead of changing it.
            await member.waitForTimeout(31000);
            await qa.submit(member, '[name="post"]');
        }
        await member.waitForURL(url => url.pathname.endsWith('/viewtopic.php'), { timeout: 20000 });
        await member.waitForLoadState('networkidle');
        check('Forum reply', (await member.locator('.post .content').allTextContents()).some(text => text.includes(`${marker} reply verification.`)));
        await qa.visit(member, `/forums/search.php?keywords=${marker}`);
        check('New post indexed for search', (await member.locator('body').innerText()).includes(subject));
        await qa.capture(member, 'forum-smoke-search');
        for (const [name, url] of [['ucp', '/forums/ucp.php'], ['posting', `/forums/posting.php?mode=reply&f=${forum}&t=${topic}`]]) {
            await member.setViewportSize({ width: 390, height: 844 });
            await qa.visit(member, url);
            await qa.capture(member, `forum-smoke-mobile-${name}`);
        }
        qa.assert.equal(qa.browserErrors.length, 0, qa.browserErrors.join('\n'));
        console.log(`PASS: forum BBCode preview/post, reply, live search, PM send/receive and mobile UCP/posting. Artifacts: ${qa.output}`);
    } finally {
        qa.report('forum-smoke', results);
        await browser.close();
    }
})().catch(error => { console.error(error.message); process.exitCode = 1; });
