<?php
include("includes/main.php");

$error = "";
$critical = false;
$message = "";

if (!$userdata['session_logged_in']) {
	abort_with_error('You must be logged in to write news.');
}

if (isset($action) && $action != 'add') {
	if (!isset($news_id)) {
		abort_with_error('Somehow you didn\'t specify a news_id.');
	}
	if ($error = check_access_level(NEWS_TABLE, 'news_id', $news_id)) {
		abort_with_error($error);
	}
}

if (!isset($action) || ($action != "edit" && $action != "remove" && $action != "add")) {
	abort_with_error('No valid action specified.');
}


// Make sure there is an bbcode_uid
if (empty($news_bbcode_uid)) {$news_bbcode_uid = make_bbcode_uid();}


if (($action == "edit" || $action == "add") && isset($submit) && empty($error))
{
  if (!isset($news_message)) $news_message = '';
  if (!isset($news_title))   $news_title = '';

  // Prepare the message
  $news_message = prepare_for_store($news_message, $news_bbcode_uid);
  $news_title   = prepare_for_store($news_title,   '');

	// The news message and title should contain content
  $error = '';
	if (strlen($news_message) < 1) $error .= ((strlen($error) > 0) ? "<br />" : "") . "Empty news message.";
	if (strlen($news_title)   < 1) $error .= ((strlen($error) > 0) ? "<br />" : "") . "Empty title.";

	if (empty($error)) {
		if ($action == "edit") {
			// UPDATE the news entry
			doQuery(
				"UPDATE ". NEWS_TABLE ." SET news_message='$news_message', news_title='$news_title', news_bbcode_uid='$news_bbcode_uid' WHERE news_id = $news_id"
			);
			$message = "Your news message has been updated.";
		}
		else if ($action == "add") {

			// INSERT the message in the database
			$result = doQuery(
				"INSERT INTO ". NEWS_TABLE. " (user_id, news_posted, news_message, news_title, news_bbcode_uid) ".
				"VALUES (". $userdata['user_id'] .", NOW(), '$news_message', '$news_title', '$news_bbcode_uid')"
			);
			$message = "Your news message has been added to RPGDX!";
		}
	}
}

$form['news_bbcode_uid'] = $news_bbcode_uid;

if (($action == "edit" || $action == "remove") && !isset($submit) && empty($error))
{
	// Get information about the selected news message
	$result = doQuery("SELECT * FROM ". NEWS_TABLE ." WHERE news_id='$news_id'");
	if ($row = mysql_fetch_array($result)) {
		foreach ($row as $key => $value) {
			$form[$key] = htmlentities($value);
		}
	}
}
else
{
	// Strip slashes from the posted information and prepare for HTML printing.
	foreach ($HTTP_POST_VARS as $key => $value) {
    $form[$key] = htmlentities(stripslashes($value));
	}
}


if     ($action == "add")  placeHeader(array(array("Posting news message")));
elseif ($action == "edit") placeHeader(array(array("Editing news message")));
else                       placeHeader(array(array("Invalid command")));

$template->set_filenames(array(
	'body' => 'editnews_body.tpl')
);



if (strlen($error) > 0) {
	$template->assign_block_vars('error', array('TEXT' => $error));

	if ($critical) {
		$template->pparse('body');
		placeFooter();
		die();
	}
}

if (strlen($message) > 0) {
	$template->assign_block_vars('message', array('TEXT' => $message));
}

if ($action == "edit" || $action == "add")
{
	$template->assign_block_vars('form', array(
		'ACTION'            => "editnews.php?action=$action",
		'HIDDEN'            => ((isset($form) && isset($form['news_id'])) ? '<input type=hidden name=news_id value='. $form['news_id'] .'>' : '').
                           ((isset($form) && isset($form['news_bbcode_uid'])) ? '<input type=hidden name=news_bbcode_uid value='. $form['news_bbcode_uid'] .'>' : ''),
		'NEWS_FIELD'        => 'news_message',
    'NEWS_TITLE_FIELD'  => 'news_title',
    'NEWS_TITLE'        => (isset($form) && isset($form['news_title'])) ? prepare_for_edit($form['news_title'], '') : '',
  	'NEWS_MESSAGE'      => (isset($form) && isset($form['news_message'])) ? prepare_for_edit($form['news_message'], $form['news_bbcode_uid']) : '',
		'SUBMIT_TEXT'       => ($action == "add") ? "Submit news" : "Submit changes")
	);
}
else if ($action == "remove") {
	$template->assign_block_vars('remove', array(
		'YES_LINK' => "editnews.php?action=remove&amp;news_id=$news_id&amp;confirmed=1",
		'NO_LINK'  => "userpage.php")
	);
}

$template->pparse('body');

placeFooter();
?>