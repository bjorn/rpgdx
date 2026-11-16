<?php
// Create and remove dedicated accounts in the local Docker rehearsal database.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
if (getenv('RPGDX_SMOKE_ALLOW_WRITES') !== '1' || !in_array($argv[1] ?? '', ['create', 'remove'], true)) {
    throw new RuntimeException('Set RPGDX_SMOKE_ALLOW_WRITES=1 and choose create or remove');
}
$phpbb_root_path = dirname(__DIR__) . '/forums/';
require $phpbb_root_path . 'config.php';
if (!in_array($dbhost, ['db', 'localhost', '127.0.0.1'], true) || $dbname !== 'rpgdx') {
    throw new RuntimeException('Fixtures require the local rpgdx rehearsal database');
}
$manifest = getenv('RPGDX_FIXTURE_MANIFEST') ?: sys_get_temp_dir() . '/rpgdx-forum-fixtures.json';
define('IN_PHPBB', true);
$phpEx = 'php';
require $phpbb_root_path . 'common.php';
require $phpbb_root_path . 'includes/functions_user.php';
$user->session_begin();
$auth->acl($user->data);
$user->setup();
if ($config['email_enable']) {
    throw new RuntimeException('Disable email in the disposable board before using fixtures');
}

if ($argv[1] === 'remove') {
    $fixtures = json_decode(file_get_contents($manifest), true, 512, JSON_THROW_ON_ERROR);
    $ids = [];
    foreach ($fixtures as $fixture) {
        $result = $db->sql_query('SELECT username FROM ' . USERS_TABLE . ' WHERE user_id=' . (int) $fixture['id']);
        $name = $db->sql_fetchfield('username');
        $db->sql_freeresult($result);
        if ($name !== $fixture['name'] || !preg_match('/^rpgdx_smoke_[a-z0-9_]+$/i', $name)) {
            throw new RuntimeException('Fixture manifest does not match the local account');
        }
        $ids[] = (int) $fixture['id'];
    }
    user_delete('remove', $ids);
    unlink($manifest);
    $cache->purge();
    echo "Removed temporary users, their forum posts and private messages\n";
    exit;
}

if (is_file($manifest)) {
    throw new RuntimeException('Remove the existing fixture manifest before creating new accounts');
}
$accounts = [];
foreach (['USER', 'ADMIN'] as $role) {
    $name = getenv('RPGDX_TEST_' . $role);
    $password = getenv('RPGDX_TEST_' . $role . '_PASSWORD');
    if ($role === 'USER') {
        $password = getenv('RPGDX_TEST_PASSWORD');
    }
    if (!$name || !$password || !preg_match('/^rpgdx_smoke_[a-z0-9_]+$/i', $name)) {
        throw new RuntimeException('Provide credentials with temporary rpgdx_smoke_ usernames');
    }
    $result = $db->sql_query('SELECT user_id FROM ' . USERS_TABLE . " WHERE username_clean='" . $db->sql_escape(utf8_clean_string($name)) . "'");
    $exists = $db->sql_fetchfield('user_id');
    $db->sql_freeresult($result);
    if ($exists) {
        throw new RuntimeException('Refusing to overwrite an existing username');
    }
    $accounts[] = ['name' => $name, 'password' => $password, 'admin' => $role === 'ADMIN'];
}
if (strtolower($accounts[0]['name']) === strtolower($accounts[1]['name'])) {
    throw new RuntimeException('Use different member and admin usernames');
}
$result = $db->sql_query("SELECT group_id FROM " . GROUPS_TABLE . " WHERE group_name='REGISTERED' AND group_type=" . GROUP_SPECIAL);
$registered = (int) $db->sql_fetchfield('group_id');
$db->sql_freeresult($result);
if (!$registered) {
    throw new RuntimeException('Missing registered-user group');
}
$fixtures = [];
foreach ($accounts as $account) {
    $id = user_add([
        'username' => $account['name'],
        'user_password' => $phpbb_container->get('passwords.manager')->hash($account['password']),
        'user_email' => $account['name'] . '@example.invalid',
        'group_id' => $registered,
        'user_type' => $account['admin'] ? USER_FOUNDER : USER_NORMAL,
        'user_ip' => '127.0.0.1',
        'user_notify_pm' => 0,
        'user_allow_massemail' => 0,
    ]);
    if (!$id) {
        throw new RuntimeException('Unable to create a temporary account');
    }
    $fixtures[] = ['id' => $id, 'name' => $account['name']];
    file_put_contents($manifest, json_encode($fixtures, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n");
}
$cache->purge();
echo "Created temporary member and founder accounts\n";
