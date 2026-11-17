<?php
/** Enable the integration style and build the stock MySQL fulltext index. CLI only. */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
define('IN_PHPBB', true);
$phpbb_root_path = dirname(__DIR__, 2) . '/forums/';
$phpEx = 'php';
require $phpbb_root_path . 'common.php';
set_error_handler(static function ($severity, $message) {
    throw new ErrorException(strip_tags($message), 0, $severity);
}, E_USER_ERROR | E_USER_WARNING);
$language->set_default_language($config['default_lang']);
$language->add_lang(['common', 'acp/common', 'acp/search', 'cli']);
$user->data['user_id'] = ANONYMOUS;
$user->ip = '127.0.0.1';
$extensions = $phpbb_container->get('ext.manager');
if (!$extensions->is_enabled('rpgdx/site')) {
    while ($extensions->enable_step('rpgdx/site')) {
    }
}
$result = $db->sql_query("SELECT style_id FROM " . STYLES_TABLE . " WHERE style_path='rpgdx'");
$styleId = (int) $db->sql_fetchfield('style_id');
$db->sql_freeresult($result);
if (!$styleId) {
    $result = $db->sql_query("SELECT * FROM " . STYLES_TABLE . " WHERE style_path='prosilver'");
    $style = $db->sql_fetchrow($result);
    $db->sql_freeresult($result);
    if (!$style) {
        throw new RuntimeException('Missing prosilver parent style');
    }
    $style['style_parent_id'] = $style['style_id'];
    unset($style['style_id']);
    $style['style_name'] = 'RPGDX';
    $style['style_copyright'] = 'RPGDX';
    $style['style_path'] = 'rpgdx';
    $style['style_active'] = 1;
    $style['style_parent_tree'] = 'prosilver';
    $db->sql_query('INSERT INTO ' . STYLES_TABLE . ' ' . $db->sql_build_array('INSERT', $style));
    $styleId = (int) $db->sql_nextid();
}
$config->set('default_style', $styleId);
$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_style=' . $styleId);
$config->set('max_post_font_size', 200);
$config->set('max_sig_font_size', 200);
$error = false;
$search = new \phpbb\search\fulltext_mysql($error, $phpbb_root_path, $phpEx, $auth, $config, $db, $user, $phpbb_dispatcher);
if ($error || ($error = $search->create_index(null, ''))) {
    throw new RuntimeException('Unable to build MySQL fulltext index: ' . $error);
}
$config->set('search_type', '\\phpbb\\search\\fulltext_mysql');
foreach ([SEARCH_WORDMATCH_TABLE, SEARCH_WORDLIST_TABLE] as $table) {
    $db->sql_query('DELETE FROM ' . $table);
}
$cache->purge();
echo "RPGDX style active; MySQL fulltext index complete\n";
