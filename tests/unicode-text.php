<?php
// These checks load pure helpers only; no database or phpBB bootstrap is used.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
define('IN_RPGDX', true);
require dirname(__DIR__) . '/includes/functions.php';
$expect = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException('Unicode text test failed: ' . $message);
    }
};
$examples = [
    ['unchanged text', 'A short description', 30, 'A short description'],
    ['unchanged whitespace', '  café  ', 20, '  café  '],
    ['ASCII word boundary', 'alpha beta gamma', 12, 'alpha beta...'],
    ['ASCII without spaces', 'abcdefghijk', 5, 'abcde...'],
    ['CJK without spaces', '日本語の説明文です', 4, '日本語の...'],
    ['emoji without spaces', '🎮🙂🌍🎉🔥', 3, '🎮🙂🌍...'],
    ['Unicode word boundary', 'été fête 東京 plus', 13, 'été fête 東京...'],
    ['trimmed truncation', '  東京日本語  ', 3, '東京日...'],
    ['exact Unicode limit', '東京🎮', 3, '東京🎮'],
    ['empty text', '', 10, ''],
    ['zero limit', '日', 0, '...'],
];
foreach ($examples as [$name, $text, $limit, $expected]) {
    $actual = shorten_text($text, $limit);
    $expect($actual === $expected, $name);
    $expect(mb_check_encoding($actual, 'UTF-8'), "$name retains valid UTF-8");
}
echo "PASS Unicode truncation: word boundaries, complete CJK/emoji and no lost final character\n";

$quotedUrl = 'https://example.com/article?q="quoted"&author=O\'Brien';
$expect(rpgdx_is_http_url('http://example.com/'), 'HTTP article URLs remain valid');
$expect(rpgdx_is_http_url($quotedUrl), 'HTTPS URLs with query values remain valid');
$expect(
    rpgdx_http_url_attribute($quotedUrl) === 'https://example.com/article?q=&quot;quoted&quot;&amp;author=O&#039;Brien',
    'URL attributes encode quotes and ampersands'
);
foreach (['javascript:alert(1)', "\tjavascript:alert(1)", '//example.com/path', 'data:text/html,unsafe'] as $unsafeUrl) {
    $expect(!rpgdx_is_http_url($unsafeUrl), "$unsafeUrl is not an allowed HTTP URL");
    $expect(rpgdx_http_url_attribute($unsafeUrl) === null, "$unsafeUrl cannot reach an href attribute");
}
$expect(
    rpgdx_html_attribute('<svg onload="alert(1)"> O\'Brien & café') ===
        '&lt;svg onload=&quot;alert(1)&quot;&gt; O&#039;Brien &amp; café',
    'caption markup remains inert attribute text'
);
echo "PASS HTML boundaries: HTTP URL allowlist and quoted attribute encoding\n";

foreach (['type_cast_helper_interface', 'request_interface', 'type_cast_helper', 'request'] as $class) {
    require dirname(__DIR__) . '/forums/phpbb/request/' . $class . '.php';
}
$originalPost = $_POST;
$originalGet = $_GET;
$originalRequest = $_REQUEST;
try {
    $caption = "Reviewer's screenshot: café naïve 東京 中文 🎮";
    $boundary = "O'Brien 🎮" . str_repeat('東', 41);
    foreach ([[$caption, 41], [$boundary, 50], [$boundary . '🙂', 51]] as [$text, $length]) {
        $_POST = ['screenshot_caption' => $text];
        $_GET = ['screenshot_caption' => 'GET must not replace POST'];
        $_REQUEST = $_POST + $_GET;
        $request = new \phpbb\request\request(null, false);
        // Simulate the site's escaped superglobal after the request snapshot.
        $_POST['screenshot_caption'] = addslashes($text);
        $raw = $request->raw_variable('screenshot_caption', '', \phpbb\request\request_interface::POST);
        $expect($raw === $text, 'raw POST preserves the original caption');
        $expect(mb_strlen($raw, 'UTF-8') === $length, 'caption counts characters before SQL escaping');
    }
    $expect(strlen($caption) === 54, 'the original 41-character regression has 54 bytes');
    $expect(mb_strlen(addslashes($boundary), 'UTF-8') === 51, 'escaping must not consume the 50-character allowance');
} finally {
    $_POST = $originalPost;
    $_GET = $originalGet;
    $_REQUEST = $originalRequest;
}
echo "PASS Caption input: raw POST snapshot and 41/50/51 Unicode character boundaries\n";
