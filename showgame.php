<?php
include("includes/main.php");


if (!isset($project_id)) {
	abort_with_error('No project_id specified.');
}

/* Grab all the info from the database.
 */

$result = doQuery(
	"SELECT ".
	"  project_id, project_bbcode_uid, project_name, project_contributors, project_description, download, project_url, ".
  "  project_type, progress_id, ". PROJECTS_TABLE .".language_id, ".
	"  DATE_FORMAT(project_last_update, '%d-%m-%Y') AS updated, ".
	"  ". PROJECTS_TABLE .".user_id, username AS poster, ".
	"  language_name AS prog_lang, ".
	"  status_title, ".
	"  type_id, type_title, type_title_plural, ".
	"  project_allow_review ".
	"FROM ". PROJECTS_TABLE .
	"  LEFT JOIN ". USERS_TABLE ." USING (user_id) ".
	"  LEFT JOIN ". PROGRAMMING_LANGUAGES_TABLE ." ON ". PROJECTS_TABLE .".language_id = ". PROGRAMMING_LANGUAGES_TABLE .".language_id ".
	"  LEFT JOIN ". PROJECT_STATUSSES_TABLE ." ON progress_id = status_id ".
	"  LEFT JOIN ". PROJECT_TYPES_TABLE ." ON project_type = type_id ".
	"WHERE project_id=". intval($project_id)
);

if (!$game = mysql_fetch_object($result)) {
	abort_with_error('No such project, it might have been removed.');
}


$template->set_filenames(array(
	'body' => 'showgame_body.tpl')
);

$template->assign_vars(array(
	'GAME_NAME'          => $game->project_name,
	'GAME_POSTER'        => $game->poster,
  'GAME_TYPE'          => $game->type_title,
  'GAME_TYPE_URL'      => append_sid('showcategory.php?cat='. $game->project_type),
	'GAME_TYPE_LINK'     => "<a href=\"". append_sid("showcategory.php?cat=$game->project_type") ."\">$game->type_title</a>",
	'GAME_LASTUPDATE'    => $game->updated,
  'GAME_REVIEW_URL'    => append_sid('editreview.php?action=add&amp;project_id='. $game->project_id),
	'POSTER_PROFILE_URL' => append_sid('profile.php?user_id='. $game->user_id))
);

if ($game->progress_id > 0) {
	$template->assign_block_vars('status', array('TEXT' => $game->status_title));
}
if ($game->language_id > 0) {
	$template->assign_block_vars('language', array('NAME' => $game->prog_lang));
}

if ($game->project_allow_review) {
	$template->assign_block_vars('review_allowed', array());
}
else {
	$template->assign_block_vars('review_not_allowed', array());
}

/*
<tr>
	<td class=tableHeader>Source available</td>
	<td class=tableCell>Yes</td>
</tr>
<tr>
	<td class=tableHeader>Ports</td>
	<td class=tableCell>Windows, Linux</td>
</tr>
*/

if (strlen($game->project_description) > 0 || strlen($game->project_contributors) > 0)
{
	if (strlen($game->project_contributors) > 0) {
		$template->assign_block_vars('descpart', array(
			'HEADING' => "Contributors",
			'CONTENT' => prepare_for_display($game->project_contributors))
		);
	}
	if (strlen($game->project_description) > 0) {
		$template->assign_block_vars('descpart', array(
			'HEADING' => "Description",
			'CONTENT' => prepare_for_display($game->project_description, $game->project_bbcode_uid))
		);
	}
} else {
	$template->assign_block_vars('nodesc', array());
}


/*
 * Show the screenshots
 */

$result = doQuery(
	"SELECT screenshot_id, screenshot_title, ". UPLOAD_FILENAME ." AS screenshot_url ".
	"FROM ". PROJECT_SCREENSHOTS_TABLE ." LEFT JOIN ". UPLOADS_TABLE ." USING (upload_id) ".
	"WHERE ". PROJECT_SCREENSHOTS_TABLE .".project_id=$game->project_id ".
	"ORDER BY screenshot_id"
);

