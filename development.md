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
It preserves existing server files, including obsolete phpBB2 entrypoints.
Follow the clean release and rollback procedure in the upgrade instructions.
