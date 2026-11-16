# Repository notes

Read [readme.md](readme.md), [development.md](development.md), and
[upgrade.md](upgrade.md) for setup and migration.

## Architecture

- `forums/` contains the official phpBB 3.3.19 distribution. Keep upstream files
  unchanged and run `python3 tools/check-phpbb-core.py` to verify them.
- `forums/styles/rpgdx/` inherits prosilver; `forums/ext/rpgdx/site/` supplies
  site navigation through supported events.
- Top-level PHP, `includes/`, and `templates/` are the separate RPGDX application.
  Authentication is handled by phpBB; legacy rendering stays isolated in
  `includes/legacy/`.
- `rpgdx_user_preferences` holds application preferences. Do not add custom
  columns to stock phpBB tables.
- `mw_*` tables preserve the historical wiki archive.

## Local work

Use the separate Docker Compose project documented in development.md. Use only
private copies of the database and uploads for rehearsal. Never commit database
dumps, generated caches, credentials, or user uploads.

## Deployment

The `live` remote is a push-to-deploy production target. Its post-receive hook
copies the worktree into the public directory using `deploy/post-receive`.
The installed hook must be kept in sync with this tracked file.

The first phpBB3 deployment requires a clean release directory and coordinated
database conversion. The ordinary hook deliberately does not delete old files,
so it cannot remove obsolete phpBB2 scripts. Follow upgrade.md for cutover and
rollback, and perform a dry run before production deployment.

Protect server-specific `forums/config.php`, avatars, `uploads/`, forum
attachments, caches, and stored backups. Do not add broad delete operations to
the deployment hook.