if (mysql_num_rows($result) > 0) {
	$template->assign_block_vars('screens', array());

	while ($row = mysql_fetch_object($result)) {
		$template->assign_block_vars('screens.shot', array(
			'LINK'  => $row->screenshot_url,
			'IMG'   => $row->screenshot_url.'.thumb.jpg',
			'TITLE' => "$row->screenshot_title (click for larger image)")
		);
	}
}


/*
 * Website and download link
 * TODO: Allow multiple custom links
 */

if ($game->project_url != "" || $game->download != "")
{
  $template->assign_block_vars('links', array());

  if ($game->project_url != "") {
    $template->assign_block_vars('links.link', array(
      'LINK' => "<a href='$game->project_url'>goto website</a>",
      'URL'  => $game->project_url,
      'NAME' => 'Website')
    );
  }
  if ($game->download != "") {
    $template->assign_block_vars('links.link', array(
      'LINK' => "<a href='$game->download'>download</a>",
      'URL'  => $game->download,
      'NAME' => 'Download')
    );
  }
}


/*
 * Review link
 */

if ($userdata['session_logged_in']) {
	//$template->assign_block_vars('link', array('LINK' => "<a href='editreview.php?action=add&amp;project_id=$game->project_id'>review this game</a>"));
} else {
	$template->assign_block_vars('loginforreview', array());
}



/*
 * Handle project rating and reviews
 */

$result = doQuery(
	"SELECT COUNT(*) AS number, AVG(review_score) AS average_score FROM ". REVIEWS_TABLE ." ".
	"WHERE project_id='$game->project_id' GROUP BY project_id"
);

if ($result && $row = mysql_fetch_assoc($result)) {
	$template->assign_block_vars('reviews', array(
		'LINK'    => "<a href='showreview.php?project_id=$game->project_id'>". $row['number']. ' review'. (($row['number'] > 1) ? 's' : '') .'</a>',
    'URL'     => append_sid('showreview.php?project_id='. $game->project_id),
		'AVERAGE' => sprintf("%01.1f", $row['average_score']),
    'CAPTION' => 'average of '. $row['number'] .' review'. (($row['number'] > 1) ? 's' : ''))
	);
}
else {
	$template->assign_block_vars('noreviews', array());
}

$result = doQuery(
	"SELECT ". REVIEWS_TABLE .".user_id, username, DATE_FORMAT(review_added, '%d-%m-%Y') AS review_added_f, review_score, review_text, review_id ".
	"FROM ". REVIEWS_TABLE ." LEFT JOIN ". USERS_TABLE ." USING (user_id) ".
	"WHERE project_id=$project_id ORDER BY review_added"
);

while ($row = mysql_fetch_object($result))
{
	$template->assign_block_vars('review', array(
		'REVIEW_ID'            => $row->review_id,
		'REVIEWER'             => $row->username,
		'REVIEWER_PROFILE_URL' => append_sid('profile.php?user_id='. $row->user_id),
		'ADDED'                => $row->review_added_f,
		'SCORE'                => $row->review_score,
		'TEXT'                 => prepare_for_display($row->review_text))
	);
}


/*
 * Handle winnings in contests
 */

$winnings = get_contest_results($game->project_id);
if (sizeof($winnings) > 0) {
	foreach ($winnings as $key => $won) {
		$template->assign_block_vars('contest_award', array(
			'IMAGE_URL'     => 'images/'. $won->category_image,
			'CATEGORY_ID'   => $won->category_id,
			'CATEGORY_NAME' => $won->category_name,
			'CONTEST_ID'    => $won->contest_id,
			'CONTEST_NAME'  => $won->contest_name,
			'CONTEST_URL'   => append_sid('showcontest.php?contest_id='. $won->contest_id))
		);
	}
}


/* Start outputting the page
 */

placeHeader(array(
	array($game->type_title_plural, append_sid('showcategory.php?cat='. $game->project_type)),
	array($game->project_name, append_sid('showgame.php?project_id='. $game->project_id))
));

$template->pparse('body');
placeFooter();
?>
