<?php
// Only application tables contain text that RPGDX now accepts as full Unicode.
// Legacy sessions, unknown archives and MediaWiki retain their original encoding.
const RPGDX_UNICODE_TABLES = [
    'rpgdx_articles', 'rpgdx_article_types', 'rpgdx_contests',
    'rpgdx_contest_categories', 'rpgdx_contest_entries', 'rpgdx_contest_votes',
    'rpgdx_log', 'rpgdx_news', 'rpgdx_operating_systems',
    'rpgdx_programming_languages', 'rpgdx_projects', 'rpgdx_project_downloads',
    'rpgdx_project_screenshots', 'rpgdx_project_statusses', 'rpgdx_project_types',
    'rpgdx_reviews', 'rpgdx_themes', 'rpgdx_uploads',
];

function rpgdx_convert_application_table(PDO $pdo, string $table): bool
{
    if (!in_array($table, RPGDX_UNICODE_TABLES, true)) {
        return false;
    }
    // Convert declared character encodings; do not reinterpret bytes or BLOBs.
    $pdo->exec("ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    return true;
}

function rpgdx_application_text_mismatches(PDO $pdo, string $source, string $table): int
{
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $source) || !in_array($table, RPGDX_UNICODE_TABLES, true)) {
        throw new InvalidArgumentException('Invalid application table or source database');
    }
    $quote = static fn($identifier) => '`' . str_replace('`', '``', $identifier) . '`';
    $columns = $pdo->query("SHOW FULL COLUMNS FROM `$source`.`$table`")->fetchAll(PDO::FETCH_ASSOC);
    $checks = [];
    foreach ($columns as $column) {
        if ($column['Collation'] !== null) {
            $name = $quote($column['Field']);
            $checks[] = "NOT (BINARY t.$name <=> BINARY CONVERT(s.$name USING utf8mb4))";
        }
    }
    if (!$checks) {
        return 0;
    }
    $keys = $pdo->query("SHOW KEYS FROM `$source`.`$table` WHERE Key_name='PRIMARY'")->fetchAll(PDO::FETCH_ASSOC);
    if (!$keys) {
        throw new RuntimeException("Cannot verify application text without a primary key: $table");
    }
    $joins = array_map(static function ($key) use ($quote) {
        $name = $quote($key['Column_name']);
        return "t.$name=s.$name";
    }, $keys);
    return (int) $pdo->query("SELECT COUNT(*) FROM `$table` t JOIN `$source`.`$table` s ON " .
        implode(' AND ', $joins) . ' WHERE ' . implode(' OR ', $checks))->fetchColumn();
}
