<?php
include_once('includes/main.php');
include_once('includes/contest.php');

$action     = $_GET['action'] ?? $_POST['action'] ?? null;
$project_id = (int) ($_GET['project_id'] ?? $_POST['project_id'] ?? 0);
$contest_id = (int) ($_GET['contest_id'] ?? $_POST['contest_id'] ?? 0);
$entry_id   = (int) ($_GET['entry_id']   ?? $_POST['entry_id']   ?? 0);

if (!$userdata['session_logged_in']) {
	abort_with_error('You have to be logged in.');
}
if (!isset($action) || !($action === 'subscribe' || $action === 'unsubscribe')) {
	abort_with_error('No or invalid action specified.');
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
