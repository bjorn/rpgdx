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
		return dirname(__DIR__) . '/templates/modern/' . $filename;
	}
}

$template = new LegacyBbcodeTemplate();
$lang = array('Quote' => 'Quote', 'wrote' => 'wrote');
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

echo "PASS Legacy BBCode: image tokens are revalidated and escaped\n";
