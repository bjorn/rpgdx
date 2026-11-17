<?php
/** Database integration coverage for contest mutation and schema invariants. */
if (PHP_SAPI !== 'cli') {
	http_response_code(404);
	exit;
}
if (getenv('RPGDX_SMOKE_ALLOW_WRITES') !== '1') {
	throw new RuntimeException('Set RPGDX_SMOKE_ALLOW_WRITES=1 for the local contest integrity test');
}

require dirname(__DIR__) . '/forums/config.php';
if ($dbhost !== 'db' || $dbname !== 'rpgdx') {
	throw new RuntimeException('Contest integrity tests require the disposable local db host and rpgdx database');
}

define('IN_RPGDX', true);
define('CON_OPEN', 0);
define('CON_ACTIVE', 1);
define('CON_VOTING', 2);
define('CON_CLOSED', 3);
require dirname(__DIR__) . '/includes/contest.php';
require dirname(__DIR__) . '/scripts/migration/contest-integrity-schema.php';

$port = !empty($dbport) ? ';port=' . (int) $dbport : '';
$pdo = new PDO(
	"mysql:host=$dbhost$port;dbname=$dbname;charset=utf8mb4",
	$dbuser,
	$dbpasswd,
	array(
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_EMULATE_PREPARES => false,
	)
);
$GLOBALS['rpgdx_contest_test_connection'] = null;

function rpgdx_contest_test_connect(): mysqli
{
	global $dbhost, $dbport, $dbuser, $dbpasswd, $dbname;
	mysqli_report(MYSQLI_REPORT_OFF);
	$connection = mysqli_connect($dbhost, $dbuser, $dbpasswd, $dbname, (int) ($dbport ?: 3306));
	if (!$connection || !mysqli_set_charset($connection, 'utf8mb4')) {
		throw new RuntimeException('Unable to connect contest test database');
	}
	$GLOBALS['rpgdx_contest_test_connection'] = $connection;
	return $connection;
}

function doQuery($query, $die = 2)
{
	$connection = $GLOBALS['rpgdx_contest_test_connection'];
	$result = mysqli_query($connection, $query);
	if ($result === false && $die === 2) {
		throw new RuntimeException('Contest test query failed: ' . mysqli_error($connection));
	}
	return $result;
}

function rpgdx_db_fetch_object($result)
{
	return $result ? mysqli_fetch_object($result) : null;
}

function rpgdx_db_num_rows($result): int
{
	return $result ? mysqli_num_rows($result) : 0;
}

function rpgdx_contest_test_expect(bool $condition, string $message): void
{
	if (!$condition) {
		throw new RuntimeException('Contest integrity test failed: ' . $message);
	}
}

function rpgdx_contest_test_reject(callable $operation, string $message): void
{
	try {
		$operation();
	} catch (RpgdxContestMutationException $error) {
		rpgdx_contest_test_expect(
			str_contains($error->getMessage(), $message),
			"expected rejection containing '$message', got '{$error->getMessage()}'"
		);
		return;
	}
	throw new RuntimeException("Contest integrity test failed: expected rejection containing '$message'");
}

function rpgdx_contest_test_identifier(string $identifier): string
{
	if (!preg_match('/^[a-zA-Z0-9_]+$/D', $identifier)) {
		throw new RuntimeException('Invalid contest test identifier');
	}
	return '`' . $identifier . '`';
}

function rpgdx_contest_test_tables(string $prefix): array
{
	return array(
		'contests' => $prefix . '_contests',
		'categories' => $prefix . '_categories',
		'entries' => $prefix . '_entries',
		'votes' => $prefix . '_votes',
		'projects' => $prefix . '_projects',
	);
}

