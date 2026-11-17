<?php
if (!defined('IN_RPGDX')) {
	die('Hacking attempt?');
}

class RpgdxContestMutationException extends RuntimeException
{
}

function rpgdx_contest_mutation_tables(?array $tables = null): array
{
	$tables = $tables ?: array(
		'contests' => CONTESTS_TABLE,
		'categories' => CONTEST_CATEGORIES_TABLE,
		'entries' => CONTEST_ENTRIES_TABLE,
		'votes' => CONTEST_VOTES_TABLE,
		'projects' => PROJECTS_TABLE,
	);
	$expectedKeys = array('contests', 'categories', 'entries', 'votes', 'projects');
	if (array_keys($tables) !== $expectedKeys) {
		throw new InvalidArgumentException('Contest table map is incomplete or out of order');
	}
	foreach ($tables as $table) {
		if (!preg_match('/^[a-zA-Z0-9_]+$/D', $table)) {
			throw new InvalidArgumentException('Invalid contest table identifier');
		}
	}
	return $tables;
}

function rpgdx_contest_mutation_query(string $sql, string $error)
{
	$result = doQuery($sql, 0);
	if ($result === false) {
		throw new RpgdxContestMutationException($error);
	}
	return $result;
}

function rpgdx_contest_mutation_transaction(callable $callback)
{
	if (doQuery('START TRANSACTION', 0) === false) {
		throw new RpgdxContestMutationException('Unable to start the contest update.');
	}
	try {
		$result = $callback();
		if (doQuery('COMMIT', 0) === false) {
			throw new RpgdxContestMutationException('Unable to finish the contest update.');
		}
		return $result;
	} catch (Throwable $error) {
		doQuery('ROLLBACK', 0);
		if ($error instanceof RpgdxContestMutationException) {
			throw $error;
		}
		throw new RpgdxContestMutationException('Unable to finish the contest update.', 0, $error);
	}
}

function rpgdx_contest_require_project_owner(int $ownerId, int $userId, bool $isAdmin): void
{
	if ($userId !== $ownerId && !$isAdmin) {
		throw new RpgdxContestMutationException('You do not have permission to view this page.');
	}
}

function rpgdx_contest_subscribe_project(
	int $projectId,
	int $contestId,
	int $userId,
	bool $isAdmin = false,
	?array $tableOverrides = null
): void {
	if ($projectId < 1) {
		throw new RpgdxContestMutationException('Somehow you didn\'t specify a valid project_id.');
	}
	if ($contestId < 1) {
		throw new RpgdxContestMutationException('Somehow you didn\'t specify a valid contest_id.');
	}
	$tables = rpgdx_contest_mutation_tables($tableOverrides);

	rpgdx_contest_mutation_transaction(function () use ($tables, $projectId, $contestId, $userId, $isAdmin): void {
		// The contest row is the serialization point for registration and phase changes.
		$result = rpgdx_contest_mutation_query(
			"SELECT contest_status FROM {$tables['contests']} "
			. "WHERE contest_id = $contestId FOR UPDATE",
			'Unable to check this contest.'
		);
		$contest = rpgdx_db_fetch_object($result);
		if (!$contest) {
			throw new RpgdxContestMutationException('No such contest, it might have been removed.');
		}
		if ((int) $contest->contest_status !== CON_OPEN) {
			throw new RpgdxContestMutationException('Subscription is not open for this contest.');
		}

		$result = rpgdx_contest_mutation_query(
			"SELECT user_id FROM {$tables['projects']} WHERE project_id = $projectId FOR UPDATE",
			'Unable to check this project.'
		);
		$project = rpgdx_db_fetch_object($result);
		if (!$project) {
			throw new RpgdxContestMutationException('No valid project_id specified.');
		}
		$projectOwner = (int) $project->user_id;
		rpgdx_contest_require_project_owner($projectOwner, $userId, $isAdmin);

		$result = rpgdx_contest_mutation_query(
			"SELECT entries.entry_id FROM {$tables['entries']} entries "
			. "INNER JOIN {$tables['projects']} projects ON projects.project_id = entries.entry_project "
			. "WHERE entries.entry_contest = $contestId AND projects.user_id = $projectOwner FOR UPDATE",
			'Unable to check existing contest subscriptions.'
		);
		if (rpgdx_db_num_rows($result) !== 0) {
			throw new RpgdxContestMutationException('This participant is already subscribed to this contest.');
		}

		rpgdx_contest_mutation_query(
			"INSERT INTO {$tables['entries']} (entry_project, entry_contest, entry_date) "
			. "VALUES ($projectId, $contestId, NOW())",
			'This project or participant is already subscribed to this contest.'
		);
	});
}

