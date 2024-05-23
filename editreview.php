<?php
include("includes/main.php");

$error = "";
$message = "";

if (!$userdata['session_logged_in']) {
	abort_with_error('You must be logged in to write reviews.');
}

if (isset($action) && $action != 'add') {
	if (!isset($review_id)) {
		abort_with_error('Somehow you didn\'t specify a review_id.');
	}
	if ($error = check_access_level(REVIEWS_TABLE, 'review_id', $review_id)) {
		abort_with_error($error);
	}
}

if (!isset($action) || ($action != "edit" && $action != "remove"
    && $action != "add")) {
	abort_with_error('No valid action specified.');
}

if ($action == 'add') {
  if (!($project_id > 0)) {
    abort_with_error('No valid game to review specified.');
  }
}


if (isset($project_id) && $project_id > 0) {
	// Extract game information
	$game = mysql_fetch_object(doQuery(
    "SELECT project_name, project_allow_review FROM ". PROJECTS_TABLE ." ".
    "WHERE project_id=$project_id"));

	if (!$game) {
		abort_with_error('No valid game to review specified.');
	} else if (!$game->project_allow_review && isset($action) && $action == 'add') {
		abort_with_error('Reviewing disabled for this project.');
	} else {
		$project_name = htmlentities($game->project_name);
	}
}


if ($action == "remove" && isset($confirmed) && $review_id > 0 && strlen($error) == 0)
{
	// DELETE review from database
	doQuery("DELETE FROM ". REVIEWS_TABLE ." WHERE review_id=$review_id");
	header("Location: userpage.php"); die();
}

if (($action == "edit" || $action == "add") &&
    isset($submit) && strlen($error) == 0)
{
	// Checks information on these conditions:
	// - Scores should be between 1 and 10

  // Prepare the review
  $review_text = prepare_for_store($review_text, '');

	if ($review_score < 1  || $review_score > 10) {
		$error = "Please fill in a rating from 1 to 10.";
	}

  if (strlen($review_text) == 0) {
    $error = "No review text.";
  }

	if (strlen($error) == 0) {
		if ($action == "edit") {
			// UPDATE the review entry
			doQuery(
				"UPDATE ". REVIEWS_TABLE ." SET ".
				"review_score = '$review_score', ".
				"review_text =  '$review_text' ".
				"WHERE review_id=$review_id"
			);
			$message = "Your review has been updated.";
		}
		else if ($action == "add") {
			// INSERT the review entry
			$result = doQuery(
				"INSERT INTO ". REVIEWS_TABLE ." (user_id, project_id, review_added, review_score, review_text) ".
				"VALUES (". $userdata['user_id'] .", $project_id, NOW(), '$review_score', '$review_text')"
			);
			header("Location: showgame.php?project_id=$project_id"); die();
		}
	}
}

if (($action == "edit" || $action == "remove") && $review_id > 0 && !isset($submit))
{
	// Get information about the selected review
	$result = doQuery(
		"SELECT * FROM ". REVIEWS_TABLE ." WHERE review_id='$review_id'"
	);
	if ($row = mysql_fetch_array($result)) {
		foreach ($row as $key => $value) {
			//$row["$key"] = htmlentities($value);
      $form[$key] = htmlentities($value);
		}
		//extract($row, EXTR_PREFIX_ALL, "rev");
	}
}
else
{
	// Strip slashes from the posted information and prepare for HTML printing.
	foreach ($_POST as $key => $value) {
		//$$key = htmlentities(stripslashes($value));
    $form[$key] = htmlentities(stripslashes($value));
	}
}

if (($action == "edit" || $action == "remove") && $review_id > 0)
{
	// Get the name of the reviewed using the review_id
	$row = mysql_fetch_object(doQuery("SELECT project_name FROM ". PROJECTS_TABLE ." WHERE project_id=". $form['project_id']));
	$project_name = $row->project_name;
}


if ($action == "remove") placeHeader(array(array("Removing review")));
else if ($action == "add") placeHeader(array(array("Reviewing $project_name")));
else if ($action == "edit") placeHeader(array(array("Editing review")));
else placeHeader(array(array("Invalid command")));


$template->set_filenames(array(
	'body' => 'editreview_body.tpl')
);

$template->assign_vars(array(
	'REV_TEXT'  => (isset($form)) ? prepare_for_edit($form['review_text']) : "",
	'GAME_NAME' => $project_name)
);


if (strlen($error) > 0) {
	$template->assign_block_vars('error', array('TEXT' => $error));
}

if (strlen($message) > 0) {
	$template->assign_block_vars('message', array('TEXT' => $message));
}

if ($action == "edit" || $action == "add")
{
	if ($action == "add") $template->assign_block_vars('writing', array());
	else $template->assign_block_vars('editing', array());

	$hidden = "";
	if (isset($form) && isset($form['review_id']) && $form['review_id'] > 0) $hidden .= "<input type=hidden name=review_id value=$review_id>";
	if (isset($form) && $form['project_id'] > 0) $hidden .= (($hidden != "") ? "\n": "") ."<input type=hidden name=project_id value=". $form['project_id'] .">";
  elseif ($project_id) $hidden .= (($hidden != "") ? "\n": "") ."<input type=hidden name=project_id value=". $project_id .">";

	$rating_options = "<option selected>--";
	for ($i = 1; $i <= 10; $i++) {
		$rating_options .= "<option". ((isset($form) && $i == $form['review_score']) ? " selected" : "") .">$i\n";
	}

	$template->assign_block_vars('form', array(
		'ACTION'         => append_sid("editreview.php?action=$action"),
		'HIDDEN'         => $hidden,
		'RATING_FIELD'   => 'review_score',
		'TEXT_FIELD'     => 'review_text',
		'RATING_OPTIONS' => $rating_options,
		'SUBMIT_TEXT'    => ($action == "add") ? "Submit review" : "Submit changes")
	);
}
else if ($action == "remove") {
	$template->assign_block_vars('remove', array(
		'YES_LINK' => "editreview.php?action=remove&amp;review_id=$review_id&amp;confirmed=1",
		'NO_LINK'  => "userpage.php")
	);
}

$template->pparse('body');

placeFooter();
?>
