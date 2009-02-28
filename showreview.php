<?php
include("includes/main.php");

$game = mysql_fetch_object(doQuery("SELECT * FROM ". PROJECTS_TABLE ." WHERE project_id=$project_id"));
$reviews = doQuery(
	"SELECT ". USERS_TABLE .".user_id, username, DATE_FORMAT(review_added, '%d-%m-%Y') AS review_added, review_score, review_text, review_id ".
	"FROM ". REVIEWS_TABLE ." LEFT JOIN ". USERS_TABLE ." USING (user_id) ".
	"WHERE project_id=$project_id ORDER BY review_added"
);

placeHeader(array(array("Reviews"), array($game->project_name)));

$template->set_filenames(array(
	'body' => 'showreviews_body.tpl')
);

$template->assign_vars(array(
	'GAME_NAME' => $game->project_name,
	'GAME_URL'  => "showgame.php?project_id=$game->project_id")
);

while ($row = mysql_fetch_object($reviews))
{
	$template->assign_block_vars('review', array(
		'REVIEW_ID' => $row->review_id,
		'REVIEWER'  => $row->username,
		'REVIEWER_PROFILE_URL' => 'profile.php?user_id='. $row->user_id,
		'ADDED'     => $row->review_added,
		'SCORE'     => $row->review_score,
		'TEXT'      => prepare_for_display($row->review_text))
	);
}

$template->pparse('body');

placeFooter();
?>

