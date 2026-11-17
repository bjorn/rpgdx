Run `site-smoke.cjs` only against a disposable local migration copy. It creates and removes temporary projects, reviews, articles and news, checks shared forum login/logout, verifies owner/admin permissions, and restores account preferences.

Provide `RPGDX_SMOKE_ALLOW_WRITES=1`, `RPGDX_TEST_USER`, `RPGDX_TEST_PASSWORD`, `RPGDX_TEST_ADMIN` and `RPGDX_TEST_ADMIN_PASSWORD` through the environment. Set `PLAYWRIGHT_MODULE` to an installed Playwright module if needed and `CHROMIUM_PATH` to its browser executable. The default URL is `http://127.0.0.1:8083`; `RPGDX_TEST_URL` can select another localhost port.

```sh
node tests/site-smoke.cjs
```

The users must be temporary target-database accounts. Run the migration count checks before this test because activity updates timestamps and read tracking in the target database.

`forum-visual.cjs` captures the migrated forum index, forum/topic pages, login and search at desktop and mobile sizes. Optional `RPGDX_TEST_USER` and `RPGDX_TEST_PASSWORD` also enable mobile UCP and posting screenshots. It checks horizontal overflow and reports missing local images for visual review.

`forum-smoke.cjs` uses the same credentials and explicit write opt-in as the site test. It checks BBCode preview/posting, a reply, live search indexing, PM delivery between the two temporary accounts, and mobile UCP/posting layouts. `RPGDX_TEST_FORUM_ID` can select a writable forum; otherwise it uses the first visible forum.

```sh
node tests/forum-visual.cjs
node tests/forum-smoke.cjs
```

Forum helpers default to `http://localhost:8083`, accept `RPGDX_TEST_URL`, and block outside network requests. Local canonical redirects between `localhost` and `127.0.0.1` on the same port are supported. Screenshots and JSON reports go to `/tmp/rpgdx-forum-qa`; set `RPGDX_ARTIFACT_DIR` to change the destination. The Playwright/browser environment variables above also apply.

The forum smoke leaves its temporary topic and PM for inspection. `forum-fixtures.php` creates dedicated member/founder accounts and removes them afterwards through phpBB's native API, including their posts and PMs. It requires the local `rpgdx` database, disabled email, exported credentials, and usernames beginning with `rpgdx_smoke_`. Existing usernames are refused, and removal checks the IDs recorded in its temporary manifest.

```sh
docker compose -p rpgdx-phpbb3 exec -T \
  -e RPGDX_SMOKE_ALLOW_WRITES -e RPGDX_TEST_USER -e RPGDX_TEST_PASSWORD \
  -e RPGDX_TEST_ADMIN -e RPGDX_TEST_ADMIN_PASSWORD \
  web php tests/forum-fixtures.php create
node tests/forum-smoke.cjs
docker compose -p rpgdx-phpbb3 exec -T -e RPGDX_SMOKE_ALLOW_WRITES \
  web php tests/forum-fixtures.php remove
```

The manifest defaults to `/tmp/rpgdx-forum-fixtures.json` inside the container; `RPGDX_FIXTURE_MANIFEST` can override it. Keep it until removal, including after a failed test. Never use real accounts for these tests.

`application-encoding.php` checks the migration's Latin1 conversion, CJK/emoji round-trips, binary and serialized byte preservation, archive/session exclusions, and idempotence. It uses only connection-local temporary tables and requires the local `db` host, `rpgdx` database, and explicit write opt-in. Run `docker compose -p rpgdx-phpbb3 exec -T -e RPGDX_SMOKE_ALLOW_WRITES=1 web php tests/application-encoding.php`.

Run `docker compose -p rpgdx-phpbb3 exec -T web php tests/unicode-text.php` for database-free checks of Unicode truncation and original POST caption character counts, including the 50-character boundary and apostrophe escaping.
