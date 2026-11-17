<?php
// Exact-ID fixtures for the disposable local contest browser test.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$action = $argv[1] ?? '';
if (getenv('RPGDX_SMOKE_ALLOW_WRITES') !== '1' || !in_array($action, array('create', 'state', 'remove'), true)) {
    throw new RuntimeException('Opt into local writes and choose create, state or remove');
}
require dirname(__DIR__) . '/forums/config.php';
if ($dbhost !== 'db' || $dbname !== 'rpgdx') {
    throw new RuntimeException('Contest fixtures require the disposable local db/rpgdx database');
}
$pdo = new PDO("mysql:host=$dbhost;port=" . (int) ($dbport ?: 3306) . ";dbname=$dbname;charset=utf8mb4", $dbuser, $dbpasswd, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$manifest = getenv('RPGDX_CONTEST_FIXTURE_MANIFEST') ?: sys_get_temp_dir() . '/rpgdx-contest-csrf-fixtures.json';
$usersManifest = getenv('RPGDX_FIXTURE_MANIFEST') ?: sys_get_temp_dir() . '/rpgdx-forum-fixtures.json';
if ($action === 'remove' && !file_exists($manifest)) {
    echo "No contest fixtures to remove\n";
    exit;
}
$users = json_decode(file_get_contents($usersManifest), true, 512, JSON_THROW_ON_ERROR);
$member = $users[0];
$query = $pdo->prepare('SELECT username FROM phpbb_users WHERE user_id=?');
$query->execute(array($member['id']));
if ($query->fetchColumn() !== $member['name'] || !preg_match('/^rpgdx_smoke_[a-z0-9_]+$/i', $member['name'])) {
    throw new RuntimeException('Contest fixture owner must match the temporary account manifest');
}
$save = function (array $data) use ($manifest): void {
    if (file_put_contents($manifest, json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n") === false) {
        throw new RuntimeException('Unable to save contest fixture IDs');
    }
};
$insert = function (string $table, array $values) use ($pdo): int {
    $statement = $pdo->prepare('INSERT INTO ' . $table . ' (' . implode(',', array_keys($values)) . ') VALUES (' . implode(',', array_fill(0, count($values), '?')) . ')');
    $statement->execute(array_values($values));
    return (int) $pdo->lastInsertId();
};
if ($action === 'create') {
    if (file_exists($manifest)) {
        throw new RuntimeException('Remove the existing contest fixtures before creating more');
    }
    $statement = $pdo->prepare('SELECT * FROM rpgdx_user_preferences WHERE user_id=?');
    $statement->execute(array($member['id']));
    $fixture = array('user_id' => (int) $member['id'], 'marker' => 'rpgdx_csrf_' . bin2hex(random_bytes(8)), 'preference' => $statement->fetch(PDO::FETCH_ASSOC), 'contests' => array(), 'categories' => array(), 'entries' => array());
    $save($fixture);
    $fixture['project'] = $insert('rpgdx_projects', array('user_id' => $fixture['user_id'], 'project_name' => $fixture['marker'], 'project_added' => date('Y-m-d H:i:s')));
    $save($fixture);
    foreach (array('open' => 0, 'classic' => 2, 'modern' => 2) as $kind => $status) {
        $fixture['contests'][$kind] = $insert('rpgdx_contests', array('contest_name' => $fixture['marker'] . '_' . $kind, 'contest_description' => 'Disposable contest CSRF fixture', 'contest_start' => date('Y-m-d H:i:s'), 'contest_end' => date('Y-m-d H:i:s'), 'contest_status' => $status));
        $save($fixture);
        if ($kind !== 'open') {
            $fixture['categories'][$kind] = $insert('rpgdx_contest_categories', array('category_contest' => $fixture['contests'][$kind], 'category_name' => 'First place', 'category_value' => 1));
            $save($fixture);
            $fixture['entries'][$kind] = $insert('rpgdx_contest_entries', array('entry_contest' => $fixture['contests'][$kind], 'entry_project' => $fixture['project'], 'entry_date' => date('Y-m-d H:i:s')));
            $save($fixture);
        }
    }
    $fixture['themes'] = $pdo->query("SELECT theme_dir, theme_id FROM rpgdx_themes WHERE theme_dir IN ('classic','modern')")->fetchAll(PDO::FETCH_KEY_PAIR);
    $save($fixture);
    echo json_encode($fixture, JSON_THROW_ON_ERROR) . "\n";
    exit;
}
$fixture = json_decode(file_get_contents($manifest), true, 512, JSON_THROW_ON_ERROR);
if ($fixture['user_id'] !== (int) $member['id']) {
    throw new RuntimeException('Contest fixture owner does not match');
}
foreach ($fixture['contests'] as $kind => $id) {
    $statement = $pdo->prepare('SELECT contest_name FROM rpgdx_contests WHERE contest_id=?');
    $statement->execute(array($id));
    if ($statement->fetchColumn() !== $fixture['marker'] . '_' . $kind) {
        throw new RuntimeException('Contest fixture ID no longer matches its marker');
    }
}
if ($action === 'state') {
    $state = array();
    foreach ($fixture['contests'] as $kind => $id) {
        $statement = $pdo->prepare('SELECT entry_id, entry_project FROM rpgdx_contest_entries WHERE entry_contest=? ORDER BY entry_id');
        $statement->execute(array($id));
        $state[$kind]['entries'] = $statement->fetchAll(PDO::FETCH_ASSOC);
        $statement = $pdo->prepare('SELECT vote_id, vote_entry, vote_user, vote_category FROM rpgdx_contest_votes JOIN rpgdx_contest_categories ON vote_category=category_id WHERE category_contest=? ORDER BY vote_id');
        $statement->execute(array($id));
        $state[$kind]['votes'] = $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    echo json_encode($state, JSON_THROW_ON_ERROR) . "\n";
    exit;
}
foreach ($fixture['contests'] as $id) {
    $statement = $pdo->prepare('DELETE v FROM rpgdx_contest_votes v JOIN rpgdx_contest_categories c ON v.vote_category=c.category_id WHERE c.category_contest=? AND v.vote_user=?');
    $statement->execute(array($id, $fixture['user_id']));
    $statement = $pdo->prepare('DELETE FROM rpgdx_contest_entries WHERE entry_contest=? AND entry_project=?');
    $statement->execute(array($id, $fixture['project'] ?? 0));
}
foreach ($fixture['categories'] as $kind => $id) {
    $statement = $pdo->prepare('DELETE FROM rpgdx_contest_categories WHERE category_id=? AND category_contest=?');
    $statement->execute(array($id, $fixture['contests'][$kind]));
}
foreach ($fixture['contests'] as $kind => $id) {
    $statement = $pdo->prepare('DELETE FROM rpgdx_contests WHERE contest_id=? AND contest_name=?');
    $statement->execute(array($id, $fixture['marker'] . '_' . $kind));
}
if (isset($fixture['project'])) {
    $statement = $pdo->prepare('DELETE FROM rpgdx_projects WHERE project_id=? AND user_id=? AND project_name=?');
    $statement->execute(array($fixture['project'], $fixture['user_id'], $fixture['marker']));
}
if ($fixture['preference']) {
    $values = $fixture['preference'];
    $statement = $pdo->prepare('REPLACE INTO rpgdx_user_preferences (' . implode(',', array_keys($values)) . ') VALUES (' . implode(',', array_fill(0, count($values), '?')) . ')');
    $statement->execute(array_values($values));
} else {
    $statement = $pdo->prepare('DELETE FROM rpgdx_user_preferences WHERE user_id=?');
    $statement->execute(array($fixture['user_id']));
}
unlink($manifest);
echo "Removed exact contest fixture IDs and restored temporary user preferences\n";
