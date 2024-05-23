<?php
include("includes/main.php");

$error = "";
$message = "";

// Some extensive error checking
// - You must be logged in
// - A valid action must be specified
// - For editing and removing, an article must be specified
// - When an article is specified, the user must be authorized to edit/remove that article.

if (!$userdata['session_logged_in']) {
	abort_with_error('You must be logged in to access this page.');
}
if (isset($action) && $action != 'add') {
	if (!isset($article_id)) {
		abort_with_error('Somehow you didn\'t specify an article_id.');
	}
	if ($error = check_access_level(ARTICLES_TABLE, 'article_id', $article_id)) {
		abort_with_error($error);
	}
}
if (!isset($action) || ($action != "edit" && $action != "remove" && $action != "add")) {
	abort_with_error('No valid action specified.');
}


if ($action == "remove" && isset($confirmed) && $article_id > 0 && strlen($error) == 0)
{
	// DELETE article from database
	doQuery("DELETE FROM ". ARTICLES_TABLE ." WHERE article_id=$article_id");
	header("Location: userpage.php"); die();
}

if (($action == "edit" || $action == "add") && isset($submit) && strlen($error) == 0)
{
	// Checks information on these conditions:
	// - An article name is filled in
	// - An article URL is filled in
	// - The article category_id is larger than 0

  // Prepare the article
  $article_summary = prepare_for_store($article_summary, '');
  $article_title   = prepare_for_store($article_title,   '');

	if (strlen($article_title) < 1) $error .= ((strlen($error) > 0) ? "<br />" : "") . "No article name specified.";
	if (strlen($article_url)  < 1) $error .= ((strlen($error) > 0) ? "<br />" : "") . "No URL to article specified.";
	if ($article_type  < 1) $error .= ((strlen($error) > 0) ? "<br />" : "") . "No type of article specified.";

	if (strlen($error) == 0) {
		if ($action == "edit") {
			// UPDATE the article entry
			doQuery(
				"UPDATE ". ARTICLES_TABLE ." SET ".
				"article_summary = '$article_summary', ".
				"article_url     = '$article_url', ".
				"article_type    = '$article_type', ".
				"article_title   = '$article_title' ".
				"WHERE article_id = $article_id"
			);
			$message = "Your article has been updated.";
		}
		else if ($action == "add") {
			// INSERT the article in the database
			$result = doQuery(
				"INSERT INTO ". ARTICLES_TABLE ." (user_id, article_created, article_summary, article_url, article_type, article_title) ".
				"VALUES (". $userdata['user_id'] .", NOW(), '$article_summary', '$article_url', '$article_type', '$article_title')"
			);
			$message = "Your article has been added to RPGDX!";
		}
	}
}

if (($action == "edit" || $action == "remove") && $article_id > 0 && !isset($submit))
{
	// Get information about the selected article
	$result = doQuery("SELECT * FROM ". ARTICLES_TABLE ." WHERE article_id='$article_id'");
	if ($row = mysql_fetch_array($result)) {
		foreach ($row as $key => $value) {
      $form[$key] = htmlentities($value);
			//$row["$key"] = htmlentities($value);
		}
		//extract($row, EXTR_PREFIX_ALL, "art");
	}
}
else
{
	// Strip slashes from the posted information and prepare for HTML printing.
	foreach ($_POST as $key => $value) {
    $form[$key] = htmlentities(stripslashes($value));
	}
}

if ($action == "remove") placeHeader(array(array("Removing article")));
else if ($action == "add") placeHeader(array(array("Adding new article")));
else if ($action == "edit") placeHeader(array(array("Editing article")));
else placeHeader(array(array("Invalid command")));

$template->set_filenames(array(
	'body' => 'editarticle_body.tpl')
);

$template->assign_vars(array(
	'ART_NAME' => ((isset($form)) ? $form['article_title']   : ''),
	'ART_URL'  => ((isset($form)) ? $form['article_url']     : ''),
	'ART_DESC' => ((isset($form)) ? prepare_for_edit($form['article_summary']) : ''))
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
	$type_selection = "";
	$result = doQuery("SELECT * FROM ". ARTICLE_TYPES_TABLE);
	while ($row = mysql_fetch_object($result))
	{
		$type_selection .=
			"<input type=\"radio\" value=\"$row->type_id\" name=\"article_type\"".
			((isset($form) && $form['article_type'] == $row->type_id) ? " checked" : "") .">$row->type_title<br />\n";
	}

	$template->assign_block_vars('form', array(
		'ACTION'         => "editarticle.php?action=$action",
		'HIDDEN'         => (isset($article_id) && $article_id > 0) ? "<input type=hidden name=article_id value=". $article_id .">" : "",
		'NAME_FIELD'     => 'article_title',
		'URL_FIELD'      => 'article_url',
		'DESC_FIELD'     => 'article_summary',
		'TYPE_SELECTION' => $type_selection,
		'SUBMIT_TEXT'    => ($action == "add") ? "Submit article" : "Submit changes")
	);
}
else if ($action == "remove") {
	$template->assign_block_vars('remove', array(
		'YES_LINK' => "editarticle.php?action=remove&amp;article_id=$article_id&amp;confirmed=1",
		'NO_LINK'  => "userpage.php")
	);
}

$template->pparse('body');

placeFooter();
?>
