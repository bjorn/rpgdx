#!/usr/bin/env bash
# Local staging finalization, including safe mail and cookie settings.
set -euo pipefail
cd "$(dirname "$0")/../.."
compose=(docker compose -p rpgdx-phpbb3)
"${compose[@]}" exec -T web php scripts/migration/supplement.php
"${compose[@]}" exec -T db mariadb -u root -prpgdx_root rpgdx <<'SQL'
UPDATE phpbb_config SET config_value='0' WHERE config_name IN ('email_enable', 'smtp_delivery', 'cookie_secure');
UPDATE phpbb_config SET config_value='' WHERE config_name='cookie_domain';
UPDATE phpbb_config SET config_value='/' WHERE config_name='cookie_path';
UPDATE phpbb_config SET config_value='rpgdx3_staging' WHERE config_name='cookie_name';
SQL
"${compose[@]}" exec -T web php scripts/migration/finish.php
"${compose[@]}" exec -T web php forums/bin/phpbbcli.php cache:purge
"${compose[@]}" exec -T web chown -R www-data:www-data forums/cache forums/store
scripts/migration/installer.sh disable
echo 'Rehearsal complete; installer removed from served tree.'
