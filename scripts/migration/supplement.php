<?php
/** Copy RPGDX data after the stock converter. Run only from CLI. */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require dirname(__DIR__, 2) . '/forums/config.php';
require __DIR__ . '/application-encoding.php';
error_reporting(E_ALL & ~E_DEPRECATED);
$source = getenv('MIGRATION_SOURCE_DB') ?: 'rpgdx_legacy';
if (!preg_match('/^[a-zA-Z0-9_]+$/', $source) || !preg_match('/^[a-zA-Z0-9_]+$/', $dbname)) {
    throw new RuntimeException('Invalid database identifier');
}
if ($source === $dbname || $table_prefix !== 'phpbb_') {
    throw new RuntimeException('Use a separate source database and phpbb_ target prefix');
}
$port = !empty($dbport) ? ';port=' . (int) $dbport : '';
$pdo = new PDO("mysql:host=$dbhost$port;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpasswd, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec("SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");
$old = '`' . $source . '`';
$scalar = static function ($sql) use ($pdo) { return (int) $pdo->query($sql)->fetchColumn(); };
$check = static function ($name, $bad) {
    if ($bad) {
        throw new RuntimeException("Check failed: $name ($bad rows)");
    }
    echo "PASS $name\n";
};
$check('official conversion finished', $scalar("SELECT COUNT(*) FROM phpbb_config WHERE config_name = 'convert_progress'"));
$check('all source posts converted', abs($scalar("SELECT COUNT(*) FROM $old.phpbb_posts") - $scalar('SELECT COUNT(*) FROM phpbb_posts')));
$check('all source topics converted', abs($scalar("SELECT COUNT(*) FROM $old.phpbb_topics") - $scalar('SELECT COUNT(*) FROM phpbb_topics')));

// This is the exact user mapping implemented by stock phpbb_user_id().
$max = $scalar("SELECT MAX(user_id) FROM $old.phpbb_users");
$hasOne = $scalar("SELECT COUNT(*) FROM $old.phpbb_users WHERE user_id = 1");
$mappedOne = $hasOne ? $max + 1 : 1;
$pdo->exec('CREATE TABLE IF NOT EXISTS rpgdx_user_id_map (legacy_user_id INT NOT NULL PRIMARY KEY, user_id INT UNSIGNED NOT NULL UNIQUE) ENGINE=InnoDB');
$pdo->exec('DELETE FROM rpgdx_user_id_map');
$pdo->exec("INSERT INTO rpgdx_user_id_map SELECT user_id, CASE WHEN user_id = -1 THEN 1 WHEN user_id = 1 THEN $mappedOne ELSE user_id END FROM $old.phpbb_users");
$check('all converted users exist', $scalar('SELECT COUNT(*) FROM rpgdx_user_id_map m LEFT JOIN phpbb_users u ON u.user_id=m.user_id WHERE u.user_id IS NULL'));
$check('post authors mapped', $scalar("SELECT COUNT(*) FROM $old.phpbb_posts s JOIN rpgdx_user_id_map m ON m.legacy_user_id=s.poster_id JOIN phpbb_posts t ON t.post_id=s.post_id WHERE t.poster_id<>m.user_id"));
$check('private message count', abs($scalar("SELECT COUNT(*) FROM $old.phpbb_privmsgs s JOIN $old.phpbb_privmsgs_text x ON x.privmsgs_text_id=s.privmsgs_id") - $scalar('SELECT COUNT(*) FROM phpbb_privmsgs')));
$check('private message authors mapped', $scalar("SELECT COUNT(*) FROM $old.phpbb_privmsgs s JOIN rpgdx_user_id_map m ON m.legacy_user_id=s.privmsgs_from_userid JOIN phpbb_privmsgs t ON t.msg_id=s.privmsgs_id WHERE t.author_id<>m.user_id"));
$check('private message recipient rows', abs($scalar("SELECT COUNT(*) + SUM(privmsgs_type IN (1,5)) FROM $old.phpbb_privmsgs s JOIN $old.phpbb_privmsgs_text x ON x.privmsgs_text_id=s.privmsgs_id") - $scalar('SELECT COUNT(*) FROM phpbb_privmsgs_to')));
$check('poll options', abs($scalar("SELECT COUNT(*) FROM $old.phpbb_vote_results r JOIN $old.phpbb_vote_desc d ON d.vote_id=r.vote_id") - $scalar('SELECT COUNT(*) FROM phpbb_poll_options')));
$check('poll vote totals', abs($scalar("SELECT SUM(vote_result) FROM $old.phpbb_vote_results r JOIN $old.phpbb_vote_desc d ON d.vote_id=r.vote_id") - $scalar('SELECT SUM(poll_option_total) FROM phpbb_poll_options')));
$check('poll voter rows', abs($scalar("SELECT COUNT(*) FROM $old.phpbb_vote_voters v JOIN $old.phpbb_vote_desc d ON d.vote_id=v.vote_id") - $scalar('SELECT COUNT(*) FROM phpbb_poll_votes')));
$check('custom group memberships', $scalar("SELECT COUNT(*) FROM $old.phpbb_user_group s JOIN $old.phpbb_groups g ON g.group_id=s.group_id JOIN rpgdx_user_id_map m ON m.legacy_user_id=s.user_id LEFT JOIN phpbb_user_group t ON t.group_id=s.group_id AND t.user_id=m.user_id WHERE g.group_single_user=0 AND t.user_id IS NULL"));
require dirname(__DIR__, 2) . '/forums/phpbb/class_loader.php';
$loader = new \phpbb\class_loader('phpbb\\', dirname(__DIR__, 2) . '/forums/phpbb/');
$loader->register();
$passwordConfig = new \phpbb\config\config([]);
$passwordHelper = new \phpbb\passwords\driver\helper($passwordConfig);
$passwordAlgorithms = [];
foreach (['salted_md5', 'phpass', 'bcrypt', 'bcrypt_2y', 'argon2i', 'argon2id'] as $algorithm) {
    $class = '\\phpbb\\passwords\\driver\\' . $algorithm;
    $passwordAlgorithms[$algorithm] = new $class($passwordConfig, $passwordHelper);
}
$passwordManager = new \phpbb\passwords\manager($passwordConfig, $passwordAlgorithms, new \phpbb\passwords\helper(), ['bcrypt_2y']);
$passwordFailures = 0;
$passwordRows = $pdo->query("SELECT s.user_password AS legacy_hash, u.user_password AS converted_hash FROM $old.phpbb_users s JOIN rpgdx_user_id_map m ON m.legacy_user_id=s.user_id JOIN phpbb_users u ON u.user_id=m.user_id WHERE s.user_id > 0");
while ($row = $passwordRows->fetch(PDO::FETCH_ASSOC)) {
    // Stock cron may strengthen the initial wrapper into a combined hash.
    $hash = str_starts_with($row['converted_hash'], '$CP$') ? substr($row['converted_hash'], 4) : $row['converted_hash'];
    if (!$passwordManager->check($row['legacy_hash'], $hash)) {
        $passwordFailures++;
    }
}
$check('all legacy passwords accepted by converted hashes', $passwordFailures);
$encodingSamples = 0;
$encodingFailures = 0;
$rows = $pdo->query("SELECT x.post_text AS source_text, t.post_text AS target_text FROM $old.phpbb_posts_text x JOIN phpbb_posts t ON t.post_id=x.post_id WHERE x.post_text REGEXP '[^ -~]' LIMIT 1000");
while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
    if (preg_match('/[a-zA-Z]*[\\x{00C0}-\\x{02AF}]+[a-zA-Z]*/u', $row['source_text'], $match) && mb_strlen($match[0], 'UTF-8') > 2) {
        $encodingSamples++;
        if (!str_contains($row['target_text'], $match[0])) {
            $encodingFailures++;
        }
        if ($encodingSamples >= 25) {
            break;
        }
    }
}
$check("accented text encoding ($encodingSamples samples)", $encodingFailures);
$pdo->exec("UPDATE phpbb_users SET user_avatar_type=CASE user_avatar_type WHEN '1' THEN 'avatar.driver.upload' WHEN '2' THEN 'avatar.driver.remote' WHEN '3' THEN 'avatar.driver.local' WHEN '0' THEN '' ELSE user_avatar_type END");
$check('avatar driver assignments preserved', $scalar("SELECT COUNT(*) FROM phpbb_users t JOIN rpgdx_user_id_map m ON m.user_id=t.user_id JOIN $old.phpbb_users s ON s.user_id=m.legacy_user_id WHERE s.user_avatar_type IN (1,2,3) AND t.user_avatar_type <> CASE s.user_avatar_type WHEN 1 THEN 'avatar.driver.upload' WHEN 2 THEN 'avatar.driver.remote' WHEN 3 THEN 'avatar.driver.local' END"));
$settings = $pdo->query("SELECT config_name, config_value FROM phpbb_config WHERE config_name IN ('avatar_salt','avatar_path')")->fetchAll(PDO::FETCH_KEY_PAIR);
$missingAvatars = 0;
$avatarCount = 0;
$missingAssets = [];
foreach ($pdo->query("SELECT t.user_id, t.user_avatar, s.user_avatar AS source_avatar FROM phpbb_users t JOIN rpgdx_user_id_map m ON m.user_id=t.user_id JOIN $old.phpbb_users s ON s.user_id=m.legacy_user_id WHERE t.user_avatar_type='avatar.driver.upload'") as $row) {
    $avatarCount++;
    $path = dirname(__DIR__, 2) . '/forums/' . $settings['avatar_path'] . '/' . $settings['avatar_salt'] . '_' . $row['user_avatar'];
    $legacyPath = (getenv('MIGRATION_LEGACY_FORUMS') ?: dirname(__DIR__, 2) . '/legacy-forums') . '/images/avatars/' . basename($row['source_avatar']);
    // A restored source asset may arrive after the converter's first pass.
    if (!is_file($path) && is_file($legacyPath) && basename($row['source_avatar']) === $row['source_avatar']) {
        if (!copy($legacyPath, $path)) {
            throw new RuntimeException('Unable to copy a restored source avatar');
        }
        chmod($path, 0644);
    }
    if (!is_file($path)) {
        $missingAvatars++;
        $missingAssets[] = [$row['user_id'], $row['source_avatar'], $row['user_avatar']];
    }
}
$check('uploaded avatar assignment count', abs($scalar("SELECT COUNT(*) FROM $old.phpbb_users WHERE user_avatar_type=1") - $avatarCount));
if ($missingAvatars) {
    echo "WARN $missingAvatars of $avatarCount uploaded avatars have unavailable source files; references retained in rpgdx_migration_missing_assets\n";
} else {
    $check("uploaded avatar files copied ($avatarCount checked)", 0);
}

