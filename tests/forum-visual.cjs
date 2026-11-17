const qa = require('./forum-browser.cjs');

(async () => {
    const browser = await qa.launch();
    const screenshots = [];
    try {
        const context = await qa.context(browser);
        const page = await context.newPage();
        await qa.visit(page, '/forums/index.php');
        screenshots.push(await qa.capture(page, 'forum-index'));
        const forum = await page.locator('a.forumtitle').first().getAttribute('href');
        const forumUrl = new URL(forum, page.url());
        const forumId = forumUrl.searchParams.get('f');
        await qa.visit(page, forumUrl.pathname + forumUrl.search);
        screenshots.push(await qa.capture(page, 'forum-list'));
        const topic = await page.locator('a.topictitle').first().getAttribute('href');
        const topicUrl = new URL(topic, page.url());
        await qa.visit(page, topicUrl.pathname + topicUrl.search);
        screenshots.push(await qa.capture(page, 'forum-topic'));
        for (const [name, url] of [['login', '/forums/ucp.php?mode=login'], ['search', '/forums/search.php']]) {
            await qa.visit(page, url);
            screenshots.push(await qa.capture(page, `forum-${name}`));
        }
        await page.setViewportSize({ width: 390, height: 844 });
        await qa.visit(page, '/forums/index.php');
        screenshots.push(await qa.capture(page, 'forum-mobile-index'));
        await qa.visit(page, topicUrl.pathname + topicUrl.search);
        screenshots.push(await qa.capture(page, 'forum-mobile-topic'));
        if (process.env.RPGDX_TEST_USER && process.env.RPGDX_TEST_PASSWORD) {
            await qa.login(page, process.env.RPGDX_TEST_USER, process.env.RPGDX_TEST_PASSWORD);
            const postingUrl = `/forums/posting.php?mode=reply&f=${forumId}&t=${topicUrl.searchParams.get('t')}`;
            for (const [name, url] of [['ucp', '/forums/ucp.php'], ['posting', postingUrl]]) {
                await qa.visit(page, url);
                screenshots.push(await qa.capture(page, `forum-mobile-${name}`));
            }
        }
        qa.assert.equal(qa.browserErrors.length, 0, qa.browserErrors.join('\n'));
        qa.report('forum-visual', { screenshots, browserErrors: qa.browserErrors });
        console.log(`PASS: forum desktop/mobile layouts. Screenshots: ${qa.output}`);
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error.message); process.exitCode = 1; });
