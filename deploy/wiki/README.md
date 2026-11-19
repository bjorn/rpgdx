# RPGDX wiki archive

The wiki runs on NearlyFreeSpeech.NET at <https://rpgdx.net/wiki/index.php/Main_Page>. The RPGDX deployment hook preserves the separately deployed `/home/public/wiki` symlink.

The release lives under `/home/protected/rpgdx-wiki-archive`. The `app/` directory contains MediaWiki and Vector. The public symlink points to `www/`, which contains the PHP entry points and static resources. The database configuration, secret, and localization cache are outside both directories.

The active forum database contains the archive in tables with the `wiki_` prefix. These tables contain the cleaned public page histories and author names. The original `mw_` tables predate the upgrade. Include `wiki_` in database backups.

For a MediaWiki update, back up the `wiki_` tables and stage the new application outside the web root. Copy `LocalSettings.php` from this directory. Refresh the public resource files and use `public.htaccess` in the public directory. Run schema updates from the CLI with a maintenance configuration that permits writes. Keep the public configuration read-only. Verify the Main Page, history, search, Recent changes and the API's read-only status before switching releases.
