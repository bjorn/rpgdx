<?php
// These checks load the legacy parser only; no database or phpBB bootstrap is used.
if (PHP_SAPI !== 'cli') {
	http_response_code(404);
	exit;
}

define('IN_PHPBB', true);
require dirname(__DIR__) . '/includes/legacy/bbcode.php';

final class LegacyBbcodeTemplate
{
	public function make_filename($filename)
	{
		global $template_name;
		return dirname(__DIR__) . '/templates/' . $template_name . '/' . $filename;
	}
}

$template_name = $argv[1] ?? 'modern';
if (!in_array($template_name, array('modern', 'classic'), true)) {
	throw new RuntimeException('Unknown template');
}
$template = new LegacyBbcodeTemplate();
$lang = array('Quote' => 'Quote', 'wrote' => 'wrote', 'Code' => 'Code');
$expect = static function ($condition, $message) {
	if (!$condition) {
		throw new RuntimeException('Legacy BBCode test failed: ' . $message);
	}
};

$uid = 'deadbeef00';
$malicious = '[img:' . $uid . ']https://example.test/image.jpg" onerror="alert(1)[/img:' . $uid . ']';
$rendered = \Rpgdx\Legacy\bbencode_second_pass($malicious, $uid);
$expect($rendered === $malicious, 'quote-bearing pretagged images remain inert text');
$expect(strpos($rendered, '<img') === false, 'quote-bearing pretagged images do not create elements');

$regex_uid = 'dead.beef0';
$regex_token = '[img:' . $regex_uid . ']https://example.test/image.png[/img:' . $regex_uid . ']';
$rendered = \Rpgdx\Legacy\bbencode_second_pass($regex_token, $regex_uid);
$expect(strpos($rendered, 'src="https://example.test/image.png"') !== false, 'UID punctuation is treated literally');

$apostrophe_token = '[img:' . $uid . "]https://example.test/user's.png[/img:" . $uid . ']';
$rendered = \Rpgdx\Legacy\bbencode_second_pass($apostrophe_token, $uid);
$expect(strpos($rendered, 'src="https://example.test/user&#039;s.png"') !== false, 'accepted URLs are attribute encoded');

$stored = \Rpgdx\Legacy\bbencode_first_pass('[img]https://example.test/image.png[/img]', $uid);
$rendered = \Rpgdx\Legacy\bbencode_second_pass($stored, $uid);
$expect(strpos($rendered, 'src="https://example.test/image.png"') !== false, 'ordinary HTTPS images still render');
$expect(strpos($rendered, 'onerror=') === false, 'ordinary images gain no executable attributes');

foreach (array('http', 'https', 'ftp', 'ftps') as $scheme) {
	foreach (array('jpg', 'jpeg', 'gif', 'png', 'PNG') as $extension) {
		$url = $scheme . '://example.test/image.' . $extension;
		$stored = \Rpgdx\Legacy\bbencode_first_pass('[img]' . $url . '[/img]', $uid);
		$rendered = \Rpgdx\Legacy\bbencode_second_pass($stored, $uid);
		$expect(strpos($rendered, 'src="' . $url . '"') !== false, 'supported image schemes and extensions remain supported');
	}
}

foreach (array(
	'https://example.test/image.jpg" onerror="alert(1)',
	'https://example.test/<svg/onload=alert(1)>.png',
	"https://example.test/image.png\nonerror=alert(1)",
	'data:image/png;base64,AAAA',
	'javascript:alert(1).png',
) as $url) {
	$rendered = \Rpgdx\Legacy\bbencode_second_pass('[img:' . $uid . ']' . $url . '[/img:' . $uid . ']', $uid);
	$expect(strpos($rendered, '<img') === false, 'unsafe pretagged image URLs do not create images');
}

$nested = '[img:' . $uid . ']https://example.test/[url=https://example.test]nested[/url].png[/img:' . $uid . ']';
$rendered = \Rpgdx\Legacy\bbencode_second_pass($nested, $uid);
$expect(strpos($rendered, '<img') === false, 'nested URL BBCode cannot introduce markup into an image attribute');

foreach (array('javascript', 'vbscript', 'data', 'file', 'custom') as $scheme) {
	foreach (array('[url]' . $scheme . '://example.test/payload[/url]', '[url=' . $scheme . '://example.test/payload]link[/url]') as $token) {
		$rendered = \Rpgdx\Legacy\make_clickable(\Rpgdx\Legacy\bbencode_second_pass($token, $uid));
		$expect(strpos($rendered, '<a ') === false, 'unsafe or unknown schemes never create BBCode links');
	}
	$expect(strpos(\Rpgdx\Legacy\make_clickable($scheme . '://example.test/payload'), '<a ') === false, 'unsafe or unknown schemes never create automatic links');
}
foreach (array('http', 'https', 'ftp', 'ftps') as $scheme) {
	$url = $scheme . '://example.test/path';
	foreach (array('[url]' . $url . '[/url]', '[url=' . $url . ']link[/url]') as $token) {
		$rendered = \Rpgdx\Legacy\bbencode_second_pass($token, $uid);
		$expect(strpos($rendered, 'href="' . $url . '"') !== false, 'supported BBCode link schemes remain supported');
	}
	$expect(strpos(\Rpgdx\Legacy\make_clickable($url), 'href="' . $url . '"') !== false, 'supported automatic link schemes remain supported');
}

// The old first pass rejected query suffixes; keep that behavior explicit.
$query = '[img]https://example.test/image.png?size=1&other=2[/img]';
$expect(\Rpgdx\Legacy\bbencode_first_pass($query, $uid) === $query, 'query suffixes remain unsupported');

echo "PASS Legacy BBCode ($template_name): image tokens are revalidated and escaped\n";
