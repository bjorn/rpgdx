<?php
// These checks load the pure highlighting helper only; no database or phpBB bootstrap is used.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
define('IN_RPGDX', true);
require dirname(__DIR__) . '/includes/functions.php';
$expect = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException('Search highlighting test failed: ' . $message);
    }
};
$examples = [
    ['ordinary case-insensitive matches', 'Quest qUeSt', ['quest'], '<u>Quest</u> <u>qUeSt</u>'],
    ['regex operators remain literal', 'Find [ab]+$ then letters', ['[ab]+$'], 'Find <u>[ab]+$</u> then letters'],
    ['multiple literal keywords', 'C++ and RPG.DX', ['c++', 'rpg.dx'], '<u>C++</u> and <u>RPG.DX</u>'],
    ['no keywords leave text unchanged', 'Plain project name', [], 'Plain project name'],
];
foreach ($examples as [$name, $text, $words, $expected]) {
    $expect(highlight($text, $words) === $expected, $name);
}
echo "PASS Search highlighting: literal patterns and case-insensitive keyword matches\n";
