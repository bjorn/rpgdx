<?php
include_once('includes/main.php');
include_once('includes/contest.php');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
	abort_with_error('Contest voting requires a submitted form.');
}

$contest_id = $request->variable('contest_id', 0, false, \phpbb\request\request_interface::POST);
$categories = $request->is_set_post('categories')
	? $request->variable('categories', array(0 => ''), false, \phpbb\request\request_interface::POST)
	: null;

if (!$userdata['session_logged_in']) {
	abort_with_error('You have to login to be able to vote!');
}
if (!check_form_key('rpgdx_contest_votes')) {
	abort_with_error('Your form has expired. Please try again.');
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
