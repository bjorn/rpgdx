<?php
/** Idempotent schema upgrade required by transactional contest mutations. */

function rpgdx_contest_integrity_tables(): array
{
	return array(
		'contests' => 'rpgdx_contests',
		'categories' => 'rpgdx_contest_categories',
		'entries' => 'rpgdx_contest_entries',
		'votes' => 'rpgdx_contest_votes',
		'projects' => 'rpgdx_projects',
	);
}

function rpgdx_contest_integrity_identifier(string $identifier): string
{
	if (!preg_match('/^[a-zA-Z0-9_]+$/D', $identifier)) {
		throw new RuntimeException('Invalid contest schema identifier');
	}
	return '`' . $identifier . '`';
}

function rpgdx_contest_integrity_table_state(PDO $pdo, string $schema, string $table): array
{
	$statement = $pdo->prepare(
		'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?'
	);
	$statement->execute(array($schema, $table));
	$engine = $statement->fetchColumn();
	if ($engine === false) {
		throw new RuntimeException("Required contest table $table does not exist");
	}

	$statement = $pdo->prepare(
		'SELECT INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME, SUB_PART, INDEX_TYPE '
		. 'FROM information_schema.STATISTICS '
		. 'WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? '
		. 'ORDER BY INDEX_NAME, SEQ_IN_INDEX'
	);
	$statement->execute(array($schema, $table));
	$indexes = array();
	foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
		$name = $row['INDEX_NAME'];
		if (!isset($indexes[$name])) {
			$indexes[$name] = array(
				'unique' => !(bool) $row['NON_UNIQUE'],
				'columns' => array(),
				'has_prefix' => false,
				'type' => strtoupper($row['INDEX_TYPE']),
			);
		}
		$indexes[$name]['columns'][] = $row['COLUMN_NAME'];
		$indexes[$name]['has_prefix'] = $indexes[$name]['has_prefix'] || $row['SUB_PART'] !== null;
	}

	return array('engine' => $engine, 'indexes' => $indexes);
}

function rpgdx_contest_integrity_has_index(array $indexes, bool $unique, array $columns): bool
{
	foreach ($indexes as $index) {
		if ($index['unique'] === $unique
			&& $index['columns'] === $columns
			&& !$index['has_prefix']
			&& $index['type'] === 'BTREE') {
			return true;
		}
	}
	return false;
}

function rpgdx_contest_integrity_duplicate_summary(
	PDO $pdo,
	string $table,
	array $columns,
	string $label
): ?string {
	$quotedTable = rpgdx_contest_integrity_identifier($table);
	$quotedColumns = array_map('rpgdx_contest_integrity_identifier', $columns);
	$columnList = implode(', ', $quotedColumns);
	$groupCount = (int) $pdo->query(
		"SELECT COUNT(*) FROM (SELECT 1 FROM $quotedTable "
		. "GROUP BY $columnList HAVING COUNT(*) > 1) duplicate_groups"
	)->fetchColumn();
	if (!$groupCount) {
		return null;
	}
	$rows = $pdo->query(
		"SELECT $columnList, COUNT(*) AS duplicate_count FROM $quotedTable "
		. "GROUP BY $columnList HAVING COUNT(*) > 1 ORDER BY duplicate_count DESC LIMIT 10"
	)->fetchAll(PDO::FETCH_ASSOC);

	$samples = array();
	foreach ($rows as $row) {
		$key = array();
		foreach ($columns as $column) {
			$key[] = "$column=" . $row[$column];
		}
		$samples[] = implode('/', $key) . ' (' . $row['duplicate_count'] . ' rows)';
	}
	return "$label ($groupCount groups; first 10): " . implode(', ', $samples);
}

/**
 * Upgrade contest tables without deleting or merging historical rows.
 *
 * All duplicate and index-definition checks complete before the first ALTER.
 * The returned list contains only tables changed by this invocation.
 */
