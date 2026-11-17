<?php
include_once("includes/main.php");

$action     = $_GET['action'] ?? $_POST['action'] ?? null;
$project_id = (int) ($_GET['project_id'] ?? $_POST['project_id'] ?? 0);
$contest_id = (int) ($_GET['contest_id'] ?? $_POST['contest_id'] ?? 0);
$entry_id   = (int) ($_GET['entry_id']   ?? $_POST['entry_id']   ?? 0);

// Standard authorisation
if (!$userdata['session_logged_in']) {
	abort_with_error('You have to be logged in.');
}
if (!isset($action) || !($action == 'subscribe' || $action == 'unsubscribe')) {
	abort_with_error('No or invalid action specified.');
}

function abort_subscription_transaction($error)
{
	doQuery('ROLLBACK', 0);
	abort_with_error($error);
}


if ($action == 'subscribe') {
	if (!(isset($project_id) && $project_id > 0)) {
		abort_with_error('Somehow you didn\'t specify a valid project_id.');
	}
	if (!(isset($contest_id) && $contest_id > 0)) {
		abort_with_error('Somehow you didn\'t specify a valid contest_id.');
	}
	if ($error = check_access_level(PROJECTS_TABLE, 'project_id', $project_id)) {
		abort_with_error($error);
	}

	doQuery('START TRANSACTION');

	$result = doQuery(
		"SELECT contest_status FROM ". CONTESTS_TABLE ." ".
		"WHERE contest_id = $contest_id FOR UPDATE"
	);
	if (!$contest = rpgdx_db_fetch_object($result)) {
		abort_subscription_transaction('No such contest, it might have been removed.');
	}
	if ((int) $contest->contest_status !== CON_OPEN) {
		abort_subscription_transaction('Subscription is not open for this contest.');
	}

	// Lock and recheck the project after the contest lock to keep the write race-safe.
	$result = doQuery(
		"SELECT user_id FROM ". PROJECTS_TABLE ." ".
		"WHERE project_id = $project_id FOR UPDATE"
	);
	if ($error = check_access_level(PROJECTS_TABLE, 'project_id', $project_id)) {
		abort_subscription_transaction($error);
	}
	$project = rpgdx_db_fetch_object($result);
	$project_owner = (int) $project->user_id;

	$result = doQuery(
		"SELECT entries.entry_id FROM ". CONTEST_ENTRIES_TABLE ." entries ".
		"INNER JOIN ". PROJECTS_TABLE ." projects ON projects.project_id = entries.entry_project ".
		"WHERE entries.entry_contest = $contest_id AND projects.user_id = $project_owner FOR UPDATE"
	);
	if (rpgdx_db_num_rows($result) != 0) {
		abort_subscription_transaction('This participant is already subscribed to this contest.');
	}

	// Subscribe this user with this project to this contest
	doQuery(
		"INSERT INTO ". CONTEST_ENTRIES_TABLE ." ".
		"(entry_project, entry_contest, entry_date) VALUES ".
		"($project_id, $contest_id, NOW())"
	);
	doQuery('COMMIT');
}


if ($action == 'unsubscribe') {
	// Retrieve the contest entry to be removed
	if (!(isset($entry_id) && $entry_id > 0)) {
		abort_with_error('Somehow you didn\'t specify a valid entry_id.');
	}
	$result = doQuery(
		"SELECT entry_id, entry_project, entry_contest FROM ". CONTEST_ENTRIES_TABLE ." ".
		"WHERE entry_id = $entry_id"
	);
	if (!$entry = rpgdx_db_fetch_object($result)) {
		abort_with_error('Somehow you didn\'t specify a valid entry_id.');
	}

	$contest_id = $entry->entry_contest;

	// Check wether the current user has the right to perform the unsubscription
	if ($error = check_access_level(PROJECTS_TABLE, 'project_id', $entry->entry_project)) {
		abort_with_error($error);
	}

	doQuery('START TRANSACTION');

	$result = doQuery(
		"SELECT contest_status FROM ". CONTESTS_TABLE ." ".
		"WHERE contest_id = $contest_id FOR UPDATE"
	);
	if (!$contest = rpgdx_db_fetch_object($result)) {
		abort_subscription_transaction('No such contest, it might have been removed.');
	}
	if ((int) $contest->contest_status !== CON_OPEN) {
		abort_subscription_transaction('Subscription is not open for this contest.');
	}

	$entry_project = (int) $entry->entry_project;
	doQuery("SELECT project_id FROM ". PROJECTS_TABLE ." WHERE project_id = $entry_project FOR UPDATE");
	if ($error = check_access_level(PROJECTS_TABLE, 'project_id', $entry_project)) {
		abort_subscription_transaction($error);
	}

	// Recheck the entry under the contest lock before deleting it.
	$result = doQuery(
		"SELECT entry_id FROM ". CONTEST_ENTRIES_TABLE ." ".
		"WHERE entry_id = $entry_id AND entry_project = $entry_project AND entry_contest = $contest_id FOR UPDATE"
	);
	if (rpgdx_db_num_rows($result) != 1) {
		abort_subscription_transaction('Somehow you didn\'t specify a valid entry_id.');
	}

	// Unsubscribe by removed the contest entry
	doQuery("DELETE FROM ". CONTEST_ENTRIES_TABLE ." WHERE entry_id = $entry_id");
	doQuery('COMMIT');
}


// Return the user to the contest page
header("Location: showcontest.php?contest_id=$contest_id");
?>
