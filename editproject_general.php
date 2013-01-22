<?php
include("includes/main.php");

$error = "";
$message = "";

if (!$userdata['session_logged_in']) {
	abort_with_error('You must be logged in to access this page.');
}

if (isset($action) && $action != 'add') {
	if (!isset($project_id)) {
		abort_with_error('Somehow you didn\'t specify a project_id.');
	}
	if ($error = check_access_level(PROJECTS_TABLE, 'project_id', $project_id)) {
		abort_with_error($error);
	}
}

if (!isset($action) || ($action != "edit" && $action != "remove" && $action != "add")) {
	abort_with_error('No valid action specified.');
}


// Make sure there is a bbcode_uid
if (empty($project_bbcode_uid)) {$project_bbcode_uid = make_bbcode_uid();}


if (strlen($error) == 0 && ($action == "edit" || $action == "add") && isset($submit))
{
  // Prepare the message
  $project_description = prepare_for_store($project_description, $project_bbcode_uid);
  $project_contributors = prepare_for_store($project_contributors);
  $project_summary = prepare_for_store($project_summary);
  $project_name = prepare_for_store($project_name);

	// Checks information on these conditions:
	// - A project name is filled in
	// - A genre is selected
	if (strlen($project_name) < 1) $error .= ((strlen($error) > 0) ? '<br />' : '') . 'No project name specified.';
	if ($project_type == 0)        $error .= ((strlen($error) > 0) ? '<br />' : '') . 'No genre selected.';

	if (strlen($error) == 0) {
		if ($action == "edit") {
			// UPDATE the project entry
			if (isset($update_updated) && $update_updated) $updated_part = "project_last_update = NOW(), ";
			else $updated_part = "";

			doQuery(
				"UPDATE ". PROJECTS_TABLE ." SET ".
				$updated_part.
				"project_name =         '$project_name', ".
				"project_contributors = '$project_contributors', ".
				"project_type =         '$project_type', ".
				"language_id =          '$language_id', ".
				"progress_id =          '$progress_id', ".
				"project_summary =      '$project_summary', ".
				"project_description =  '$project_description', ".
				"project_url =          '$project_url', ".
				"download =             '$download', ".
        "project_bbcode_uid =   '$project_bbcode_uid', ".
				"project_allow_review = '". ((isset($project_allow_review)) ? "1" : "0") ."' ".
				"WHERE project_id=$project_id"
			);
			$message = stripslashes($project_name) ." has been updated.";
			add_log("User ". $userdata['user_id'] ." (". $userdata['username'] .") has updated project $project_id ($project_name).");
		}
		else if ($action == "add") {
			// INSERT the project entry
			doQuery(
				"INSERT INTO ". PROJECTS_TABLE ." (".
				"user_id, project_last_update, project_added, project_name, project_contributors, project_type, language_id, progress_id, project_summary, project_description, ".
				"project_url, download, project_bbcode_uid, project_allow_review".
				") VALUES (".
				$userdata['user_id'] .", NOW(), NOW(), '$project_name', '$project_contributors', '$project_type', '$language_id', '$progress_id', '$project_summary', '$project_description', '$project_url', '$download', '$project_bbcode_uid', '". ((isset($project_allow_review)) ? "1" : "0") ."')"
			);
			$message = stripslashes($project_name) ." has been added to RPGDX!";
			$project_id = mysql_insert_id();
			add_log("User ". $userdata['user_id'] ." (". $userdata['username'] .") has added project $project_id (". stripslashes($project_name) .").");
			header('Location: editproject.php?project_id=' . $project_id);
		}
	}
}

$form['project_bbcode_uid'] = $project_bbcode_uid;

if (isset($action) && ($action == "edit" || $action == "remove") && $project_id > 0 && !isset($submit))
{
	// Get information about the selected project
	$result = doQuery("SELECT * FROM ". PROJECTS_TABLE ." WHERE project_id='$project_id'");
	if ($row = mysql_fetch_array($result)) {
		foreach ($row as $key => $value) {
			//$row["$key"] = htmlentities($value);
      $form[$key] = htmlentities($value);
		}
		//extract($row, EXTR_PREFIX_ALL, "rpg");
	}
}
else
{
	// Strip slashes from the posted information and prepare for HTML printing.
	foreach ($HTTP_POST_VARS as $key => $value) {
		//$$key = htmlentities(stripslashes($value));
    $form[$key] = htmlentities(stripslashes($value));
	}

	if (!isset($HTTP_POST_VARS['project_allow_review']))
	{
		if (isset($submit)) {$form['project_allow_review'] = false;}
		else {$form['project_allow_review'] = true;}
	}
}

if (isset($action) && $action == "remove" && isset($confirmed) && $project_id > 0 && strlen($error) == 0)
{
	// DELETE project from database
	doQuery("DELETE FROM ". PROJECTS_TABLE ." WHERE project_id=$project_id");
	add_log("User ". $userdata['user_id'] ." (". $userdata['username'] .") has removed project $project_id (". $form['project_name'] .").");
	header("Location: userpage.php"); die();
}