function rpgdx_migrate_contest_integrity_schema(
	PDO $pdo,
	?array $tables = null,
	bool $verbose = true
): array {
	$tables = $tables ?: rpgdx_contest_integrity_tables();
	$expectedKeys = array('contests', 'categories', 'entries', 'votes', 'projects');
	if (array_keys($tables) !== $expectedKeys) {
		throw new RuntimeException('Contest schema table map is incomplete or out of order');
	}
	foreach ($tables as $table) {
		rpgdx_contest_integrity_identifier($table);
	}

	$schema = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
	rpgdx_contest_integrity_identifier($schema);

	$states = array();
	foreach ($tables as $key => $table) {
		$states[$key] = rpgdx_contest_integrity_table_state($pdo, $schema, $table);
	}

	$duplicates = array_filter(array(
		rpgdx_contest_integrity_duplicate_summary(
			$pdo,
			$tables['entries'],
			array('entry_project', 'entry_contest'),
			'duplicate project/contest entries'
		),
		rpgdx_contest_integrity_duplicate_summary(
			$pdo,
			$tables['votes'],
			array('vote_user', 'vote_category'),
			'duplicate user/category votes'
		),
	));
	if ($duplicates) {
		throw new RuntimeException(
			'Contest integrity migration refused before schema changes. '
			. implode('; ', $duplicates)
			. '. Resolve each duplicate set explicitly, retain an audit of the decision, and rerun.'
		);
	}

	$requirements = array(
		'categories' => array(
			'category_contest' => array(false, array('category_contest')),
		),
		'entries' => array(
			'entry_project_contest' => array(true, array('entry_project', 'entry_contest')),
			'entry_contest' => array(false, array('entry_contest')),
		),
		'votes' => array(
			'vote_user_category' => array(true, array('vote_user', 'vote_category')),
			'vote_entry' => array(false, array('vote_entry')),
		),
	);

	$plans = array();
	foreach ($tables as $key => $table) {
		$clauses = array();
		if (strcasecmp($states[$key]['engine'], 'InnoDB') !== 0) {
			$clauses[] = 'ENGINE=InnoDB';
		}
		foreach ($requirements[$key] ?? array() as $name => [$unique, $columns]) {
			if (isset($states[$key]['indexes'][$name])) {
				$named = $states[$key]['indexes'][$name];
				if ($named['unique'] !== $unique
					|| $named['columns'] !== $columns
					|| $named['has_prefix']
					|| $named['type'] !== 'BTREE') {
					throw new RuntimeException(
						"Index $table.$name exists with an incompatible definition; no schema changes made"
					);
				}
			}
			if (!rpgdx_contest_integrity_has_index($states[$key]['indexes'], $unique, $columns)) {
				$kind = $unique ? 'ADD UNIQUE KEY' : 'ADD KEY';
				$quotedColumns = array_map('rpgdx_contest_integrity_identifier', $columns);
				$clauses[] = $kind . ' ' . rpgdx_contest_integrity_identifier($name)
					. ' (' . implode(', ', $quotedColumns) . ')';
			}
		}
		$plans[$key] = $clauses;
	}

	$changed = array();
	foreach ($plans as $key => $clauses) {
		if (!$clauses) {
			continue;
		}
		$table = $tables[$key];
		$pdo->exec(
			'ALTER TABLE ' . rpgdx_contest_integrity_identifier($table) . ' ' . implode(', ', $clauses)
		);
		$changed[] = $table;
		if ($verbose) {
			echo "Updated contest integrity schema for $table\n";
		}
	}

	foreach ($tables as $key => $table) {
		$state = rpgdx_contest_integrity_table_state($pdo, $schema, $table);
		if (strcasecmp($state['engine'], 'InnoDB') !== 0) {
			throw new RuntimeException("Contest table $table is not InnoDB after migration");
		}
		foreach ($requirements[$key] ?? array() as [$unique, $columns]) {
			if (!rpgdx_contest_integrity_has_index($state['indexes'], $unique, $columns)) {
				throw new RuntimeException("Contest table $table is missing a required index after migration");
			}
		}
	}

	if ($verbose && !$changed) {
		echo "Contest integrity schema already current\n";
	}
	return $changed;
}