function rpgdx_contest_test_create_tables(PDO $pdo, array $tables, string $engine, bool $indexes): void
{
	foreach ($tables as $table) {
		rpgdx_contest_test_identifier($table);
	}
	if (!in_array($engine, array('InnoDB', 'MyISAM'), true)) {
		throw new RuntimeException('Invalid contest test engine');
	}
	$categoryIndex = $indexes ? ', KEY category_contest (category_contest)' : '';
	$entryIndexes = $indexes
		? ', UNIQUE KEY entry_project_contest (entry_project, entry_contest), KEY entry_contest (entry_contest)'
		: '';
	$voteIndexes = $indexes
		? ', UNIQUE KEY vote_user_category (vote_user, vote_category), KEY vote_entry (vote_entry)'
		: '';
	$pdo->exec('CREATE TABLE ' . rpgdx_contest_test_identifier($tables['contests']) . " (
		contest_id INT NOT NULL AUTO_INCREMENT,
		contest_status INT NOT NULL,
		PRIMARY KEY (contest_id)
	) ENGINE=$engine");
	$pdo->exec('CREATE TABLE ' . rpgdx_contest_test_identifier($tables['categories']) . " (
		category_id INT NOT NULL AUTO_INCREMENT,
		category_contest INT NOT NULL,
		PRIMARY KEY (category_id)$categoryIndex
	) ENGINE=$engine");
	$pdo->exec('CREATE TABLE ' . rpgdx_contest_test_identifier($tables['projects']) . " (
		project_id INT NOT NULL AUTO_INCREMENT,
		user_id INT NOT NULL,
		project_name TEXT NULL,
		PRIMARY KEY (project_id)
	) ENGINE=$engine");
	$pdo->exec('CREATE TABLE ' . rpgdx_contest_test_identifier($tables['entries']) . " (
		entry_id INT NOT NULL AUTO_INCREMENT,
		entry_project INT NOT NULL,
		entry_contest INT NOT NULL,
		entry_date DATETIME NULL,
		entry_disqualified TINYINT NOT NULL DEFAULT 0,
		PRIMARY KEY (entry_id)$entryIndexes
	) ENGINE=$engine");
	$pdo->exec('CREATE TABLE ' . rpgdx_contest_test_identifier($tables['votes']) . " (
		vote_id INT NOT NULL AUTO_INCREMENT,
		vote_entry INT NOT NULL,
		vote_user INT NOT NULL,
		vote_category INT NOT NULL,
		PRIMARY KEY (vote_id)$voteIndexes
	) ENGINE=$engine");
}

function rpgdx_contest_test_drop_tables(PDO $pdo, array $tables): void
{
	foreach (array_reverse($tables) as $table) {
		$pdo->exec('DROP TABLE IF EXISTS ' . rpgdx_contest_test_identifier($table));
	}
}

function rpgdx_contest_test_insert(PDO $pdo, string $table, array $values): int
{
	$columns = array_keys($values);
	$quotedColumns = array_map('rpgdx_contest_test_identifier', $columns);
	$placeholders = implode(', ', array_fill(0, count($values), '?'));
	$statement = $pdo->prepare(
		'INSERT INTO ' . rpgdx_contest_test_identifier($table)
		. ' (' . implode(', ', $quotedColumns) . ") VALUES ($placeholders)"
	);
	$statement->execute(array_values($values));
	return (int) $pdo->lastInsertId();
}

function rpgdx_contest_test_votes(PDO $pdo, array $tables, int $userId, int $contestId): array
{
	$statement = $pdo->prepare(
		'SELECT vote_category, vote_entry FROM ' . rpgdx_contest_test_identifier($tables['votes']) . ' votes '
		. 'INNER JOIN ' . rpgdx_contest_test_identifier($tables['categories']) . ' categories '
		. 'ON category_id = vote_category WHERE vote_user = ? AND category_contest = ? '
		. 'ORDER BY vote_category'
	);
	$statement->execute(array($userId, $contestId));
	$votes = array();
	foreach ($statement->fetchAll(PDO::FETCH_KEY_PAIR) as $categoryId => $entryId) {
		$votes[(int) $categoryId] = (int) $entryId;
	}
	return $votes;
}

