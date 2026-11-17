# RPGDX

RPGDX hosts independent RPG projects, reviews, articles, contests, and a phpBB forum.
The forum uses stock phpBB 3.3.19 with a prosilver-based RPGDX style and a small
extension. Site authentication uses phpBB sessions and permissions.

## Local development

The Docker environment uses Apache, PHP 8.3, and MariaDB 11.7. It binds to
localhost on port 8083 by default. See [development.md](development.md) and
[the migration instructions](upgrade.md) for restoring and converting a phpBB2
backup. A fresh phpBB installation alone does not contain the RPGDX tables.

## Maintaining phpBB

The files supplied in the official phpBB distribution are unchanged. Verify this
with `python3 tools/check-phpbb-core.py`.

RPGDX additions live in `forums/styles/rpgdx/`, `forums/ext/rpgdx/`, and the main
site's `includes/` directory. Keep these additions when updating phpBB. Update
prosilver along with the forum, and verify both forum and site login flows after
an update. The release archive URL and checksums are recorded in
`tools/phpbb-upstream.json`.

The child style inherits prosilver, with three template overrides for the compact
index and topic tables: `index_body.html`, `forumlist_body.html`, and
`viewforum_body.html`. Compare these against their updated prosilver versions
when upgrading. Header, breadcrumb and topic additions use extension events;
phpBB core and prosilver remain unchanged.

The main site's existing templates and BBCode content are supported by isolated
legacy helpers. They do not replace phpBB3's template or text rendering systems.

## Production migration

Rehearse conversion with a recent database backup and copies of uploaded files.
Switch the site and forum together during a posting freeze, following the
migration instructions. Existing phpBB2 files must not remain in the deployed
forum directory. The ordinary rsync deployment hook removes files absent from
the public checkout, including repository-excluded paths, while explicit filters
preserve production configuration and runtime data. The coordinated initial
migration still requires a prepared, clean release directory.