// Copy application and wiki tables, including archives, but not obsolete sessions.
$tables = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = " . $pdo->quote($source) . " AND (LEFT(TABLE_NAME, 6) = 'rpgdx_' OR LEFT(TABLE_NAME, 3) = 'mw_') ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
        throw new RuntimeException('Invalid source table');
    }
    if ($table === 'rpgdx_sessions') {
        continue;
    }
    $pdo->exec("DROP TABLE IF EXISTS `$table`");
    $pdo->exec("CREATE TABLE `$table` LIKE $old.`$table`");
    $unicode = rpgdx_convert_application_table($pdo, $table);
    $pdo->exec("INSERT INTO `$table` SELECT * FROM $old.`$table`");
    $check("$table row count", abs($scalar("SELECT COUNT(*) FROM `$table`") - $scalar("SELECT COUNT(*) FROM $old.`$table`")));
    if ($unicode) {
        $check("$table Unicode text preserved", rpgdx_application_text_mismatches($pdo, $source, $table));
    }
}
$pdo->exec('CREATE TABLE IF NOT EXISTS rpgdx_migration_missing_assets (user_id INT UNSIGNED NOT NULL PRIMARY KEY, source_avatar VARCHAR(255) NOT NULL, converted_avatar VARCHAR(255) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
$pdo->exec('DELETE FROM rpgdx_migration_missing_assets');
$missingInsert = $pdo->prepare('INSERT INTO rpgdx_migration_missing_assets VALUES (?, ?, ?)');
foreach ($missingAssets as $asset) {
    $missingInsert->execute($asset);
}
$activeUploadSql = 'SELECT project_icon_file AS upload_id FROM rpgdx_projects WHERE project_icon_file IS NOT NULL AND project_icon_file>0 UNION SELECT upload_id FROM rpgdx_project_screenshots';
$activeUploads = array_fill_keys($pdo->query($activeUploadSql)->fetchAll(PDO::FETCH_COLUMN), true);
$check('active upload metadata present', $scalar("SELECT COUNT(*) FROM ($activeUploadSql) r LEFT JOIN rpgdx_uploads u ON u.upload_id=r.upload_id WHERE u.upload_id IS NULL"));
$pdo->exec('CREATE TABLE IF NOT EXISTS rpgdx_migration_missing_uploads (upload_id INT NOT NULL PRIMARY KEY, upload_filename VARCHAR(255) NOT NULL, active_reference TINYINT NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
$pdo->exec('DELETE FROM rpgdx_migration_missing_uploads');
$missingUpload = $pdo->prepare('INSERT INTO rpgdx_migration_missing_uploads VALUES (?, ?, ?)');
$uploadCount = 0;
$missingUploadCount = 0;
$missingActiveUploads = 0;
foreach ($pdo->query("SELECT upload_id, CONCAT('uploads/', upload_type, upload_id, upload_ext) AS filename FROM rpgdx_uploads") as $upload) {
    $uploadCount++;
    if (!is_file(dirname(__DIR__, 2) . '/' . $upload['filename'])) {
        $missingUploadCount++;
        $active = isset($activeUploads[$upload['upload_id']]) ? 1 : 0;
        $missingActiveUploads += $active;
        $missingUpload->execute([$upload['upload_id'], $upload['filename'], $active]);
    }
}
if ($missingUploadCount) {
    echo "WARN $missingUploadCount of $uploadCount RPGDX upload registry files unavailable ($missingActiveUploads actively referenced); see rpgdx_migration_missing_uploads\n";
} else {
    $check("RPGDX upload files present ($uploadCount checked)", 0);
}
if (!$missingActiveUploads) {
    $check('active project image files present (' . count($activeUploads) . ' checked)', 0);
}
define('IN_RPGDX', true);
require dirname(__DIR__, 2) . '/includes/functions.php';
$thumbnailCount = 0;
$regeneratedThumbnails = 0;
foreach ($pdo->query("SELECT DISTINCT CONCAT('uploads/',u.upload_type,u.upload_id,u.upload_ext) AS filename FROM rpgdx_project_screenshots s JOIN rpgdx_uploads u ON u.upload_id=s.upload_id") as $screenshot) {
    $path = dirname(__DIR__, 2) . '/' . $screenshot['filename'];
    $thumbnailCount++;
    if (!is_file($path . '.thumb.jpg') && is_file($path)) {
        create_thumbnail($path);
        $regeneratedThumbnails++;
    }
}
echo "Screenshot thumbnails checked: $thumbnailCount; regenerated from original images: $regeneratedThumbnails\n";
foreach (['rpgdx_articles' => 'user_id', 'rpgdx_news' => 'user_id', 'rpgdx_projects' => 'user_id', 'rpgdx_reviews' => 'user_id', 'rpgdx_contest_votes' => 'vote_user'] as $table => $column) {
    $pdo->exec("UPDATE `$table` t JOIN rpgdx_user_id_map m ON m.legacy_user_id=t.`$column` SET t.`$column`=m.user_id");
    // Existing references to deleted users remain historical references.
    $check("$table ownership mapping", $scalar("SELECT COUNT(*) FROM `$table` t JOIN $old.`$table` s USING (" . ['rpgdx_articles'=>'article_id', 'rpgdx_news'=>'news_id', 'rpgdx_projects'=>'project_id', 'rpgdx_reviews'=>'review_id', 'rpgdx_contest_votes'=>'vote_id'][$table] . ") JOIN rpgdx_user_id_map m ON m.legacy_user_id=s.`$column` WHERE t.`$column` <> m.user_id"));
}
require __DIR__ . '/contest-integrity-schema.php';
rpgdx_migrate_contest_integrity_schema($pdo);
$pdo->exec('CREATE TABLE IF NOT EXISTS rpgdx_user_preferences (user_id INT UNSIGNED NOT NULL PRIMARY KEY, user_theme INT NOT NULL DEFAULT 0, user_projects_sort_method TINYINT UNSIGNED NOT NULL DEFAULT 0, user_projects_last_sort_method TINYINT UNSIGNED NOT NULL DEFAULT 2) ENGINE=InnoDB');
$pdo->exec('DELETE FROM rpgdx_user_preferences');
$pdo->exec("INSERT INTO rpgdx_user_preferences SELECT m.user_id, s.user_theme, s.user_projects_sort_method, s.user_projects_last_sort_method FROM $old.phpbb_users s JOIN rpgdx_user_id_map m ON m.legacy_user_id=s.user_id");
$check('all custom preferences copied', abs($scalar("SELECT COUNT(*) FROM $old.phpbb_users") - $scalar('SELECT COUNT(*) FROM rpgdx_user_preferences')));
$check('custom preference values mapped', $scalar("SELECT COUNT(*) FROM rpgdx_user_preferences t JOIN rpgdx_user_id_map m ON m.user_id=t.user_id JOIN $old.phpbb_users s ON s.user_id=m.legacy_user_id WHERE t.user_theme<>s.user_theme OR t.user_projects_sort_method<>s.user_projects_sort_method OR t.user_projects_last_sort_method<>s.user_projects_last_sort_method"));

// Legacy timestamps use an inclusive unread boundary, phpBB3 uses exclusive.
$pdo->exec("UPDATE phpbb_users u JOIN rpgdx_user_id_map m ON m.user_id=u.user_id JOIN $old.phpbb_users s ON s.user_id=m.legacy_user_id SET u.user_lastmark=GREATEST(0, s.user_lastread - 1) WHERE s.user_id > 0");
$pdo->exec('DELETE FROM phpbb_topics_track');
$pdo->exec("INSERT INTO phpbb_topics_track (user_id, topic_id, forum_id, mark_time) SELECT m.user_id, r.read_topic_id, t.forum_id, GREATEST(0, MAX(r.read_time) - 1) FROM $old.phpbb_reads r JOIN rpgdx_user_id_map m ON m.legacy_user_id=r.read_user_id JOIN phpbb_topics t ON t.topic_id=r.read_topic_id WHERE m.user_id <> 1 GROUP BY m.user_id, r.read_topic_id, t.forum_id");
$check('valid native read markers', $scalar('SELECT COUNT(*) FROM phpbb_topics_track tt LEFT JOIN phpbb_users u ON u.user_id=tt.user_id LEFT JOIN phpbb_topics t ON t.topic_id=tt.topic_id WHERE u.user_id IS NULL OR t.topic_id IS NULL'));
$check('all surviving read markers copied', abs($scalar("SELECT COUNT(*) FROM (SELECT m.user_id, r.read_topic_id FROM $old.phpbb_reads r JOIN rpgdx_user_id_map m ON m.legacy_user_id=r.read_user_id JOIN phpbb_topics t ON t.topic_id=r.read_topic_id WHERE m.user_id<>1 GROUP BY m.user_id, r.read_topic_id) reads_to_copy") - $scalar('SELECT COUNT(*) FROM phpbb_topics_track')));
$pdo->exec("UPDATE phpbb_config SET config_value='1' WHERE config_name='load_db_lastread'");
// Sessions are intentionally expired when switching authentication systems.
$pdo->exec('DROP TABLE IF EXISTS rpgdx_sessions');
$pdo->exec('DELETE FROM phpbb_sessions');
$pdo->exec('DELETE FROM phpbb_sessions_keys');
echo 'Supplemental migration completed; source database unchanged' . PHP_EOL;
