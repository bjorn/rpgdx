# CLAUDE.md

Notes for AI agents working in this repo. Read [readme.md](readme.md) and [development.md](development.md) first — the guidance here **does not** duplicate them.

## Project shape

- `forums/` — the phpBB 2.0.23 fork. Stock phpBB files are here, customized in place.
- Everything at the top level (`index.php`, `admin.php`, `showgame.php`, `templates/`, `includes/`, `images/`, `sql/`) is the custom rpgdx site, separate from phpBB.
- The site is **archival**. Prefer minimal, surgical changes. Don't modernize or refactor aggressively; fix bugs in the existing style.

## Deploying

There is no CI. Deploys go through a git push-to-deploy on a NearlyFreeSpeech host:

```
git push live master
```

- `live` remote → `blindeijer_rpgdx@ssh.nyc1.nearlyfreespeech.net:/home/private/rpgdx`
- `receive.denyCurrentBranch = updateInstead` updates that worktree
- `deploy/post-receive` (the tracked source of truth) runs and rsyncs to `/home/public/`

**The hook on the server must match `deploy/post-receive` in the repo.** If you edit the hook, copy it back manually:

```bash
scp deploy/post-receive blindeijer_rpgdx@ssh.nyc1.nearlyfreespeech.net:/home/private/rpgdx/.git/hooks/post-receive
ssh blindeijer_rpgdx@ssh.nyc1.nearlyfreespeech.net 'chmod +x /home/private/rpgdx/.git/hooks/post-receive'
```

The hook excludes `.*`, `*.md`, `HOSTED`, `TODO`, `rpgdx.code-workspace`, `sql`, `install`, `docker`, `docker-compose.yml`, `deploy`. It does **not** pass `--delete` — server-only files (`.htaccess_forwards`, `images/guicomp/`, user uploads) are preserved. Don't add `--delete` without checking what would be swept up.

**Before pushing to live:** for anything non-trivial, do a dry-run first (see `memory/feedback_prod_deploy_caution.md`). One bad rsync can expose the phpBB installer or clobber user content.

## Files that must never end up on prod

These are `.gitignore`'d for a reason — the server has the real versions:

- `forums/config.php` — server-specific DB credentials
- `uploads/*.{jpg,png,gif}` — user-uploaded game/project assets
- `forums/images/avatars/*.{jpg,png,gif}` — user avatars

Don't add `!`-patterns to `.gitignore` or commit local copies of these.

## Database layout

Three table prefixes in `indierp_main`:

- `phpbb_*` — stock phpBB 2 tables (created by `forums/install/install.php` on first setup, not by `sql/database.sql`). Custom code assumes this exact prefix.
- `rpgdx_*` — custom rpgdx tables (projects, articles, news, contests, reviews…). Defined in `sql/database.sql`.
- `mw_*` — legacy MediaWiki archive from an old incarnation of the site. Read-only, not used by the current app. Leave alone.

`sql/database.sql` defines the rpgdx tables **plus** one custom phpBB table (`phpbb_reads`). It is not a full phpBB schema — phpBB's installer produces those.

## PHP 8 warning fixes

Running phpBB 2 (~2003 code) on PHP 8 produces a steady stream of "Undefined array key" / "Undefined variable" warnings that flood the apache log. Multiple recent commits (`d57960b`, `32fca9d`, `4ec3789`, `41e5c54`) fix these in a consistent style:

- **For row-key accesses** like `$row['user_avatar_type']`: extract to a local with a `??` default, then use the local:
  ```php
  $avatar_type = $row['user_avatar_type'] ?? 0;
  if ($avatar_type && ...) { switch ($avatar_type) { ... } }
  ```
- **For plain variables** used but never assigned in some code path: initialize them at the top of the enclosing scope (e.g. `$select_sort_mode = '';`) rather than sprinkling `isset()` at the read sites.

Preferred over inline `isset($x) ? $x : ''` because it keeps the read sites readable.

## Local iteration

The repo is bind-mounted into the `web` container (`./:/var/www/html:delegated`), so PHP/template edits are live — just refresh the browser, no rebuild. Apache logs for debugging:

```bash
docker compose logs -f web
```

Rebuild is only needed when `docker/apache/Dockerfile` changes.

## Git remotes

- `origin` → `git@github.com:bjorn/rpgdx.git` (public, archival)
- `live` → NearlyFreeSpeech push-to-deploy (see above)
