<?php
// Compatibility routes for bookmarked phpBB2 URLs. No session is required.
$script = basename($_SERVER['SCRIPT_NAME']);
$mode = is_string($_GET['mode'] ?? null) ? $_GET['mode'] : '';
$userId = max(0, (int) ($_GET['u'] ?? 0));
// phpBB3 reserves user 1 for Anonymous; the converter relocates that account.
if ($userId === 1) {
    require dirname(__DIR__, 2) . '/forums/config.php';
    $connection = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname, (int) ($dbport ?: 3306));
    $result = $connection->query('SELECT user_id FROM rpgdx_user_id_map WHERE legacy_user_id = 1');
    if ($row = $result->fetch_assoc()) {
        $userId = (int) $row['user_id'];
    }
    $connection->close();
}
$params = [];
switch ($script) {
    case 'profile.php':
        if ($mode === 'register') {
            $target = 'ucp.php';
            $params = ['mode' => 'register'];
        } elseif ($mode === 'sendpassword') {
            $target = 'ucp.php';
            $params = ['mode' => 'sendpassword'];
        } elseif ($mode === 'editprofile') {
            $target = 'ucp.php';
            $params = ['i' => 'ucp_profile', 'mode' => 'profile_info'];
        } else {
            $target = 'memberlist.php';
            $params = ['mode' => 'viewprofile', 'u' => $userId];
        }
        break;
    case 'privmsg.php':
        $target = 'ucp.php';
        $params = ['i' => 'pm'];
        if ($mode === 'post') {
            $params += ['mode' => 'compose', 'u' => $userId];
        } elseif ($mode === 'read' && isset($_GET['p'])) {
            $params += ['mode' => 'view', 'p' => max(0, (int) $_GET['p'])];
        } else {
            $folders = ['inbox' => 0, 'outbox' => -2, 'sentbox' => -1];
            $folder = is_string($_GET['folder'] ?? null) ? $_GET['folder'] : 'inbox';
            $params += ['mode' => 'view', 'f' => $folders[$folder] ?? 0];
        }
        break;
    case 'groupcp.php':
        $target = 'memberlist.php';
        $params = ['mode' => 'group', 'g' => max(0, (int) ($_GET['g'] ?? 0))];
        break;
    default:
        $target = 'ucp.php';
        $params = ['mode' => 'login'];
        if (isset($_GET['logout'])) {
            $params = ['mode' => 'logout', 'sid' => is_string($_GET['sid'] ?? null) ? $_GET['sid'] : ''];
        }
}
header('Location: ' . $target . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986), true, 302);
exit;