function rpgdx_contest_test_worker(array $payload): never
{
	$startAt = (float) $payload['start_at'];
	while (microtime(true) < $startAt) {
		usleep(1000);
	}
	rpgdx_contest_test_connect();
	try {
		if ($payload['operation'] === 'subscribe') {
			rpgdx_contest_subscribe_project(
				(int) $payload['project_id'],
				(int) $payload['contest_id'],
				(int) $payload['user_id'],
				false,
				$payload['tables']
			);
		} elseif ($payload['operation'] === 'vote') {
			$votes = array();
			foreach ($payload['votes'] as $categoryId => $entryId) {
				$votes[(int) $categoryId] = (int) $entryId;
			}
			rpgdx_contest_replace_votes(
				(int) $payload['contest_id'],
				(int) $payload['user_id'],
				$votes,
				$payload['tables']
			);
		} else {
			throw new RuntimeException('Unknown contest test worker operation');
		}
		echo json_encode(array('passed' => true), JSON_THROW_ON_ERROR);
	} catch (Throwable $error) {
		echo json_encode(array('passed' => false, 'error' => $error->getMessage()), JSON_THROW_ON_ERROR);
	}
	exit;
}

function rpgdx_contest_test_concurrent(array $payloads): array
{
	$processes = array();
	foreach ($payloads as $payload) {
		$command = array(
			PHP_BINARY,
			__FILE__,
			'--worker',
			base64_encode(json_encode($payload, JSON_THROW_ON_ERROR)),
		);
		$pipes = array();
		$process = proc_open(
			$command,
			array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')),
			$pipes
		);
		if (!is_resource($process)) {
			throw new RuntimeException('Unable to start contest concurrency worker');
		}
		fclose($pipes[0]);
		$processes[] = array($process, $pipes);
	}

	$results = array();
	foreach ($processes as [$process, $pipes]) {
		$output = stream_get_contents($pipes[1]);
		$error = stream_get_contents($pipes[2]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		$status = proc_close($process);
		rpgdx_contest_test_expect($status === 0, "concurrency worker exited $status: $error");
		$results[] = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
	}
	return $results;
}

if (($argv[1] ?? '') === '--worker') {
	$payload = json_decode(base64_decode($argv[2], true), true, 512, JSON_THROW_ON_ERROR);
	rpgdx_contest_test_worker($payload);
}

rpgdx_contest_test_connect();
$suffix = bin2hex(random_bytes(4));
$tables = rpgdx_contest_test_tables('rpgdx_ci_' . $suffix);
$migrationTables = rpgdx_contest_test_tables('rpgdx_cm_' . $suffix);
$duplicateTables = rpgdx_contest_test_tables('rpgdx_cd_' . $suffix);
$trigger = 'rpgdx_ct_' . $suffix;

try {
	rpgdx_contest_test_create_tables($pdo, $tables, 'InnoDB', true);

	$projects = array();
	foreach (array(101, 101, 102, 102, 201, 202, 203) as $index => $owner) {
		$projects[] = rpgdx_contest_test_insert($pdo, $tables['projects'], array(
			'user_id' => $owner,
			'project_name' => "Contest test project $index",
		));
	}
	$open = rpgdx_contest_test_insert($pdo, $tables['contests'], array('contest_status' => CON_OPEN));
	$active = rpgdx_contest_test_insert($pdo, $tables['contests'], array('contest_status' => CON_ACTIVE));
	$voting = rpgdx_contest_test_insert($pdo, $tables['contests'], array('contest_status' => CON_VOTING));
	$closed = rpgdx_contest_test_insert($pdo, $tables['contests'], array('contest_status' => CON_CLOSED));

	rpgdx_contest_subscribe_project($projects[0], $open, 101, false, $tables);
	$entry = (int) $pdo->query(
		'SELECT entry_id FROM ' . rpgdx_contest_test_identifier($tables['entries'])
		. ' WHERE entry_contest = ' . $open
	)->fetchColumn();
	rpgdx_contest_test_expect(
		rpgdx_contest_unsubscribe_entry($entry, 101, false, $tables) === $open,
		'open-phase unsubscription returns its contest for the endpoint redirect'
	);
	foreach (array($active, $voting, $closed) as $phaseContest) {
		rpgdx_contest_test_reject(
			fn() => rpgdx_contest_subscribe_project($projects[0], $phaseContest, 101, false, $tables),
			'Subscription is not open'
		);
		$phaseEntry = rpgdx_contest_test_insert($pdo, $tables['entries'], array(
			'entry_project' => $projects[0],
			'entry_contest' => $phaseContest,
			'entry_date' => date('Y-m-d H:i:s'),
		));
		rpgdx_contest_test_reject(
			fn() => rpgdx_contest_unsubscribe_entry($phaseEntry, 101, false, $tables),
			'Subscription is not open'
		);
	}

	rpgdx_contest_subscribe_project($projects[0], $open, 101, false, $tables);
	rpgdx_contest_test_reject(
		fn() => rpgdx_contest_subscribe_project($projects[1], $open, 101, false, $tables),
		'already subscribed'
	);
	rpgdx_contest_test_reject(
		fn() => rpgdx_contest_subscribe_project($projects[2], $open, 999, false, $tables),
		'do not have permission'
	);
	rpgdx_contest_subscribe_project($projects[2], $open, 999, true, $tables);
	try {
		rpgdx_contest_test_insert($pdo, $tables['entries'], array(
			'entry_project' => $projects[0],
			'entry_contest' => $open,
			'entry_date' => date('Y-m-d H:i:s'),
		));
		throw new RuntimeException('Contest integrity test failed: duplicate project registration was accepted');
	} catch (PDOException $error) {
		rpgdx_contest_test_expect($error->getCode() === '23000', 'project/contest uniqueness rejects duplicate registration');
	}

	$concurrentContest = rpgdx_contest_test_insert($pdo, $tables['contests'], array('contest_status' => CON_OPEN));
	$startAt = microtime(true) + 0.5;
	$registrationResults = rpgdx_contest_test_concurrent(array(
		array('operation' => 'subscribe', 'project_id' => $projects[2], 'contest_id' => $concurrentContest, 'user_id' => 102, 'tables' => $tables, 'start_at' => $startAt),
		array('operation' => 'subscribe', 'project_id' => $projects[3], 'contest_id' => $concurrentContest, 'user_id' => 102, 'tables' => $tables, 'start_at' => $startAt),
	));
	rpgdx_contest_test_expect(
		count(array_filter($registrationResults, fn($result) => $result['passed'])) === 1,
		'concurrent participant registrations produce one winner and one rejection'
	);
	$count = (int) $pdo->query(
		'SELECT COUNT(*) FROM ' . rpgdx_contest_test_identifier($tables['entries'])
		. ' WHERE entry_contest = ' . $concurrentContest
	)->fetchColumn();
	rpgdx_contest_test_expect($count === 1, 'contest lock leaves one concurrent participant registration');

	$voteContest = rpgdx_contest_test_insert($pdo, $tables['contests'], array('contest_status' => CON_VOTING));
	$otherContest = rpgdx_contest_test_insert($pdo, $tables['contests'], array('contest_status' => CON_VOTING));
	$categories = array();
	foreach (array(10, 8, 6) as $unused) {
		$categories[] = rpgdx_contest_test_insert($pdo, $tables['categories'], array('category_contest' => $voteContest));
	}
	$otherCategory = rpgdx_contest_test_insert($pdo, $tables['categories'], array('category_contest' => $otherContest));
	$entries = array();
	foreach (array_slice($projects, 4, 3) as $project) {
		$entries[] = rpgdx_contest_test_insert($pdo, $tables['entries'], array(
			'entry_project' => $project,
			'entry_contest' => $voteContest,
			'entry_date' => date('Y-m-d H:i:s'),
		));
	}
	$disqualified = rpgdx_contest_test_insert($pdo, $tables['entries'], array(
		'entry_project' => $projects[3],
		'entry_contest' => $voteContest,
		'entry_date' => date('Y-m-d H:i:s'),
		'entry_disqualified' => 1,
	));
	$missingProject = rpgdx_contest_test_insert($pdo, $tables['entries'], array(
		'entry_project' => 999999,
		'entry_contest' => $voteContest,
		'entry_date' => date('Y-m-d H:i:s'),
	));
	$crossContest = rpgdx_contest_test_insert($pdo, $tables['entries'], array(
		'entry_project' => $projects[0],
		'entry_contest' => $otherContest,
		'entry_date' => date('Y-m-d H:i:s'),
	));

	$validVotes = array($categories[0] => $entries[0], $categories[1] => $entries[1], $categories[2] => -1);
	rpgdx_contest_replace_votes($voteContest, 301, $validVotes, $tables);
	rpgdx_contest_test_expect(
		rpgdx_contest_test_votes($pdo, $tables, 301, $voteContest) === array(
			$categories[0] => $entries[0],
			$categories[1] => $entries[1],
		),
		'valid voting stores selected entries and preserves the permitted blank'
	);
	$beforeInvalid = rpgdx_contest_test_votes($pdo, $tables, 301, $voteContest);
	foreach (array($disqualified, $missingProject, $crossContest) as $invalidEntry) {
		rpgdx_contest_test_reject(
			fn() => rpgdx_contest_replace_votes(
				$voteContest,
				301,
				array($categories[0] => $invalidEntry, $categories[1] => $entries[1], $categories[2] => -1),
				$tables
			),
			'entry you voted on seems to be invalid'
		);
		rpgdx_contest_test_expect(
			rpgdx_contest_test_votes($pdo, $tables, 301, $voteContest) === $beforeInvalid,
			'invalid vote target cannot replace existing votes'
		);
	}
	rpgdx_contest_test_reject(
		fn() => rpgdx_contest_replace_votes(
			$voteContest,
			301,
			array($otherCategory => $entries[0], $categories[1] => $entries[1], $categories[2] => -1),
			$tables
		),
		'category you voted for seems to be invalid'
	);
	foreach (array($open, $active, $closed) as $nonVotingContest) {
		rpgdx_contest_test_reject(
			fn() => rpgdx_contest_replace_votes(
				$nonVotingContest,
				301,
				array(),
				$tables
			),
			'Voting is not open'
		);
	}

	$pdo->exec(
		'ALTER TABLE ' . rpgdx_contest_test_identifier($tables['entries'])
		. ' DROP INDEX entry_project_contest'
	);
	$duplicateProjectEntry = rpgdx_contest_test_insert($pdo, $tables['entries'], array(
		'entry_project' => $projects[4],
		'entry_contest' => $voteContest,
		'entry_date' => date('Y-m-d H:i:s'),
	));
	rpgdx_contest_test_reject(
		fn() => rpgdx_contest_replace_votes(
			$voteContest,
			301,
			array($categories[0] => $entries[0], $categories[1] => $duplicateProjectEntry, $categories[2] => -1),
			$tables
		),
		'multiple times for the same project'
	);
	$pdo->exec(
		'DELETE FROM ' . rpgdx_contest_test_identifier($tables['entries'])
		. ' WHERE entry_id = ' . $duplicateProjectEntry
	);
	$pdo->exec(
		'ALTER TABLE ' . rpgdx_contest_test_identifier($tables['entries'])
		. ' ADD UNIQUE KEY entry_project_contest (entry_project, entry_contest)'
	);

	rpgdx_contest_replace_votes($voteContest, 303, $validVotes, $tables);
	$beforeFailure = rpgdx_contest_test_votes($pdo, $tables, 303, $voteContest);
	$pdo->exec(
		'CREATE TRIGGER ' . rpgdx_contest_test_identifier($trigger)
		. ' BEFORE INSERT ON ' . rpgdx_contest_test_identifier($tables['votes'])
		. " FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'injected contest vote failure'"
	);
	rpgdx_contest_test_reject(
		fn() => rpgdx_contest_replace_votes(
			$voteContest,
			303,
			array($categories[0] => $entries[1], $categories[1] => $entries[0], $categories[2] => -1),
			$tables
		),
		'Unable to replace your contest votes'
	);
	$pdo->exec('DROP TRIGGER ' . rpgdx_contest_test_identifier($trigger));
	rpgdx_contest_test_expect(
		rpgdx_contest_test_votes($pdo, $tables, 303, $voteContest) === $beforeFailure,
		'injected insert failure rolls back the preceding vote deletion'
	);

	$startAt = microtime(true) + 0.5;
	$voteSetA = array($categories[0] => $entries[0], $categories[1] => $entries[1], $categories[2] => -1);
	$voteSetB = array($categories[0] => $entries[1], $categories[1] => $entries[0], $categories[2] => -1);
	$voteResults = rpgdx_contest_test_concurrent(array(
		array('operation' => 'vote', 'contest_id' => $voteContest, 'user_id' => 304, 'votes' => $voteSetA, 'tables' => $tables, 'start_at' => $startAt),
		array('operation' => 'vote', 'contest_id' => $voteContest, 'user_id' => 304, 'votes' => $voteSetB, 'tables' => $tables, 'start_at' => $startAt),
	));
	rpgdx_contest_test_expect(
		count(array_filter($voteResults, fn($result) => $result['passed'])) === 2,
		'concurrent valid vote replacements both complete'
	);
	$finalConcurrentVotes = rpgdx_contest_test_votes($pdo, $tables, 304, $voteContest);
	$storedA = array($categories[0] => $entries[0], $categories[1] => $entries[1]);
	$storedB = array($categories[0] => $entries[1], $categories[1] => $entries[0]);
	rpgdx_contest_test_expect(
		$finalConcurrentVotes === $storedA || $finalConcurrentVotes === $storedB,
		'contest lock leaves one complete concurrent vote set without a hybrid'
	);

	rpgdx_contest_test_create_tables($pdo, $migrationTables, 'MyISAM', false);
	$changed = rpgdx_migrate_contest_integrity_schema($pdo, $migrationTables, false);
	rpgdx_contest_test_expect(count($changed) === 5, 'first migration converts all five transactional tables');
	$schemaAfter = array();
	foreach ($migrationTables as $table) {
		$schemaAfter[$table] = $pdo->query(
			'SHOW CREATE TABLE ' . rpgdx_contest_test_identifier($table)
		)->fetch(PDO::FETCH_NUM)[1];
	}
	rpgdx_contest_test_expect(
		rpgdx_migrate_contest_integrity_schema($pdo, $migrationTables, false) === array(),
		'migration rerun detects current engines and indexes without another ALTER'
	);
	foreach ($migrationTables as $table) {
		$currentSchema = $pdo->query(
			'SHOW CREATE TABLE ' . rpgdx_contest_test_identifier($table)
		)->fetch(PDO::FETCH_NUM)[1];
		rpgdx_contest_test_expect($currentSchema === $schemaAfter[$table], "$table schema stays unchanged on rerun");
	}

	rpgdx_contest_test_create_tables($pdo, $duplicateTables, 'MyISAM', false);
	foreach (array(1, 2) as $unused) {
		rpgdx_contest_test_insert($pdo, $duplicateTables['entries'], array(
			'entry_project' => 51,
			'entry_contest' => 61,
			'entry_date' => date('Y-m-d H:i:s'),
		));
		rpgdx_contest_test_insert($pdo, $duplicateTables['votes'], array(
			'vote_entry' => 71,
			'vote_user' => 81,
			'vote_category' => 91,
		));
	}
	$beforeRefusal = array();
	foreach ($duplicateTables as $table) {
		$beforeRefusal[$table] = $pdo->query(
			'SHOW CREATE TABLE ' . rpgdx_contest_test_identifier($table)
		)->fetch(PDO::FETCH_NUM)[1];
	}
	try {
		rpgdx_migrate_contest_integrity_schema($pdo, $duplicateTables, false);
		throw new RuntimeException('Contest integrity test failed: duplicate migration data was accepted');
	} catch (RuntimeException $error) {
		rpgdx_contest_test_expect(
			str_contains($error->getMessage(), 'entry_project=51/entry_contest=61')
			&& str_contains($error->getMessage(), 'vote_user=81/vote_category=91')
			&& str_contains($error->getMessage(), 'Resolve each duplicate set explicitly'),
			'duplicate refusal identifies both key sets and an actionable next step'
		);
	}
	foreach ($duplicateTables as $table) {
		$currentSchema = $pdo->query(
			'SHOW CREATE TABLE ' . rpgdx_contest_test_identifier($table)
		)->fetch(PDO::FETCH_NUM)[1];
		rpgdx_contest_test_expect($currentSchema === $beforeRefusal[$table], "$table is not mutated after duplicate preflight refusal");
	}

	echo "PASS contest integrity: phases, eligibility, atomicity, concurrency and rerunnable migration\n";
} finally {
	$pdo->exec('DROP TRIGGER IF EXISTS ' . rpgdx_contest_test_identifier($trigger));
	rpgdx_contest_test_drop_tables($pdo, $duplicateTables);
	rpgdx_contest_test_drop_tables($pdo, $migrationTables);
	rpgdx_contest_test_drop_tables($pdo, $tables);
}
