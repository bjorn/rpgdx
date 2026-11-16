#!/usr/bin/env bash
# Destructive only to the two dedicated local rehearsal databases.
set -euo pipefail
cd "$(dirname "$0")/../.."
if [[ "${1:-}" != --reset-staging || ! -f "${2:-}" ]]; then
    echo "Usage: $0 --reset-staging /absolute/path/to/private-backup.sql" >&2
    exit 2
fi
backup=$2
rpgdx_port=${RPGDX_PORT:-8083}
if [[ ! "$rpgdx_port" =~ ^[0-9]+$ ]] || ((rpgdx_port < 1 || rpgdx_port > 65535)); then
    echo 'RPGDX_PORT must be between 1 and 65535' >&2
    exit 2
fi
compose=(docker compose -p rpgdx-phpbb3)
scripts/migration/installer.sh enable
"${compose[@]}" exec -T db mariadb -u root -prpgdx_root <<'SQL'
DROP DATABASE IF EXISTS rpgdx_legacy;
DROP DATABASE IF EXISTS rpgdx;
CREATE DATABASE rpgdx_legacy CHARACTER SET latin1;
CREATE DATABASE rpgdx CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;
GRANT ALL ON rpgdx.* TO 'username'@'%';
GRANT SELECT ON rpgdx_legacy.* TO 'username'@'%';
SQL
# The historical dump contains CREATE DATABASE and USE for the live name.
# Strip only those directives and stream all private rows directly to MariaDB.
python3 - "$backup" <<'PY' | "${compose[@]}" exec -T db mariadb -u root -prpgdx_root rpgdx_legacy
import re
import sys
with open(sys.argv[1], 'rb') as source:
    for line in source:
        if not re.match(rb'^(CREATE DATABASE|USE)\b', line):
            sys.stdout.buffer.write(line)
PY
rm -f forums/config.php
"${compose[@]}" exec -T web sh -c 'find forums/cache -mindepth 1 ! -name .htaccess ! -name index.htm -delete'
"${compose[@]}" exec -T web sh -c 'cat > /tmp/rpgdx-install.yml' <<YAML
installer:
  admin:
    name: migration_admin
    password: local-migration-only
    email: migration@example.invalid
  board:
    lang: en
    name: RPGDX
    description: RPG development community
  database:
    dbms: mysqli
    dbhost: db
    dbuser: username
    dbpasswd: password
    dbname: rpgdx
    table_prefix: phpbb_
  email:
    enabled: false
  server:
    server_name: localhost
    server_port: $rpgdx_port
    script_path: /forums
    cookie_secure: false
    force_server_vars: false
YAML
"${compose[@]}" exec -T web php forums/install/phpbbcli.php install /tmp/rpgdx-install.yml
"${compose[@]}" exec -T web rm /tmp/rpgdx-install.yml
"${compose[@]}" exec -T web chown -R www-data:www-data forums/cache forums/store forums/images/avatars/gallery forums/images/ranks forums/images/smilies
"${compose[@]}" exec -T -e MIGRATION_DISPOSABLE_SOURCE=1 -e MIGRATION_SOURCE_WRITE_USER=root -e MIGRATION_SOURCE_WRITE_PASSWORD=rpgdx_root web php scripts/migration/prepare-source.php
"${compose[@]}" exec -T db mariadb -u root -prpgdx_root rpgdx -e "UPDATE phpbb_config SET config_value='0' WHERE config_name IN ('max_post_font_size','max_sig_font_size');"
MIGRATION_DB_PASSWORD=password python3 scripts/migration/convert.py --url "http://localhost:$rpgdx_port/forums/install/app.php"
scripts/migration/finalize.sh
