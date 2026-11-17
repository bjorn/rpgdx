# phpBB3 upgrade

The forum uses the official phpBB 3.3.19 files without core modifications. RPGDX
integration uses a separate bootstrap, adapters, `forums/ext/rpgdx/site`, and the
`forums/styles/rpgdx` child style. Check the distribution with
`python3 tools/check-phpbb-core.py` after changing the integration.

Run `python3 tools/check-phpbb-style.py` after replacing phpBB files. It compares
the native sources of child template overrides with their reviewed hashes,
flagging only templates that need attention. After reviewing those differences
and updating overrides as needed, refresh the baseline with
`python3 tools/check-phpbb-style.py --record`.

## Updating an existing phpBB3 database

After replacing the stock files with the verified release, back up the existing
target database. Run the stock database-only updater without rerunning the
phpBB2 conversion or supplemental data copy:

```bash
scripts/migration/installer.sh enable
docker compose -p rpgdx-phpbb3 exec -T --user www-data web php forums/install/phpbbcli.php update tools/phpbb-update-db.yml --no-interaction --safe-mode
docker compose -p rpgdx-phpbb3 exec -T --user www-data web php forums/bin/phpbbcli.php cache:purge
scripts/migration/installer.sh disable
python3 tools/check-phpbb-core.py
```