if (isset($action) && $action == "remove") placeHeader(array(array("Removing project")));
else if (isset($action) && $action == "add") placeHeader(array(array("Adding new project")));
else if (isset($action) && $action == "edit") placeHeader(array(array("Editing project")));
else placeHeader(array(array("Invalid command")));


$template->set_filenames(array(
	'body' => 'editproject_general_body.tpl')
);

$template->assign_vars(array(
	'PROJECT_NAME'         => (isset($form['project_name'])) ? prepare_for_edit($form['project_name']) : '',
	'PROJECT_PEOPLE'       => (isset($form['project_contributors'])) ? prepare_for_edit($form['project_contributors']) : '',
	'PROJECT_BRIEF'        => (isset($form['project_summary'])) ? prepare_for_edit($form['project_summary']) : '',
	'PROJECT_LENGTHY'      => (isset($form['project_description']) && isset($form['project_bbcode_uid'])) ? prepare_for_edit($form['project_description'], $form['project_bbcode_uid']) : '',
	'PROJECT_URL'          => (isset($form['project_url'])) ? $form['project_url'] : '',
	'PROJECT_DOWNLOAD'     => (isset($form['download'])) ? $form['download'] : '',
	'PROJECT_ALLOW_REVIEW' => (isset($form['project_allow_review'])) ? (($form['project_allow_review']) ? 'CHECKED' : '') : 'CHECKED')
);


if (strlen($error) > 0) {
	$template->assign_block_vars('error', array(
		'TEXT' => $error)
	);
}

if (strlen($message) > 0) {
	$template->assign_block_vars('message', array(
		'TEXT' => $message)
	);
}


if ($action == "edit" || $action == "add")
{
	$genre_options = "<option value=0 selected>- please select a genre -</option>\n";
	$genres = doQuery("SELECT * FROM ". PROJECT_TYPES_TABLE);
	while ($genre = mysql_fetch_object($genres)) {
		$genre_options .= "<option value=\"$genre->type_id\"". ((isset($form['project_type']) && $form['project_type'] == $genre->type_id) ? " selected" : "") .">$genre->type_title</option>\n";
	}

	$lang_options = "<option value=0 selected>Unspecified</option>\n";
	$languages = doQuery("SELECT * FROM ". PROGRAMMING_LANGUAGES_TABLE ." ORDER BY language_name");
	while ($row = mysql_fetch_object($languages)) {
		$lang_options .= "<option value=\"$row->language_id\"". ((isset($form['language_id']) && $form['language_id'] == $row->language_id) ? " selected" : "") .">$row->language_name</option>\n";
	}

	$progress_options = "<option value=0 selected>Unspecified</option>\n";
	$progresses = doQuery("SELECT status_id, status_title FROM ". PROJECT_STATUSSES_TABLE ." ORDER BY status_perc");
	while ($progress = mysql_fetch_object($progresses)) {
		$progress_options .= "<option value=\"$progress->status_id\"". ((isset($form['project_type']) && $form['progress_id'] == $progress->status_id) ? " selected" : "") .">$progress->status_title</option>\n";
	}

	$template->assign_block_vars('form', array(
		'ACTION'             => "editproject_general.php?action=$action",
		'HIDDEN'             => ((isset($project_id) && $project_id > 0) ? '<input type=hidden name=project_id value='. $project_id. '>' : '').
                           ((isset($form) && isset($form['project_bbcode_uid'])) ? '<input type=hidden name=project_bbcode_uid value='. $form['project_bbcode_uid'] .'>' : ''),
		'NAME_FIELD'         => 'project_name',
		'PEOPLE_FIELD'       => 'project_contributors',
		'BRIEF_FIELD'        => 'project_summary',
		'LENGTHY_FIELD'      => 'project_description',
		'GENRE_ID_FIELD'     => 'project_type',
		'LANG_ID_FIELD'      => 'language_id',
		'PROGRESS_ID_FIELD'  => 'progress_id',
		'URL_FIELD'          => 'project_url',
		'DOWNLOAD_FIELD'     => 'download',
		'ALLOW_REVIEW_FIELD' => 'project_allow_review',
		'GENRE_OPTIONS'      => $genre_options,
		'LANG_OPTIONS'       => $lang_options,
		'PROGRESS_OPTIONS'   => $progress_options,
		'SUBMIT_TEXT'        => ($action == "add") ? "Submit project" : "Submit changes")
	);

	if ($action == "edit")
	{
		$template->assign_block_vars('form.edit', array(
			'FIELD'   => 'update_updated',
			'CHECKED' => '')
		);
	}
}
else if ($action == "remove") {
	$template->assign_block_vars('remove', array(
		'YES_LINK' => "editproject_general.php?action=remove&amp;project_id=$project_id&amp;confirmed=1",
		'NO_LINK'  => "userpage.php")
	);
}

$template->pparse('body');

placeFooter();
?>
