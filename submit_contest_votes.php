<?php
include_once('includes/main.php');
include_once('includes/contest.php');

$contest_id = (int) ($_GET['contest_id'] ?? $_POST['contest_id'] ?? 0);
$categories = $_POST['categories'] ?? null;

if (!$userdata['session_logged_in']) {
	abort_with_error('You have to login to be able to vote!');
}
if (!is_array($categories)) {
	abort_with_error('Somehow your submission lacked the categories.');
}
if (!$contest_id) {
	abort_with_error('No contest_id specified.');
}

try {
	rpgdx_contest_replace_votes(
		$contest_id,
		(int) $userdata['user_id'],
		$categories
	);
} catch (RpgdxContestMutationException $error) {
	abort_with_error($error->getMessage());
}

header("Location: showcontest.php?contest_id=$contest_id");
?>
