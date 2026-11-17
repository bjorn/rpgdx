<?php
// Exercise the migration helper using connection-local temporary tables only.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
if (getenv('RPGDX_SMOKE_ALLOW_WRITES') !== '1') {
    throw new RuntimeException('Set RPGDX_SMOKE_ALLOW_WRITES=1 for the local encoding test');
}
require dirname(__DIR__) . '/forums/config.php';
if ($dbhost !== 'db' || $dbname !== 'rpgdx') {
    throw new RuntimeException('Encoding tests require the local db host and rpgdx database');
}
require dirname(__DIR__) . '/scripts/migration/application-encoding.php';

$port = !empty($dbport) ? ';port=' . (int) $dbport : '';
$pdo = new PDO("mysql:host=$dbhost$port;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpasswd, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
$pdo->exec("SET SESSION sql_mode='STRICT_ALL_TABLES,NO_AUTO_VALUE_ON_ZERO'");
$expect = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException('Encoding test failed: ' . $message);
    }
};
$schema = static function (string $table) use ($pdo): string {
    return $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
};
$columns = static function (string $table) use ($pdo): array {
    return array_column($pdo->query("SHOW FULL COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC), null, 'Field');
};
$legacyBytes = "caf\xe9 No\xebl \x80";
$decodedText = 'café Noël €';
$binaryBytes = "\x00\xff\x80'\\";
$serializedValue = ['caption' => "caf\xe9", 'bytes' => $binaryBytes];
$serializedBytes = serialize($serializedValue);
$legacySql = "CONVERT(UNHEX('" . bin2hex($legacyBytes) . "') USING latin1)";
$binarySql = "UNHEX('" . bin2hex($binaryBytes) . "')";
$serializedSql = "UNHEX('" . bin2hex($serializedBytes) . "')";
$tables = ['rpgdx_projects', 'rpgdx_project_downloads', 'mw_text', 'rpgdx_sessions', 'rpgdx_unknown_archive'];

try {
    foreach (['rpgdx_projects', 'rpgdx_project_downloads'] as $table) {
        // A temporary table shadows the permanent name only in this connection.
        $pdo->exec("CREATE TEMPORARY TABLE `$table` (
            row_id INT NOT NULL PRIMARY KEY,
            text_value TEXT NULL,
            short_value VARCHAR(32) NULL,
            binary_value VARBINARY(32) NULL,
            serialized_value BLOB NULL,
            numeric_value INT NOT NULL
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci");
        $pdo->exec("INSERT INTO `$table` VALUES (7, $legacySql, $legacySql, $binarySql, $serializedSql, -1), (8, NULL, '', NULL, NULL, 42)");
        $beforeColumns = $columns($table);
        $expect(rpgdx_convert_application_table($pdo, $table), "$table is converted");
        $afterColumns = $columns($table);
        foreach (['text_value', 'short_value'] as $column) {
            $expect($afterColumns[$column]['Collation'] === 'utf8mb4_unicode_ci', "$table.$column accepts full Unicode");
        }
        foreach (['row_id', 'binary_value', 'serialized_value', 'numeric_value'] as $column) {
            $expect($beforeColumns[$column] === $afterColumns[$column], "$table.$column metadata stays unchanged");
        }
        $rows = $pdo->query("SELECT * FROM `$table` ORDER BY row_id")->fetchAll(PDO::FETCH_ASSOC);
        $expect($rows[0]['text_value'] === $decodedText && $rows[0]['short_value'] === $decodedText, "$table decodes declared Latin1 without reinterpreting bytes");
        $expect($rows[0]['binary_value'] === $binaryBytes, "$table retains binary bytes");
        $expect($rows[0]['serialized_value'] === $serializedBytes, "$table retains serialized BLOB bytes");
        $expect(unserialize($rows[0]['serialized_value'], ['allowed_classes' => false]) === $serializedValue, "$table retains valid serialization lengths");
        $expect((int) $rows[0]['row_id'] === 7 && (int) $rows[0]['numeric_value'] === -1 && (int) $rows[1]['numeric_value'] === 42, "$table retains numeric IDs and values");
        $expect($rows[1]['text_value'] === null && $rows[1]['short_value'] === '' && $rows[1]['binary_value'] === null && $rows[1]['serialized_value'] === null, "$table distinguishes null and empty values");
        $expect(str_contains($schema($table), 'ENGINE=MyISAM'), "$table retains its storage engine");

        $unicode = '日本語 🙂 café';
        $insert = $pdo->prepare("INSERT INTO `$table` (row_id, text_value, numeric_value) VALUES (9, ?, 0)");
        $insert->execute([$unicode]);
        $stored = $pdo->query("SELECT text_value, HEX(text_value) AS bytes FROM `$table` WHERE row_id=9")->fetch(PDO::FETCH_ASSOC);
        $expect($stored['text_value'] === $unicode && $stored['bytes'] === strtoupper(bin2hex($unicode)), "$table round-trips CJK and a four-byte emoji");

        $convertedSchema = $schema($table);
        $convertedRows = $pdo->query("SELECT * FROM `$table` ORDER BY row_id")->fetchAll(PDO::FETCH_ASSOC);
        $expect(rpgdx_convert_application_table($pdo, $table), "$table can be converted again");
        $expect($schema($table) === $convertedSchema, "$table schema is idempotent");
        $expect($pdo->query("SELECT * FROM `$table` ORDER BY row_id")->fetchAll(PDO::FETCH_ASSOC) === $convertedRows, "$table data is idempotent");
        echo "PASS $table: Latin1, binary/serialized bytes, nulls, IDs, CJK/emoji and idempotence\n";
    }

    foreach (['mw_text', 'rpgdx_sessions', 'rpgdx_unknown_archive'] as $table) {
        $pdo->exec("CREATE TEMPORARY TABLE `$table` (
            row_id INT NOT NULL PRIMARY KEY,
            data TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci,
            binary_value BLOB
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci");
        $pdo->exec("INSERT INTO `$table` VALUES (1, $serializedSql, $binarySql)");
        $beforeSchema = $schema($table);
        $expect(!rpgdx_convert_application_table($pdo, $table), "$table is excluded");
        $expect($schema($table) === $beforeSchema, "$table archive schema stays unchanged");
        // Fetch raw bytes so the connection does not transcode serialized TEXT.
        $row = $pdo->query("SELECT BINARY data AS payload, binary_value FROM `$table`")->fetch(PDO::FETCH_ASSOC);
        $expect($row['payload'] === $serializedBytes && $row['binary_value'] === $binaryBytes, "$table archive bytes stay unchanged");
        $expect(unserialize($row['payload'], ['allowed_classes' => false]) === $serializedValue, "$table retains valid serialized byte lengths");
        echo "PASS $table: excluded schema and serialized/binary bytes unchanged\n";
    }
} finally {
    foreach ($tables as $table) {
        // TEMPORARY prevents cleanup from ever dropping a permanent table.
        $pdo->exec("DROP TEMPORARY TABLE IF EXISTS `$table`");
    }
}
echo "Application encoding checks complete; permanent tables untouched\n";
