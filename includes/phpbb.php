<?php
if (!defined('IN_RPGDX')) {
    exit;
}

// phpBB owns authentication, cookies, sessions, bans and permissions.
define('IN_PHPBB', true);
$phpbb_root_path = './forums/';
$phpEx = 'php';
require $phpbb_root_path . 'common.php';
$request->enable_super_globals();
$user->session_begin();
$auth->acl($user->data);
$user->setup();
$phpbb_template = $template;

// Preserve the small API expected by the separate RPGDX application.
define('ADMIN', 1);
$userdata = $user->data;
$userdata['session_logged_in'] = !empty($user->data['is_registered']);
$userdata['user_level'] = $userdata['session_logged_in'] && $auth->acl_get('a_') ? ADMIN : 0;
// The site's existing query builders expect escaped request strings, as in phpBB2.
// Keep phpBB's request object untouched so its authentication API receives raw input.
function rpgdx_escape_request($value)
{
    global $db;
    if (is_array($value)) {
        return array_map('rpgdx_escape_request', $value);
    }
    return mysqli_real_escape_string($db->get_db_connect_id(), (string) $value);
}
$_GET = array_map('rpgdx_escape_request', $_GET);
$_POST = array_map('rpgdx_escape_request', $_POST);

$lang = array('Quote' => 'Quote', 'wrote' => 'wrote', 'Code' => 'Code');

require_once __DIR__ . '/legacy/template.php';
require_once __DIR__ . '/legacy/bbcode.php';