function rpgdx_contest_unsubscribe_entry(
	int $entryId,
	int $userId,
	bool $isAdmin = false,
	?array $tableOverrides = null
): int {
	if ($entryId < 1) {
		throw new RpgdxContestMutationException('Somehow you didn\'t specify a valid entry_id.');
	}
	$tables = rpgdx_contest_mutation_tables($tableOverrides);
	$result = rpgdx_contest_mutation_query(
		"SELECT entry_project, entry_contest FROM {$tables['entries']} WHERE entry_id = $entryId",
		'Unable to check this contest subscription.'
	);
	$entry = rpgdx_db_fetch_object($result);
	if (!$entry) {
		throw new RpgdxContestMutationException('Somehow you didn\'t specify a valid entry_id.');
	}
	$projectId = (int) $entry->entry_project;
	$contestId = (int) $entry->entry_contest;

	rpgdx_contest_mutation_transaction(function () use (
		$tables,
		$entryId,
		$projectId,
		$contestId,
		$userId,
		$isAdmin
	): void {
		$result = rpgdx_contest_mutation_query(
			"SELECT contest_status FROM {$tables['contests']} "
			. "WHERE contest_id = $contestId FOR UPDATE",
			'Unable to check this contest.'
		);
		$contest = rpgdx_db_fetch_object($result);
		if (!$contest) {
			throw new RpgdxContestMutationException('No such contest, it might have been removed.');
		}
		if ((int) $contest->contest_status !== CON_OPEN) {
			throw new RpgdxContestMutationException('Subscription is not open for this contest.');
		}

		$result = rpgdx_contest_mutation_query(
			"SELECT user_id FROM {$tables['projects']} WHERE project_id = $projectId FOR UPDATE",
			'Unable to check this project.'
		);
		$project = rpgdx_db_fetch_object($result);
		if (!$project) {
			throw new RpgdxContestMutationException('No valid project_id specified.');
		}
		rpgdx_contest_require_project_owner((int) $project->user_id, $userId, $isAdmin);

		$result = rpgdx_contest_mutation_query(
			"SELECT entry_id FROM {$tables['entries']} "
			. "WHERE entry_id = $entryId AND entry_project = $projectId "
			. "AND entry_contest = $contestId FOR UPDATE",
			'Unable to recheck this contest subscription.'
		);
		if (rpgdx_db_num_rows($result) !== 1) {
			throw new RpgdxContestMutationException('Somehow you didn\'t specify a valid entry_id.');
		}
		rpgdx_contest_mutation_query(
			"DELETE FROM {$tables['entries']} WHERE entry_id = $entryId",
			'Unable to remove this contest subscription.'
		);
	});
	return $contestId;
}

