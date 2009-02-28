<?php
include_once("includes/main.php");

// Standard authorisation
if (!$userdata['session_logged_in']) {
	abort_with_error('You have to be logged in.');
}
if (!isset($action) || !($action == 'subscribe' || $action == 'unsubscribe')) {
	abort_with_error('No or invalid action specified.');
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

	// Subscribe this user with this project to this contest
	doQuery(
		"INSERT INTO ". CONTEST_ENTRIES_TABLE ." ".
		"(entry_project, entry_contest, entry_date) VALUES ".
		"($project_id, $contest_id, NOW())"
	);
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
	if (!$entry = mysql_fetch_object($result)) {
		abort_with_error('Somehow you didn\'t specify a valid entry_id.');
	}

	$contest_id = $entry->entry_contest;

	// Check wether the current user has the right to perform the unsubscription
	if ($error = check_access_level(PROJECTS_TABLE, 'project_id', $entry->entry_project)) {
		abort_with_error($error);
	}

	// Unsubscribe by removed the contest entry
	doQuery("DELETE FROM ". CONTEST_ENTRIES_TABLE ." WHERE entry_id = $entry_id");
}


// Return the user to the contest page
header("Location: showcontest.php?contest_id=$contest_id");
?>