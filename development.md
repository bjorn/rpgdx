# RPGDX development

Use the isolated Docker setup for the phpBB3 site:

```bash
docker compose -p rpgdx-phpbb3 up -d --build
```

The site is at http://localhost:8083/. Set `RPGDX_PORT` to change the host port.
PHP source and templates are bind-mounted, so edits appear immediately. PHP 8.3
includes mysqli, mbstring, XML, GD, and zip support. No global PHP compatibility
shim is required.

The database starts empty. Follow [upgrade.md](upgrade.md) to restore and convert
an existing RPGDX/phpBB2 database. Keep private dumps outside the checkout and
web root. Database credentials in Compose are only for the local instance.

```bash
docker compose -p rpgdx-phpbb3 logs --tail 100 web
docker compose -p rpgdx-phpbb3 exec -T web php forums/bin/phpbbcli.php cache:purge
python3 tools/check-phpbb-core.py
```

The application has three separate layers:

- Stock phpBB under `forums/`, including unmodified prosilver templates.
- The RPGDX forum style and extension under `forums/styles/rpgdx/` and
  `forums/ext/rpgdx/site/`.
- The main RPGDX site, whose `includes/phpbb.php` adapter starts the shared
  session and whose legacy rendering helpers stay under `includes/legacy/`.

Use phpBB's supported extension events for forum behavior. Keep application
preferences in `rpgdx_user_preferences`, outside the stock user schema. Changes
to upstream files fail `tools/check-phpbb-core.py`.

Do not use the ordinary deployment hook for the initial phpBB2 replacement.
It removes retired and repository-excluded application files but preserves
production configuration and runtime data, so it is not a clean migration
release. Follow the clean release and rollback procedure in the upgrade
instructions.

## Deployment filter test

Run `python3 tests/test_deploy.py` with rsync installed. It exercises the actual
post-receive hook using disposable local source and destination directories,
covering a dry run, file copying, stale-file deletion, protected server data and
rsync failure. Repository-only exclusions are anchored at the root so phpBB
runtime directories remain included. Access rules are copied; server
configuration, uploaded files, and historical images are preserved. Historical
image filters cover only the legacy avatar image extensions and known site image
paths, so stale PHP files alongside them are removed. The test does not contact
production.