function rpgdx_contest_replace_votes(
	int $contestId,
	int $userId,
	array $categories,
	?array $tableOverrides = null
): void {
	if ($contestId < 1) {
		throw new RpgdxContestMutationException('No contest_id specified.');
	}
	$tables = rpgdx_contest_mutation_tables($tableOverrides);

	rpgdx_contest_mutation_transaction(function () use ($tables, $contestId, $userId, $categories): void {
		// Deliberately serialize each contest while its phase, choices and votes are
		// validated. Contests are small and correctness takes priority over voter
		// throughput; a future narrower lock must preserve the same phase boundary.
		$result = rpgdx_contest_mutation_query(
			"SELECT contest_status FROM {$tables['contests']} "
			. "WHERE contest_id = $contestId FOR UPDATE",
			'Unable to check this contest.'
		);
		$contest = rpgdx_db_fetch_object($result);
		if (!$contest) {
			throw new RpgdxContestMutationException('No such contest, it might have been removed.');
		}
		if ((int) $contest->contest_status !== CON_VOTING) {
			throw new RpgdxContestMutationException('Voting is not open for this contest.');
		}

		$result = rpgdx_contest_mutation_query(
			"SELECT category_id FROM {$tables['categories']} "
			. "WHERE category_contest = $contestId ORDER BY category_id FOR UPDATE",
			'Unable to check contest categories.'
		);
		$validCategories = array();
		while ($category = rpgdx_db_fetch_object($result)) {
			$validCategories[(int) $category->category_id] = true;
		}
		if (count($validCategories) !== count($categories)) {
			throw new RpgdxContestMutationException('Number of categories doesn\'t match.');
		}

		$votes = array();
		foreach ($categories as $categoryId => $entryId) {
			if (!is_int($categoryId) || $categoryId < 1 || !isset($validCategories[$categoryId])) {
				throw new RpgdxContestMutationException('At least one category you voted for seems to be invalid.');
			}
			if (!is_int($entryId)
				&& (!is_string($entryId) || !preg_match('/^(?:-1|[1-9][0-9]*)$/D', $entryId))) {
				throw new RpgdxContestMutationException('At least one entry you voted on seems to be invalid.');
			}
			$votes[$categoryId] = (int) $entryId;
		}

		$result = rpgdx_contest_mutation_query(
			"SELECT entry_id, entry_project FROM {$tables['entries']} "
			. "INNER JOIN {$tables['projects']} ON project_id = entry_project "
			. "WHERE entry_disqualified = 0 AND entry_contest = $contestId "
			. "AND project_name IS NOT NULL ORDER BY entry_id FOR UPDATE",
			'Unable to check eligible contest entries.'
		);
		$entryProjects = array();
		$qualifiedProjects = array();
		while ($entry = rpgdx_db_fetch_object($result)) {
			$entryId = (int) $entry->entry_id;
			$projectId = (int) $entry->entry_project;
			$entryProjects[$entryId] = $projectId;
			$qualifiedProjects[$projectId] = true;
		}

		$selectedProjects = array();
		$blankVotes = 0;
		foreach ($votes as $categoryId => $entryId) {
			if ($entryId === -1) {
				$blankVotes++;
				continue;
			}
			if (!isset($entryProjects[$entryId])) {
				throw new RpgdxContestMutationException('At least one entry you voted on seems to be invalid.');
			}
			$projectId = $entryProjects[$entryId];
			if (isset($selectedProjects[$projectId])) {
				throw new RpgdxContestMutationException('You cannot vote multiple times for the same project.');
			}
			$selectedProjects[$projectId] = $categoryId;
		}

		$numCategories = count($validCategories);
		$requiredBlanks = $numCategories - count($qualifiedProjects);
		if ($requiredBlanks > 0 && $blankVotes > $requiredBlanks) {
			throw new RpgdxContestMutationException('Too many blank votes.');
		}

		rpgdx_contest_mutation_query(
			"DELETE votes FROM {$tables['votes']} votes "
			. "INNER JOIN {$tables['categories']} categories ON vote_category = category_id "
			. "WHERE vote_user = $userId AND category_contest = $contestId",
			'Unable to replace your contest votes.'
		);
		foreach ($votes as $categoryId => $entryId) {
			if ($entryId !== -1) {
				rpgdx_contest_mutation_query(
					"INSERT INTO {$tables['votes']} (vote_entry, vote_user, vote_category) "
					. "VALUES ($entryId, $userId, $categoryId)",
					'Unable to replace your contest votes.'
				);
			}
		}
	});
}