The installer is served only temporarily. Keep it restricted to the operator.
Remove it with `installer.sh disable` immediately after updating. Outside Docker,
run the same PHP commands as the web user. Verify forum and site login, posting and profile pages afterward.
phpBB 3.3.19 supplies the security fixes omitted from the 3.3.18 packages; see
[the official changelog](https://www.phpbb.com/community/docs/CHANGELOG.html#v3318).

## Local rehearsal

Keep the SQL backup outside the checkout. It contains private account data and
must never be committed or copied to the web root. The historical dump includes
its original database name; the rehearsal script removes those database-selection
directives while importing into a separate source database.

Prepare a private copy of the phpBB2 code, then add the actual uploaded avatars
and custom smilies from the old deployment:

```bash
mkdir -p /tmp/rpgdx-private-legacy
git archive 6e51955 forums | tar -x -C /tmp/rpgdx-private-legacy
export RPGDX_LEGACY_FORUMS=/tmp/rpgdx-private-legacy/forums
# Copy private avatars and custom smilies into this forum copy.
```

The migration Docker override mounts that copy at
`/var/www/html/legacy-forums`, read only. Include the old uploaded avatars and
custom smilies there so the official converter can copy them. Keep RPGDX's
`uploads/` directory alongside the site.

```bash
docker compose -p rpgdx-phpbb3 -f docker-compose.yml -f docker-compose.migration.yml up -d --build
scripts/migration/rehearse.sh --reset-staging /absolute/path/to/private-backup.sql
```

This command recreates only the local `rpgdx` and `rpgdx_legacy` databases. It
installs phpBB through its official CLI, drives the official phpBB2 web converter,
copies the application data, checks the conversion, and disables outgoing email
and production cookies. The source database is read only to the forum account.
The temporary installation administrator is removed by the stock conversion.
The command enables the RPGDX child style and extension, builds the MySQL fulltext search
index, and removes the served installer. A verified canonical installer stays in
`tools/phpbb-install`; the next rehearsal copies it back automatically.

The stock converter rejects usernames that normalize to the same phpBB3 name.
`prepare-source.php` keeps the lowest user ID unchanged and gives later conflicts
a deterministic ID suffix in the disposable source copy. It records both names
in `rpgdx_migration_username_changes`, which is copied to the target. The original
backup is unchanged. One duplicate name required this adjustment in the tested
snapshot. Target font-size parser limits are temporarily disabled during
conversion to retain old oversized BBCode, then restored to 200.

Search uses phpBB's stock MySQL Fulltext backend on MariaDB. Its own installer
creates the indexes and reads the database's token limits. The tested InnoDB
configuration indexes words of 3 through 84 characters and uses MariaDB's default
stopword list. The database maintains the index when posts are added or changed.

The converter saves progress in the target database. If interrupted after it has
accepted its settings, continue without resetting either database:

```bash
MIGRATION_DB_PASSWORD=password python3 scripts/migration/convert.py --resume
scripts/migration/finalize.sh
```

The supplemental step can be rerun before the site is opened for writes. It
replaces application and wiki tables from the source, remaps ownership, restores
preferences and read markers, and expires sessions. Repeating it after accepting
new content would overwrite that new application content.

## Preserved data

The stock converter handles forum users, salted legacy passwords, profile
fields, posts, topics, polls, private messages, groups, permissions, watches,
avatars and smilies. It creates native profile fields such as
`pf_phpbb_location`, `pf_phpbb_occupation`, `pf_phpbb_interests`, and
`pf_phpbb_website`.

The supplemental script copies the `rpgdx_*` and `mw_*` source tables except
obsolete `rpgdx_sessions`. The known application tables listed in
`scripts/migration/application-encoding.php` use
`utf8mb4_unicode_ci` in the target. Copying transcodes text from each source
column's declared character set, and migration checks compare every copied text
column with its Unicode source value. Binary columns are not transcoded.
MediaWiki and unknown archival tables retain their original schema and data.
The legacy session table is not copied, and any leftover target copy is dropped
because authentication now uses phpBB3 sessions. The source copy is unchanged.

Main-site requests configure the shared mysqli connection as `utf8mb4` before
escaping form input, allowing CJK text and four-byte characters such as emoji in
RPGDX content. The standalone forum keeps phpBB's own connection configuration.
Caption length limits and search excerpt truncation count Unicode characters.
The application encoding regression test checks legacy text and binary data;
the site smoke test verifies Unicode through create, edit and display forms.

phpBB3 reserves user ID 1 for its anonymous account. The official conversion maps
legacy anonymous ID -1 to 1 and moves an existing legacy user ID 1 to one greater
than the source's highest user ID. All other user IDs retain their values.
`rpgdx_user_id_map` records this mapping. Article, news, project and review
ownership, contest votes, preferences and read markers use it. Existing references
to users already deleted from the source remain historical references.

`rpgdx_user_preferences` stores the three application preferences outside the
stock users table. Legacy `phpbb_reads` rows become native `phpbb_topics_track`
rows; `user_lastread` becomes `user_lastmark`. One second is subtracted because
the old unread comparison includes the boundary timestamp and phpBB3 excludes it.
Deleted topics and anonymous read markers are skipped.

Checks cover post/topic/private-message counts, poll options and totals, poll
voters, custom group memberships, mapped users, every legacy password against its
converted hash, application and wiki row counts, ownership, preferences, native
read-marker references, uploaded avatar files, and accented-text encoding samples.
Numeric legacy avatar types become native driver names. Unavailable original
avatar files produce an explicit warning and are recorded in
`rpgdx_migration_missing_assets`; their account references are retained.
RPGDX upload filenames are checked using the site's original expression,
`CONCAT('uploads/', upload_type, upload_id, upload_ext)`. Unavailable referenced
files are recorded in `rpgdx_migration_missing_uploads` with an explicit warning
and a flag identifying current project references.
Missing screenshot thumbnails are regenerated from existing original images with
the site's thumbnail helper.
SQL rows and password hashes are
never printed by these scripts. Converter diagnostics stay in a local temporary
file if an error occurs.

Two independent snapshots were converted:

| Backup completed | User rows | Posts | Topics | Private messages | Projects |
| --- | ---: | ---: | ---: | ---: | ---: |
| 2024-05-22 07:40:54 | 2,123 | 26,912 | 2,103 | 3,152 | 276 |
| 2026-11-16 19:49:10 | 2,128 | 26,936 | 2,111 | 3,153 | 277 |

Both contain 258 poll options, 810 poll voter rows, 52 articles, 254 reviews and
254 news items. Nineteen accented-text samples passed encoding checks. The final
staging database uses the fresh 2026 snapshot and current uploaded files. It
retains 22 unavailable original files among 123 uploaded-avatar references in the
missing-asset audit. All 613 active project icons/screenshots have their files and
metadata. Of 902 historical upload registry rows, 260 refer to unavailable files;
none of those rows are referenced by current projects. A new frozen backup is
still required at production cutover.

## Production cutover

The ordinary push-to-deploy hook is not the initial upgrade procedure: although
it removes application files absent from the public checkout, including
repository-excluded paths, it deliberately preserves production configuration
and runtime data and cannot coordinate the database cutover. Prepare a clean
release beside the current deployment, then switch code and database together.

1. Build that clean release from this checkout, excluding `.git`, private SQL
   dumps, Docker files, tests, and generated caches. Include phpBB's `.htaccess`
   files. Keep migration scripts and the canonical installer in a private tools
   directory, outside the final public document root. Use the tested PHP 8.3
   runtime and extensions; the old global PHP shims are no longer required.
2. Close both forum and RPGDX writes. Take a final database backup and copies of
   uploads and avatars, and retain the entire old deployment for rollback. Copy
   `uploads/` into the new release. Also copy historical image assets: legacy
   `forums/images/avatars/*.{gif,jpg,png}`, `images/guicomp/*.png`,
   `images/tablet.png`, `images/sponge.jpg`, `images/sourcesecure-120x90.gif`,
   and `templates/modern/images/rpgdx_banner.png`. Preserve
   `.htaccess_forwards` and `.well-known/acme-challenge/` if present, keeping
   certificate renewal tokens available. Do not mix old forum PHP files into it.
3. Restore the backup into a disposable source database on the same MariaDB
   server as a new target database. Configure the new release's
   `forums/config.php` for that target and install stock phpBB using the
   `phpbb_` prefix. Restrict the temporary installer to the migration operator.
4. Make the old forum assets available to the converter outside public access.
   Run `prepare-source.php` against the disposable source with its explicit
   write opt-in, retaining the username audit. Temporarily lift target font-size
   parser limits as in `rehearse.sh`. Run the stock phpBB2 converter or
   `convert.py` with the target URL and source options. Supply credentials
   through the documented environment variables.
5. Give the target database account temporary SELECT access to the source and
   run `MIGRATION_SOURCE_DB=source_database MIGRATION_LEGACY_FORUMS=/private/old-forum php scripts/migration/supplement.php`.
   Review every check and any missing-asset audit rows. Then run
   `php scripts/migration/finish.php` to activate the style/extension and rebuild
   search. Remove source-database access afterward. Do not use the local
   `finalize.sh` in production because it deliberately disables mail and HTTPS
   cookies for staging.
6. Configure production URL, HTTPS cookies, cookie path `/`, and mail settings.
   Ensure uploaded avatars and attachments are in the new release, and make
   phpBB's cache, files, store and upload directories writable by its web user.
   Remove the served installer and legacy source copy. Purge the cache and
   verify the prepared release using a private hostname or local host mapping.
7. Switch the hosting document root to the prepared release. If the host requires
   a fixed directory, stop requests while moving the old public directory aside
   and placing the clean release there. Keep writes closed until login/logout,
   owner/admin permissions, old URLs, avatars and representative pages pass on
   the production hostname. Then reopen the site and update the installed
   deployment hook from `deploy/post-receive` for subsequent ordinary updates.

For rollback, stop writes and restore the old code, database and uploads as one
set. Posts accepted after cutover must be reconciled before rolling back to the
old snapshot. Keep the original backup until the new deployment is verified.
