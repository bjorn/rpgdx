<?php
include_once('includes/main.php');
include_once('includes/contest.php');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
	abort_with_error('Contest subscriptions require a submitted form.');
}

$action = $request->variable('action', '', false, \phpbb\request\request_interface::POST);
$project_id = $request->variable('project_id', 0, false, \phpbb\request\request_interface::POST);
$contest_id = $request->variable('contest_id', 0, false, \phpbb\request\request_interface::POST);
$entry_id = $request->variable('entry_id', 0, false, \phpbb\request\request_interface::POST);

if (!$userdata['session_logged_in']) {
	abort_with_error('You have to be logged in.');
}
if (!isset($action) || !($action === 'subscribe' || $action === 'unsubscribe')) {
	abort_with_error('No or invalid action specified.');
}

if (!check_form_key('rpgdx_contest_' . $action)) {
	abort_with_error('Your form has expired. Please try again.');
}

$user_id = (int) $userdata['user_id'];
$is_admin = (int) $userdata['user_level'] === ADMIN;
try {
	if ($action === 'subscribe') {
		rpgdx_contest_subscribe_project($project_id, $contest_id, $user_id, $is_admin);
	} else {
		$contest_id = rpgdx_contest_unsubscribe_entry($entry_id, $user_id, $is_admin);
	}
} catch (RpgdxContestMutationException $error) {
	abort_with_error($error->getMessage());
}

header("Location: showcontest.php?contest_id=$contest_id");
?>
