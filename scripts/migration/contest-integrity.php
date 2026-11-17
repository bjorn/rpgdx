<?php
/** Upgrade an existing RPGDX database for transactional contest mutations. */
if (PHP_SAPI !== 'cli') {
	http_response_code(404);
	exit;
}

require dirname(__DIR__, 2) . '/forums/config.php';
require __DIR__ . '/contest-integrity-schema.php';

if (!preg_match('/^[a-zA-Z0-9_]+$/D', $dbname)) {
	throw new RuntimeException('Invalid target database identifier');
}
$port = !empty($dbport) ? ';port=' . (int) $dbport : '';
$pdo = new PDO(
	"mysql:host=$dbhost$port;dbname=$dbname;charset=utf8mb4",
	$dbuser,
	$dbpasswd,
	array(
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_EMULATE_PREPARES => false,
	)
);

rpgdx_migrate_contest_integrity_schema($pdo);
echo "Contest integrity migration complete; no historical rows were removed\n";
