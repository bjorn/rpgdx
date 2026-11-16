<?php
/** Resolve phpBB3 username collisions in a disposable source copy only. */
if (PHP_SAPI !== 'cli' || getenv('MIGRATION_DISPOSABLE_SOURCE') !== '1') {
    exit("Set MIGRATION_DISPOSABLE_SOURCE=1 for an isolated source copy\n");
}
$phpbb_root_path = dirname(__DIR__, 2) . '/forums/';
$phpEx = 'php';
define('IN_PHPBB', true);
require $phpbb_root_path . 'vendor/autoload.php';
require $phpbb_root_path . 'includes/utf/utf_tools.php';
require $phpbb_root_path . 'config.php';
$source = getenv('MIGRATION_SOURCE_DB') ?: 'rpgdx_legacy';
if (!preg_match('/^[a-zA-Z0-9_]+$/', $source) || $source === $dbname) {
    throw new RuntimeException('Use a separate source database');
}
$port = !empty($dbport) ? ';port=' . (int) $dbport : '';
$pdo = new PDO("mysql:host=$dbhost$port;dbname=$source;charset=utf8mb4", getenv('MIGRATION_SOURCE_WRITE_USER') ?: $dbuser, getenv('MIGRATION_SOURCE_WRITE_PASSWORD') ?: $dbpasswd, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('CREATE TABLE IF NOT EXISTS rpgdx_migration_username_changes (legacy_user_id INT NOT NULL PRIMARY KEY, original_username VARCHAR(255) NOT NULL, converted_username VARCHAR(255) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
$users = $pdo->query('SELECT user_id, username FROM phpbb_users ORDER BY user_id')->fetchAll(PDO::FETCH_ASSOC);
$occupied = [];
foreach ($users as $row) {
    $occupied[utf8_clean_string($row['username'])] = true;
}
$seen = [];
$count = 0;
foreach ($users as $row) {
    $clean = utf8_clean_string($row['username']);
    if (isset($seen[$clean])) {
        $suffix = '_' . $row['user_id'];
        $candidate = mb_substr($row['username'], 0, 25 - strlen($suffix), 'UTF-8') . $suffix;
        while (isset($occupied[utf8_clean_string($candidate)])) {
            $suffix .= '_';
            $candidate = mb_substr($row['username'], 0, 25 - strlen($suffix), 'UTF-8') . $suffix;
        }
        $pdo->prepare('INSERT INTO rpgdx_migration_username_changes VALUES (?, ?, ?)')->execute([$row['user_id'], $row['username'], $candidate]);
        $pdo->prepare('UPDATE phpbb_users SET username=? WHERE user_id=?')->execute([$candidate, $row['user_id']]);
        $occupied[utf8_clean_string($candidate)] = true;
        $count++;
    }
    $seen[$clean] = true;
}
echo "Prepared isolated source: $count duplicate usernames renamed, originals retained in audit table\n";
